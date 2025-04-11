<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class CompraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad', IntegerType::class, [
                'label' => 'Cantidad',
                'data' => 1,
                'attr' => ['min' => 1],
            ])
            ->add('multiplicador', NumberType::class, [
                'label' => 'Multiplicador de precio',
                'data' => 1.0,
                'scale' => 2,
            ]);
    }
}
