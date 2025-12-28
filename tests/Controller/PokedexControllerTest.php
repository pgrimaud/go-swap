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

    public function testApiPokedexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/pokedex');

        $this->assertResponseRedirects('/login');
    }

    public function testApiPokedexEndpoint(): void
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

        // Call API
        $client->request('GET', '/api/pokedex');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode((string) $client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('pokemon', $data);
        $this->assertIsArray($data['pokemon']);
        $this->assertNotEmpty($data['pokemon'], 'API should return all Pokemon');
    }

    public function testApiPokedexPokemonStructure(): void
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

        $client->request('GET', '/api/pokedex');

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        // Skip test if no Pokemon in test database
        if (empty($data['pokemon'])) {
            $this->markTestSkipped('No Pokemon in test database. Run app:update:pokemon --env=test first.');
        }

        $this->assertNotEmpty($data['pokemon']);
        $pokemon = $data['pokemon'][0];

        // Check Pokemon has required fields
        $this->assertArrayHasKey('id', $pokemon);
        $this->assertArrayHasKey('number', $pokemon);
        $this->assertArrayHasKey('name', $pokemon);
        $this->assertArrayHasKey('picture', $pokemon);
        $this->assertArrayHasKey('generation', $pokemon);
        $this->assertArrayHasKey('userPokemon', $pokemon);

        // If userPokemon is present, check structure
        if ($pokemon['userPokemon'] !== null) {
            $this->assertArrayHasKey('hasNormal', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasShiny', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasShadow', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasPurified', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasLucky', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasXxl', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasXxs', $pokemon['userPokemon']);
            $this->assertArrayHasKey('hasPerfect', $pokemon['userPokemon']);
        }
    }
}
