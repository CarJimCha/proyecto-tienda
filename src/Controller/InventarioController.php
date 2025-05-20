<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InventarioController extends AbstractController
{
    #[Route('/inventario', name: 'app_inventario')]
    public function index(TransactionRepository $transactionRepository): Response
    {
        $user = $this->getUser();

        // Seguridad básica: si no hay usuario logueado, redirigir
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $inventario = $transactionRepository->getGroupedInventoryByUser($user);

        return $this->render('inventario/index.html.twig', [
            'inventario' => $inventario,
        ]);
    }
}
