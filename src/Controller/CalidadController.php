<?php

namespace App\Controller;

use App\Entity\Calidad;
use App\Form\CalidadType;
use App\Repository\CalidadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/calidad')]
final class CalidadController extends AbstractController
{
    #[Route(name: 'app_calidad_index', methods: ['GET'])]
    public function index(CalidadRepository $calidadRepository): Response
    {
        return $this->render('calidad/index.html.twig', [
            'calidads' => $calidadRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_calidad_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $calidad = new Calidad();
        $form = $this->createForm(CalidadType::class, $calidad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($calidad);
            $entityManager->flush();

            return $this->redirectToRoute('app_calidad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('calidad/new.html.twig', [
            'calidad' => $calidad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_calidad_show', methods: ['GET'])]
    public function show(Calidad $calidad): Response
    {
        return $this->render('calidad/show.html.twig', [
            'calidad' => $calidad,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_calidad_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Calidad $calidad, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CalidadType::class, $calidad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_calidad_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('calidad/edit.html.twig', [
            'calidad' => $calidad,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_calidad_delete', methods: ['POST'])]
    public function delete(Request $request, Calidad $calidad, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$calidad->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($calidad);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_calidad_index', [], Response::HTTP_SEE_OTHER);
    }
}
