<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PokedexControllerTest extends WebTestCase
{
    public function testPokedexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/pokedex');

        $this->assertResponseRedirects('/login');
    }

    public function testPokedexPageIsAccessibleForAuthenticatedUser(): void
    {
        $client = static::createClient();

        // Login first
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'admin@go-swap.com',
            'password' => 'admin123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Access pokedex
        $client->request('GET', '/pokedex');

        $this->assertResponseIsSuccessful();
    }

    public function testPokedexDisplaysPokemonCards(): void
    {
        $client = static::createClient();

        // Login
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'admin@go-swap.com',
            'password' => 'admin123',
        ]);
        $client->submit($form);
        $client->followRedirect();

        // Access pokedex
        $crawler = $client->request('GET', '/pokedex');

        $this->assertResponseIsSuccessful();
        // Check that pokemon cards are displayed (should have pokemon images)
        $this->assertGreaterThan(0, $crawler->filter('img')->count(), 'Expected pokemon images on page');
    }
}
