<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Categoria;
use App\Form\CsvImportType;
use App\Repository\ItemRepository;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CsvImportController extends AbstractController
{
    #[Route('/admin/importar-items', name: 'app_import_items')]
    public function importItems(
        Request $request,
        EntityManagerInterface $em,
        CategoriaRepository $categoriaRepository,
        ItemRepository $itemRepository
    ): Response {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();
            if (($handle = fopen($csvFile->getPathname(), 'r')) !== false) {
                $firstLine = true;
                $importedCount = 0;
                $updatedCount = 0;

                while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                    if ($firstLine) {
                        $firstLine = false;
                        continue;
                    }

                    [$nombre, $precioTexto, $peso, $categoriaNombre] = $data;
                    $precioMc = $this->convertirPrecioAMc($precioTexto);

                    // Buscar o crear categoría
                    $categoria = $categoriaRepository->findOneBy(['nombre' => $categoriaNombre]);
                    if (!$categoria) {
                        $categoria = new Categoria();
                        $categoria->setNombre($categoriaNombre);
                        $em->persist($categoria);
                    }

                    // Buscar por nombre
                    $item = $itemRepository->findOneBy(['nombre' => $nombre]);

                    if (!$item) {
                        $item = new Item();
                        $item->setNombre($nombre);
                        $em->persist($item);
                        $importedCount++;
                    } else {
                        $updatedCount++;
                    }

                    // Actualizar datos (común a creación y edición)
                    $item->setPrecio($precioMc);
                    $item->setPeso((float) $peso);
                    $item->setCategoria($categoria);
                }

                fclose($handle);
                $em->flush();

                $this->addFlash('success', "Importación completada: $importedCount nuevos, $updatedCount actualizados.");
                return $this->redirectToRoute('app_import_items');
            } else {
                $this->addFlash('danger', 'No se pudo leer el archivo CSV.');
            }
        }

        return $this->render('import/import_items.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function convertirPrecioAMc(string $precioTexto): int
    {
        $mc = 0;

        // Extraer todos los bloques tipo "1 MO", "25 MP", "3 MC"
        preg_match_all('/(\d+)\s*(MO|MP|MC)/i', $precioTexto, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $valor = (int) $match[1];
            $unidad = strtoupper($match[2]);

            switch ($unidad) {
                case 'MO':
                    $mc += $valor * 1000;
                    break;
                case 'MP':
                    $mc += $valor * 10;
                    break;
                case 'MC':
                    $mc += $valor;
                    break;
            }
        }

        return $mc;
    }
}
