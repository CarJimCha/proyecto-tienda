<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Transaction;
use App\Repository\CategoriaRepository;
use App\Repository\ItemRepository;
use App\Entity\User;
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
        PaginatorInterface $paginator
    ): Response {
        $queryBuilder = $itemRepository->createQueryBuilder('i')
            ->leftJoin('i.categoria', 'c')
            ->addSelect('i', 'c');
            // ->addSelect('c.nombre'); // Añadimos el campo de categoría necesario para la ordenación

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

        // Ordenación segura con switch
        $sortParam = $request->query->get('sort', 'i.nombre');

        switch ($sortParam) {
            case 'i.precio':
                $queryBuilder->orderBy('i.precio', 'ASC');
                break;
            case 'i.precio_DESC':
                $queryBuilder->orderBy('i.precio', 'DESC');
                break;
            case 'c.nombre':
                $queryBuilder->orderBy('c.nombre', 'ASC');
                break;
            default:
                $queryBuilder->orderBy('i.nombre', 'ASC');
                $sortParam = 'i.nombre';
                break;
        }

        $sortField = $sortParam;

        // Paginación
        $items = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            25
        );

        return $this->render('comprar/index.html.twig', [
            'items' => $items,
            'sortField' => $sortField,
            'nombre' => $nombre,
            'categoriaId' => $categoriaId,
            'categorias' => $categoriaRepository->findAll(),
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

