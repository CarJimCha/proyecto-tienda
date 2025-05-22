<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Transaction;
use App\Repository\CategoriaRepository;
use App\Repository\ItemRepository;
use App\Entity\User;
use App\Service\PaginadorService;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Repository\CalidadRepository;
use Knp\Component\Pager\PaginatorInterface;

class CompraController extends AbstractController
{

    /* TODO: Este es el controlador OG, no perder por si hay que dar marcha atrás */
    // #[Route('/comprar', name: 'app_compra')]
    /* public function index(ItemRepository $itemRepository): Response
    {
        $items = $itemRepository->findAll();

        return $this->render('comprar/index.html.twig', [
            'items' => $items,
        ]);
    } */

    #[Route('/comprar', name: 'app_compra')]
    public function index(
        Request $request,
        ItemRepository $itemRepository,
        CategoriaRepository $categoriaRepository,
        PaginadorService $paginadorService
    ): Response {
        $queryBuilder = $itemRepository->createQueryBuilder('i')
            ->leftJoin('i.categoria', 'c')
            ->addSelect('c');

        // Filtros
        $nombre = $request->query->get('nombre');
        $categoriaId = $request->query->get('categoria');

        if ($nombre) {
            $queryBuilder->andWhere('i.nombre LIKE :nombre')
                ->setParameter('nombre', '%' . $nombre . '%');
        }

        if ($categoriaId) {
            $queryBuilder->andWhere('c.id = :categoriaId')
                ->setParameter('categoriaId', $categoriaId);
        }

        // Ordenación segura
        $allowedSorts = ['i.nombre', 'i.precio', 'c.nombre'];
        $sortField = $request->query->get('sort', 'i.nombre');
        $sortDirection = strtoupper($request->query->get('direction', 'ASC'));

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'i.nombre';
        }
        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            $sortDirection = 'ASC';
        }

        if ($sortField === 'i.precio') {
            // Creamos un campo oculto que indica si el precio es 0 (no comprable)
            $queryBuilder
                ->addSelect('(CASE WHEN i.precio = 0 THEN 1 ELSE 0 END) AS HIDDEN is_not_buyable');

            // Para ASC: primero los comprables, luego los de precio 0
            // Para DESC: primero los de precio 0
            $queryBuilder->orderBy('is_not_buyable', $sortDirection === 'ASC' ? 'ASC' : 'DESC')
                ->addOrderBy('i.precio', $sortDirection);
        } else {
            $queryBuilder->orderBy($sortField, $sortDirection);
        }

        // Paginación
        $itemsPerPage = $request->query->getInt('items_per_page', 10);
        $items = $paginadorService->paginar($queryBuilder, $request, $itemsPerPage);

        return $this->render('comprar/index.html.twig', [
            'items' => $items,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'nombre' => $nombre,
            'categoriaId' => $categoriaId,
            'categorias' => $categoriaRepository->findAll(),
            'itemsPerPage' => $itemsPerPage,
        ]);
    }



    // Ruta para ver los detalles del ítem a comprar
    #[Route('/comprar/{id}', name: 'comprar', methods: ['GET'])]
    public function comprar(
        int $id,
        Request $request,
        ItemRepository $itemRepository,
        CalidadRepository $calidadRepository,
        EntityManagerInterface $em,
        Security $security
    ): Response {
        /** @var User|null $usuario */
        $usuario = $security->getUser();
        if (!$usuario) {
            $this->addFlash('error', 'Debes iniciar sesión.');
            return $this->redirectToRoute('app_login');
        }

        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('El ítem no existe');
        }

        $calidades = $calidadRepository->findAll();

        $cantidad = (int) $request->query->get('cantidad', 0);
        $calidadId = $request->query->get('calidad');

        // Si hay parámetros GET de comprar, procesamos la transacción
        if ($cantidad > 0 && $calidadId !== null) {
            $calidad = $calidadRepository->find($calidadId);
            if (!$calidad) {
                $this->addFlash('error', 'La calidad seleccionada no es válida.');
            } else {
                $nombreCategoria = $item->getCategoria()->getNombre();
                $categoriasCombate = [
                    "Herramientas y Oficios",
                    "Arte y Decoración",
                    "Orfebrería y Joyas",
                    "Instrumentos Musicales",
                    "Panoplia Clásica",
                    "Panoplia Oriental",
                    "Panoplia Exótica",
                ];

                if (in_array($nombreCategoria, $categoriasCombate)) {
                    $multiplicador = (float) $calidad->getMultiplicadorPrecioCombate();
                } else {
                    $multiplicador = (float) $calidad->getMultiplicadorPrecio();
                }

                $precioTotal = $item->getPrecio() * $cantidad * $multiplicador;

                if ($usuario->getBalance() < $precioTotal) {
                    $this->addFlash('error', 'No tienes suficiente saldo para esta comprar.');
                } else {
                    $transaccion = new Transaction();
                    $transaccion->setUser($usuario);
                    $transaccion->setItem($item);
                    $transaccion->setCantidad($cantidad);
                    $transaccion->setPrecio($precioTotal);
                    $transaccion->setTimestamp(new \DateTimeImmutable());
                    $transaccion->setCategoria($item->getCategoria());
                    $transaccion->setCalidad($calidad);

                    $usuario->setBalance($usuario->getBalance() - $precioTotal);

                    $em->persist($transaccion);
                    $em->persist($usuario);
                    $em->flush();

                    $this->addFlash('success', "Has comprado $cantidad unidad(es) de {$item->getNombre()} (calidad {$calidad->getNombre()}) por $precioTotal monedas.");
                    // Redirige para evitar re-envío al refrescar
                    return $this->redirectToRoute('comprar', ['id' => $id]);
                }
            }
        }

        return $this->render('comprar/comprar.html.twig', [
            'item' => $item,
            'calidades' => $calidades,
        ]);
    }


    public function listarItems(Request $request, PaginatorInterface $paginator)
    {
        // Obtener los ítems con un query builder de Doctrine
        $queryBuilder = $this->itemRepository->createQueryBuilder('i');

        // Aplicar la ordenación según el parámetro 'sort' (por nombre o categoría)
        $sortField = $request->query->get('sort', 'nombre'); // Campo por defecto
        $queryBuilder->orderBy('i.' . $sortField, 'ASC');

        // Paginación (obtenemos los ítems paginados)
        $items = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Página actual
            20 // Número de ítems por página
        );

        return $this->render('comprar/index.html.twig', [
            'items' => $items,
            'sortField' => $sortField,
        ]);
    }


}

