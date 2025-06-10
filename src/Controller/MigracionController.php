<?php

namespace App\Controller;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\MigratorConfiguration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MigracionController extends AbstractController
{
    #[Route('/migrar', name: 'migrar_bd')]
    # #[IsGranted('ROLE_ADMIN')]
    public function migrar(#[Autowire(service: 'doctrine.migrations.dependency_factory')] DependencyFactory $dependencyFactory): Response
    {
        try {
            $planCalculator = $dependencyFactory->getMigrationPlanCalculator();
            $latestVersion = $dependencyFactory->getVersionAliasResolver()->resolveVersionAlias('latest');
            $plan = $planCalculator->getPlanUntilVersion($latestVersion);

            if ($plan->getItems() === []) {
                return new Response('✅ No hay migraciones pendientes.');
            }

            $migrator = $dependencyFactory->getMigrator();
            $migratorConfiguration = new MigratorConfiguration();

            $migrator->migrate($plan, $migratorConfiguration);

            return new Response('✅ Migraciones ejecutadas correctamente.');
        } catch (\Throwable $e) {
            return new Response('❌ Error en la migración: ' . $e->getMessage());
        }
    }
}
