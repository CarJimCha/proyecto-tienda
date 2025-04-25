<?php

namespace App\Controller;

use App\Form\CsvImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CsvImportController extends AbstractController
{
    #[Route('/admin/importar-items', name: 'import_items')]
    public function importItems(Request $request): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();

            // Aquí es donde procesaremos el CSV más adelante

            $this->addFlash('success', 'Archivo recibido. Procesamiento aún no implementado.');
            return $this->redirectToRoute('import_items');
        }

        return $this->render('import/import_items.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
