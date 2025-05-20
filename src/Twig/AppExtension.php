<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('moneda_formateada', [$this, 'formatearMoneda']),
        ];
    }

    public function formatearMoneda(float $precio): string
    {
        if ($precio <= 0) {
            return 'No Aplica';
        }

        $precio = round($precio); // nos aseguramos de trabajar con enteros en MC

        $mo = floor($precio / 1000);
        $resto = $precio % 1000;

        $mp = floor($resto / 10);
        $mc = $resto % 10;

        $partes = [];

        if ($mo > 0) $partes[] = "{$mo} MO";
        if ($mp > 0) $partes[] = "{$mp} MP";
        if ($mc > 0) $partes[] = "{$mc} MC";

        return implode(', ', $partes);
    }

}
