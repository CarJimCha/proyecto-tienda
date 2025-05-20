<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InventarioController extends AbstractController
{
    #[Route('/inventario', name: 'app_inventario')]
    public function index(
        Request $request,
        TransactionRepository $transactionRepository,
        UserRepository $userRepository
    ): Response {
        $currentUser = $this->getUser();

        // Seguridad básica
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        $usuarioSeleccionado = $currentUser;
        $usuarios = null;

        // Si es administrador, puede seleccionar a otro usuario
        if ($this->isGranted('ROLE_ADMIN')) {
            $usuarios = $userRepository->findAll();

            $selectedUserId = $request->query->get('usuario_id');
            if ($selectedUserId) {
                $usuarioSeleccionado = $userRepository->find($selectedUserId);
            }
        }

        $inventario = $transactionRepository->getGroupedInventoryByUser($usuarioSeleccionado);

        return $this->render('inventario/index.html.twig', [
            'inventario' => $inventario,
            'usuarios' => $usuarios,
            'usuarioSeleccionado' => $usuarioSeleccionado,
        ]);
    }
}
