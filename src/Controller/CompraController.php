<?php

namespace App\Controller;

/*
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemRepository;

final class CompraController extends AbstractController
{
    #[Route('/compra', name: 'app_compra')]
    public function index(ItemRepository $itemRepository): Response
    {
        $items = $itemRepository->findAll();

        return $this->render('compra/index.html.twig', [
            'items' => $items,
        ]);
    }
} */

/*
// Importar dependencias necesarias
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CompraController extends AbstractController
{
    #[Route('/compra', name: 'app_compra')]
    public function index(ItemRepository $itemRepository): Response
    {
        $items = $itemRepository->findAll();

        return $this->render('compra/index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/compra/añadir/{id}', name: 'app_carrito_añadir')]
    public function añadirCarrito($id, ItemRepository $itemRepository, SessionInterface $session, Request $request): RedirectResponse
    {
        // Buscar el item por el ID
        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('El item no existe.');
        }

        // Obtener la cantidad elegida (por defecto 1)
        $cantidad = $request->request->get('cantidad', 1);

        // Obtener el carrito de la sesión (si no existe, inicializarlo)
        $carrito = $session->get('carrito', []);

        // Verificar si el ítem ya está en el carrito
        if (isset($carrito[$id])) {
            // Si ya está, aumentamos la cantidad
            $carrito[$id]['cantidad'] += $cantidad;
        } else {
            // Si no está, añadimos el ítem con la cantidad indicada
            $carrito[$id] = [
                'item' => $item,
                'cantidad' => $cantidad,
                'precio' => $item->getPrecio(),
            ];
        }

        // Guardar el carrito de nuevo en la sesión
        $session->set('carrito', $carrito);

        // Depurar el contenido del carrito en la sesión
        dump($carrito);  // Esto imprimirá el contenido del carrito en la consola de Symfony

        // Redirigir al usuario de vuelta a la página de compras
        return $this->redirectToRoute('app_compra');
    }
}
*/

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Transaction;
use App\Repository\ItemRepository;
use App\Entity\User;
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
    // #[Route('/compra', name: 'app_compra')]
    /* public function index(ItemRepository $itemRepository): Response
    {
        $items = $itemRepository->findAll();

        return $this->render('compra/index.html.twig', [
            'items' => $items,
        ]);
    } */

    #[Route('/compra', name: 'app_compra')]
    public function index(Request $request, ItemRepository $itemRepository, PaginatorInterface $paginator): Response
    {
        // Obtener los ítems con un query builder de Doctrine
        $queryBuilder = $itemRepository->createQueryBuilder('i');

        // Obtener el campo de ordenación de la consulta
        $sortField = $request->query->get('sort', 'nombre'); // Campo por defecto
        $queryBuilder->orderBy('i.' . $sortField, 'ASC');

        // Paginación (obtenemos los ítems paginados)
        $items = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1), // Página actual
            20 // Número de ítems por página
        );

        return $this->render('compra/index.html.twig', [
            'items' => $items,
            'sortField' => $sortField,
        ]);
    }

    #[Route('/comprar', name: 'comprar_sin_id', methods: ['GET'])]
    public function comprarSinId(Request $request, EntityManagerInterface $em, ItemRepository $itemRepository): Response
    {
        /** @var User $usuario */
        $usuario = $this->getUser();
        if (!$usuario) {
            $this->addFlash('error', 'Debes iniciar sesión.');
            return $this->redirectToRoute('app_login');
        }

        $itemId = $request->request->get('item_id');
        $cantidad = (int)$request->request->get('cantidad', 1);
        $multiplicador = (float)$request->request->get('multiplicador', 1.0);

        $item = $itemRepository->find($itemId);
        if (!$item || $cantidad < 1 || $multiplicador <= 0) {
            $this->addFlash('error', 'Datos inválidos.');
            return $this->redirectToRoute('app_compra');
        }

        $precioTotal = $item->getPrecio() * $cantidad * $multiplicador;

        if ($usuario->getBalance() < $precioTotal) {
            $this->addFlash('error', 'No tienes suficiente saldo.');
            return $this->redirectToRoute('app_compra');
        }

        $transaccion = new Transaction();
        $transaccion->setUserId($usuario);
        $transaccion->setItemId($item);
        $transaccion->setCantidad($cantidad);
        $transaccion->setPrecio($precioTotal);
        $transaccion->setTimestamp(new \DateTimeImmutable());

        $usuario->setBalance($usuario->getBalance() - $precioTotal);

        $em->persist($transaccion);
        $em->flush();

        $this->addFlash('success', 'Has comprado ' . $cantidad . 'x ' . $item->getNombre() . ' por ' . $precioTotal . ' monedas.');

        return $this->redirectToRoute('app_compra');
    }

    // Ruta para ver los detalles del ítem a comprar
    #[Route('/comprar/{id}', name: 'comprar', methods: ['GET'])]
    public function comprarPost(int $id, Request $request, ItemRepository $itemRepository, CalidadRepository $calidadRepository, EntityManagerInterface $em): Response
    {
        /** @var User $usuario */
        $usuario = $this->getUser();
        if (!$usuario) {
            $this->addFlash('error', 'Debes iniciar sesión.');
            return $this->redirectToRoute('app_login');
        }

        // Recuperamos el ítem por su ID
        $item = $itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('El ítem no existe');
        }

        // Recuperamos todas las calidades
        $calidades = $calidadRepository->findAll();

        // Obtener el id de la calidad seleccionada del formulario
        $calidadId = $request->request->get('calidad');
        $calidad = $calidadRepository->find($calidadId);
        if (!$calidad) {
            $this->addFlash('error', 'La calidad seleccionada no es válida.');
            return $this->redirectToRoute('app_compra');
        }

        // Obtener la cantidad y el multiplicador del formulario
        $cantidad = (int) $request->request->get('cantidad', 1);
        $multiplicador = (float) $request->request->get('multiplicador', 1.0);

        // Calcular el precio total
        $precioTotal = $item->getPrecio() * $cantidad * $multiplicador;

        // Verificar si el usuario tiene suficiente saldo
        if ($usuario->getBalance() < $precioTotal) {
            $this->addFlash('error', 'No tienes suficiente saldo.');
            return $this->redirectToRoute('app_compra');
        }

        // Crear la transacción
        $transaccion = new Transaction();
        $transaccion->setUserId($usuario); // Establecer el usuario
        $transaccion->setItemId($item);    // Establecer el ítem
        $transaccion->setCantidad($cantidad);
        $transaccion->setPrecio($precioTotal);
        $transaccion->setTimestamp(new \DateTimeImmutable());
        $transaccion->setCategoriaId($item->getCategoria()); // Establecer la categoría del ítem
        $transaccion->setCalidadId($calidad);  // Establecer la calidad seleccionada

        // Actualizar el saldo del usuario
        $usuario->setBalance($usuario->getBalance() - $precioTotal);

        // Persistir la transacción y el usuario
        $em->persist($transaccion);
        $em->persist($usuario);
        $em->flush();

        $this->addFlash('success', 'Has comprado ' . $cantidad . 'x ' . $item->getNombre() . ' por ' . $precioTotal . ' monedas.');

        return $this->redirectToRoute('app_compra');
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

        return $this->render('compra/index.html.twig', [
            'items' => $items,
            'sortField' => $sortField,
        ]);
    }


}

