<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Repository\CustomListRepository;
use App\Repository\PokemonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CustomListApiControllerTest extends WebTestCase
{
    public function testAddPokemonToList(): void
    {
        $client = static::createClient();

        // Login as test user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@go-swap.com');
        $this->assertNotNull($user, 'Test user should exist');
        $client->loginUser($user);

        // Get a custom list
        $customListRepository = static::getContainer()->get(CustomListRepository::class);
        $customLists = $customListRepository->findAllByUser($user);

        if (empty($customLists)) {
            $this->markTestSkipped('No custom lists available for testing');
        }

        $customList = $customLists[0];

        // Get a pokemon that is NOT in the list
        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $allPokemon = $pokemonRepository->findAll();
        $existingPokemonIds = array_map(fn ($p) => $p->getId(), $customList->getPokemons());

        $pokemon = null;
        foreach ($allPokemon as $p) {
            if (!in_array($p->getId(), $existingPokemonIds)) {
                $pokemon = $p;
                break;
            }
        }

        if (!$pokemon) {
            $this->markTestSkipped('No available pokemon to add to list');
        }

        // Add pokemon to list
        $client->request(
            'POST',
            sprintf('/api/custom-lists/%d/pokemon/%d', $customList->getId(), $pokemon->getId()),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($pokemon->getId(), $data['data']['pokemon']['id']);
        $this->assertEquals($pokemon->getName(), $data['data']['pokemon']['name']);
    }

    public function testAddPokemonAlreadyInList(): void
    {
        $client = static::createClient();

        // Login as test user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@go-swap.com');
        $this->assertNotNull($user, 'Test user should exist');
        $client->loginUser($user);

        // Get a custom list with pokemon
        $customListRepository = static::getContainer()->get(CustomListRepository::class);
        $customLists = $customListRepository->findAllByUser($user);

        if (empty($customLists)) {
            $this->markTestSkipped('No custom lists available for testing');
        }

        $customList = null;
        $pokemon = null;

        foreach ($customLists as $list) {
            if ($list->getPokemonCount() > 0) {
                $customList = $list;
                $pokemon = $list->getPokemons()[0];
                break;
            }
        }

        if (!$customList || !$pokemon) {
            $this->markTestSkipped('No custom list with pokemon available for testing');
        }

        // Try to add same pokemon again
        $client->request(
            'POST',
            sprintf('/api/custom-lists/%d/pokemon/%d', $customList->getId(), $pokemon->getId()),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Pokemon already in list', $data['error']);
    }

    public function testAddPokemonUnauthorized(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/custom-lists/1/pokemon/1');

        // Symfony redirects to login (302) when user is not authenticated
        $this->assertResponseRedirects('/login');
    }

    public function testDeletePokemonFromList(): void
    {
        $client = static::createClient();

        // Login as test user
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('admin@go-swap.com');
        $this->assertNotNull($user, 'Test user should exist');
        $client->loginUser($user);

        // Get a custom list with pokemon
        $customListRepository = static::getContainer()->get(CustomListRepository::class);
        $customLists = $customListRepository->findAllByUser($user);

        if (empty($customLists)) {
            $this->markTestSkipped('No custom lists available for testing');
        }

        $customListPokemon = null;

        foreach ($customLists as $list) {
            if ($list->getCustomListPokemon()->count() > 0) {
                $customListPokemon = $list->getCustomListPokemon()->first();
                break;
            }
        }

        if (!$customListPokemon) {
            $this->markTestSkipped('No custom list with pokemon available for testing');
        }

        // Delete pokemon from list
        $client->request(
            'DELETE',
            sprintf('/api/custom-lists/pokemon/%d', $customListPokemon->getId()),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Pokemon removed from list', $data['message']);
    }
}
