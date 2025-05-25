<?php

namespace App\Controller;

use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
