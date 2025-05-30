<?php

namespace App\Form;

use App\Entity\Calidad;
use App\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalidadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('numero', null,
                ['label' => 'Número'])
            ->add('multiplicador_precio', null,
                ['label' => 'Multiplicador al precio'])
            ->add('multiplicador_precio_combate', null,
                ['label' => 'Multiplicador al precio de Combate'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Calidad::class,
        ]);
    }
}
