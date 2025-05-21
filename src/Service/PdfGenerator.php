<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class PdfGenerator
{
    private Environment $twig;
    private string $projectDir;

    public function __construct(Environment $twig, KernelInterface $kernel)
    {
        $this->twig = $twig;
        $this->projectDir = $kernel->getProjectDir();
    }

    public function generatePdf(string $template, array $data = []): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $html = $this->twig->render($template, $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
