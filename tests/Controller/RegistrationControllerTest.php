<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name=registration_form]');
    }

    public function testRegisterWithValidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Registrarse')->form();

        $form['registration_form[characterName]'] = 'TestCharacter_' . uniqid();
        $form['registration_form[plainPassword]'] = 'validPassword123';
        $form['registration_form[balance]'] = 12345;

        $client->submit($form);

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('body'); // Ajusta esto según la página a la que redirige
    }

    public function testRegisterWithDuplicateCharacterName(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        // Primer registro válido
        $characterName = 'TestDuplicate_' . uniqid();
        $form = $crawler->selectButton('Registrarse')->form();
        $form['registration_form[characterName]'] = $characterName;
        $form['registration_form[plainPassword]'] = 'validPassword123';
        $form['registration_form[balance]'] = 500;

        $client->submit($form);
        $client->followRedirect();

        // Segundo registro con el mismo nombre de personaje
        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Registrarse')->form();
        $form['registration_form[characterName]'] = $characterName; // mismo nombre
        $form['registration_form[plainPassword]'] = 'otherPassword123';
        $form['registration_form[balance]'] = 300;

        $client->submit($form);

        // Debe quedarse en el formulario y mostrar el mensaje de error
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('.form-error-message, .invalid-feedback', 'There is already an account with this characterName');
    }
}
