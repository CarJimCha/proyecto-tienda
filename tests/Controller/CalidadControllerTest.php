<?php

namespace App\Tests\Controller;

use App\Entity\Calidad;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CalidadControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $calidadRepository;
    private string $path = '/calidad/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->calidadRepository = $this->manager->getRepository(Calidad::class);

        foreach ($this->calidadRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Calidad index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'calidad[nombre]' => 'Testing',
            'calidad[numero]' => 'Testing',
            'calidad[multiplicador_precio]' => 'Testing',
            'calidad[multiplicador_precio_combate]' => 'Testing',
            'calidad[items]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->calidadRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Calidad();
        $fixture->setNombre('My Title');
        $fixture->setNumero('My Title');
        $fixture->setMultiplicador_precio('My Title');
        $fixture->setMultiplicador_precio_combate('My Title');
        $fixture->setItems('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Calidad');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Calidad();
        $fixture->setNombre('Value');
        $fixture->setNumero('Value');
        $fixture->setMultiplicador_precio('Value');
        $fixture->setMultiplicador_precio_combate('Value');
        $fixture->setItems('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'calidad[nombre]' => 'Something New',
            'calidad[numero]' => 'Something New',
            'calidad[multiplicador_precio]' => 'Something New',
            'calidad[multiplicador_precio_combate]' => 'Something New',
            'calidad[items]' => 'Something New',
        ]);

        self::assertResponseRedirects('/calidad/');

        $fixture = $this->calidadRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNombre());
        self::assertSame('Something New', $fixture[0]->getNumero());
        self::assertSame('Something New', $fixture[0]->getMultiplicador_precio());
        self::assertSame('Something New', $fixture[0]->getMultiplicador_precio_combate());
        self::assertSame('Something New', $fixture[0]->getItems());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Calidad();
        $fixture->setNombre('Value');
        $fixture->setNumero('Value');
        $fixture->setMultiplicador_precio('Value');
        $fixture->setMultiplicador_precio_combate('Value');
        $fixture->setItems('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/calidad/');
        self::assertSame(0, $this->calidadRepository->count([]));
    }
}
