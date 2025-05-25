<?php

namespace App\Controller;

use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;


class CorreoController extends AbstractController
{
    #[Route('/probar-correo', name: 'probar_correo')]
    public function index(MailService $mailService): Response
    {

        try {
            $mailService->enviarCorreo('carlosjimcha@gmail.com', 'Correo de prueba', '¡Funciona! Hasta el paso 6');
        } catch (\Exception $e) {
            dd('Error al enviar correo: ' . $e->getMessage());
        }

        return new Response('Correo enviado (si todo fue bien)');
    }

    #[Route('/enviar-inventario/{id}', name: 'enviar_inventario')]
    public function enviarInventario(
        Request $request,
        int $id,
        MailService $mailService,
        UserRepository $userRepository,
        TransactionRepository $transactionRepository,
        \Twig\Environment $twig
    ): Response {
        $usuario = $userRepository->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new Response('Dirección de correo no válida.', 400);
            }

            $inventario = $transactionRepository->getGroupedInventoryByUser($usuario);

            // Renderizar plantilla HTML
            $html = $twig->render('pdf/inventario.html.twig', [
                'usuarioSeleccionado' => $usuario,
                'inventario' => $inventario,
            ]);

            try {
                $mailService->enviarCorreoHtml(
                    $email,
                    'Inventario de ' . $usuario->getCharacterName(),
                    $html
                );
            } catch (\Exception $e) {
                return new Response('Error al enviar el correo: ' . $e->getMessage());
            }

            return new Response('Inventario enviado a ' . htmlspecialchars($email) . '.');
        }

        // Mostrar formulario
        return $this->render('correo/formulario_envio.html.twig', [
            'usuario' => $usuario,
        ]);
    }


}
