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

