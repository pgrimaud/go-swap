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
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('perPage', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('hasMore', $data);
        $this->assertArrayHasKey('variant', $data);
        $this->assertArrayHasKey('search', $data);

        $this->assertEquals(1, $data['page']);
        $this->assertEquals(50, $data['perPage']);
        $this->assertIsArray($data['pokemon']);
    }

    public function testApiPokedexPagination(): void
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

        // Page 1
        $client->request('GET', '/api/pokedex?page=1&perPage=5');
        $data1 = json_decode((string) $client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();

        // Skip test if no Pokemon in test database
        if (empty($data1['pokemon'])) {
            $this->markTestSkipped('No Pokemon in test database.');
        }

        // Only test pagination if we have enough Pokemon
        if ($data1['total'] <= 5) {
            $this->markTestSkipped('Not enough Pokemon to test pagination (need > 5).');
        }

        // Page 2
        $client->request('GET', '/api/pokedex?page=2&perPage=5');
        $data2 = json_decode((string) $client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertNotEmpty($data1['pokemon']);
        $this->assertNotEmpty($data2['pokemon']);
        // Verify different Pokemon on different pages
        $this->assertNotEquals($data1['pokemon'][0]['id'], $data2['pokemon'][0]['id']);
    }

    public function testApiPokedexVariantFilter(): void
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

        $client->request('GET', '/api/pokedex?variant=shiny');

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        $this->assertEquals('shiny', $data['variant']);
    }

    public function testApiPokedexSearchFilter(): void
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

        $client->request('GET', '/api/pokedex?search=Pikachu');

        $this->assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        $this->assertEquals('Pikachu', $data['search']);

        // Skip assertion if no Pokemon in test database
        if (empty($data['pokemon'])) {
            $this->markTestSkipped('No Pokemon in test database. Run app:update:pokemon --env=test first.');
        }

        $this->assertNotEmpty($data['pokemon']);
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
        $this->assertArrayHasKey('types', $pokemon);
    }
}
