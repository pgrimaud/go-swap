<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserPokemon;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserPokemonControllerTest extends WebTestCase
{
    private function loginUser(mixed $client): User
    {
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneBy(['email' => 'admin@go-swap.com']);

        if (!$testUser instanceof User) {
            self::fail('Test user not found');
        }

        $client->loginUser($testUser);

        return $testUser;
    }

    public function testToggleVariantRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/user-pokemon/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['variant' => 'normal', 'value' => true]));

        self::assertResponseStatusCodeSame(302);
        self::assertResponseRedirects('/login');
    }

    public function testToggleVariantWithInvalidVariant(): void
    {
        $client = static::createClient();
        $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['variant' => 'invalid', 'value' => true]));

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $data);
        self::assertSame('Invalid variant', $data['error']);
    }

    public function testToggleVariantCreatesUserPokemon(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $userPokemonRepository = static::getContainer()->get(UserPokemonRepository::class);
        $existingUserPokemon = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);
        if ($existingUserPokemon) {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $entityManager->remove($existingUserPokemon);
            $entityManager->flush();
        }

        $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['variant' => 'shiny', 'value' => true]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('success', $data);
        self::assertTrue($data['success']);
        self::assertArrayHasKey('data', $data);
        self::assertSame($pokemon->getId(), $data['data']['pokemonId']);
        self::assertTrue($data['data']['hasShiny']);
        self::assertFalse($data['data']['hasNormal']);
    }

    public function testToggleVariantUpdatesExistingUserPokemon(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $userPokemonRepository = static::getContainer()->get(UserPokemonRepository::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Remove existing
        $existing = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);
        if ($existing) {
            $entityManager->remove($existing);
            $entityManager->flush();
        }

        $userPokemon = new UserPokemon();
        $userPokemon->setUser($user);
        $userPokemon->setPokemon($pokemon);
        $userPokemon->setFirstCaughtAt(new \DateTimeImmutable());
        $userPokemon->setHasNormal(true);

        $entityManager->persist($userPokemon);
        $entityManager->flush();

        $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['variant' => 'shiny', 'value' => true]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertTrue($data['success']);
        self::assertTrue($data['data']['hasNormal']);
        self::assertTrue($data['data']['hasShiny']);
    }

    public function testToggleVariantDeletesWhenNoVariants(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $userPokemonRepository = static::getContainer()->get(UserPokemonRepository::class);

        // Remove existing
        $existing = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);
        if ($existing) {
            $entityManager->remove($existing);
            $entityManager->flush();
        }

        $userPokemon = new UserPokemon();
        $userPokemon->setUser($user);
        $userPokemon->setPokemon($pokemon);
        $userPokemon->setFirstCaughtAt(new \DateTimeImmutable());
        $userPokemon->setHasShiny(true);

        $entityManager->persist($userPokemon);
        $entityManager->flush();

        $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['variant' => 'shiny', 'value' => false]));

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertTrue($data['success']);
        self::assertArrayHasKey('deleted', $data);
        self::assertTrue($data['deleted']);
    }

    public function testToggleAllVariants(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $variants = ['normal', 'shiny', 'shadow', 'purified', 'lucky', 'xxl', 'xxs', 'perfect'];

        foreach ($variants as $variant) {
            $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], json_encode(['variant' => $variant, 'value' => true]));

            self::assertResponseIsSuccessful();
            $data = json_decode($client->getResponse()->getContent(), true);

            self::assertTrue($data['success']);
            self::assertTrue($data['data']['has' . ucfirst($variant)]);
        }
    }

    public function testApiPokedexIncludesUserPokemonData(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        // Create a test Pokemon with variants
        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $userPokemonRepository = static::getContainer()->get(UserPokemonRepository::class);

        // Remove existing UserPokemon if any
        $existing = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);
        if ($existing) {
            $entityManager->remove($existing);
            $entityManager->flush();
        }

        // Create UserPokemon with specific variants
        $userPokemon = new UserPokemon();
        $userPokemon->setUser($user);
        $userPokemon->setPokemon($pokemon);
        $userPokemon->setHasNormal(true);
        $userPokemon->setHasShiny(true);
        $userPokemon->setFirstCaughtAt(new \DateTimeImmutable());

        $entityManager->persist($userPokemon);
        $entityManager->flush();

        // Call API
        $client->request('GET', '/api/pokedex?page=1&perPage=5');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('pokemon', $data);
        self::assertNotEmpty($data['pokemon']);

        // Find our Pokemon in the response
        $foundPokemon = null;
        foreach ($data['pokemon'] as $p) {
            if ($p['id'] === $pokemon->getId()) {
                $foundPokemon = $p;
                break;
            }
        }

        self::assertNotNull($foundPokemon, 'Pokemon not found in API response');
        self::assertArrayHasKey('userPokemon', $foundPokemon);
        self::assertNotNull($foundPokemon['userPokemon']);
        self::assertTrue($foundPokemon['userPokemon']['hasNormal']);
        self::assertTrue($foundPokemon['userPokemon']['hasShiny']);
        self::assertFalse($foundPokemon['userPokemon']['hasShadow']);
    }

    public function testApiPokedexFilterByVariant(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $userPokemonRepository = static::getContainer()->get(UserPokemonRepository::class);

        // Create 2 Pokemon: one with shiny, one without
        $pokemon1 = $pokemonRepository->findOneBy(['number' => 1]);
        $pokemon2 = $pokemonRepository->findOneBy(['number' => 2]);

        if (!$pokemon1 || !$pokemon2) {
            self::markTestSkipped('Need at least 2 Pokemon in test database');
        }

        // Remove existing
        $existing1 = $userPokemonRepository->findByUserAndPokemon($user, $pokemon1);
        if ($existing1) {
            $entityManager->remove($existing1);
        }
        $existing2 = $userPokemonRepository->findByUserAndPokemon($user, $pokemon2);
        if ($existing2) {
            $entityManager->remove($existing2);
        }
        $entityManager->flush();

        // Pokemon 1 has shiny
        $userPokemon1 = new UserPokemon();
        $userPokemon1->setUser($user);
        $userPokemon1->setPokemon($pokemon1);
        $userPokemon1->setHasShiny(true);
        $userPokemon1->setFirstCaughtAt(new \DateTimeImmutable());
        $entityManager->persist($userPokemon1);

        // Pokemon 2 has only normal
        $userPokemon2 = new UserPokemon();
        $userPokemon2->setUser($user);
        $userPokemon2->setPokemon($pokemon2);
        $userPokemon2->setHasNormal(true);
        $userPokemon2->setFirstCaughtAt(new \DateTimeImmutable());
        $entityManager->persist($userPokemon2);

        $entityManager->flush();

        // Call API with variant parameter (for frontend filtering, but backend returns all)
        $client->request('GET', '/api/pokedex?variant=shiny&perPage=10');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('pokemon', $data);
        self::assertEquals('shiny', $data['variant']);

        // Should return ALL Pokemon (not just those with shiny)
        self::assertGreaterThanOrEqual(2, count($data['pokemon']));

        // Verify our test Pokemon are in the response with correct data
        $pokemon1Data = null;
        $pokemon2Data = null;
        foreach ($data['pokemon'] as $p) {
            if ($p['id'] === $pokemon1->getId()) {
                $pokemon1Data = $p;
            }
            if ($p['id'] === $pokemon2->getId()) {
                $pokemon2Data = $p;
            }
        }

        self::assertNotNull($pokemon1Data);
        self::assertNotNull($pokemon2Data);

        // Pokemon 1 should have shiny
        self::assertNotNull($pokemon1Data['userPokemon']);
        self::assertTrue($pokemon1Data['userPokemon']['hasShiny']);

        // Pokemon 2 should have only normal (no shiny)
        self::assertNotNull($pokemon2Data['userPokemon']);
        self::assertFalse($pokemon2Data['userPokemon']['hasShiny']);
        self::assertTrue($pokemon2Data['userPokemon']['hasNormal']);
    }

    public function testApiPokedexWithNoPokemon(): void
    {
        $client = static::createClient();
        $this->loginUser($client);

        // Search for non-existent Pokemon
        $client->request('GET', '/api/pokedex?search=zzzzznonexistent');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('pokemon', $data);
        self::assertEmpty($data['pokemon']);
        self::assertEquals(0, $data['total']);
        self::assertFalse($data['hasMore']);
    }

    public function testToggleVariantWithInvalidJson(): void
    {
        $client = static::createClient();
        $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'invalid json{');

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $data);
    }

    public function testFirstCaughtAtIsSet(): void
    {
        $client = static::createClient();
        $user = $this->loginUser($client);

        $pokemonRepository = static::getContainer()->get(PokemonRepository::class);
        $pokemon = $pokemonRepository->findOneBy([]);

        if (!$pokemon instanceof Pokemon) {
            self::markTestSkipped('No Pokemon found in test database');
        }

        $userPokemonRepository = static::getContainer()->get(UserPokemonRepository::class);
        $existingUserPokemon = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);
        if ($existingUserPokemon) {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $entityManager->remove($existingUserPokemon);
            $entityManager->flush();
        }

        // Create new UserPokemon
        $client->request('POST', '/api/user-pokemon/' . $pokemon->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['variant' => 'normal', 'value' => true]));

        self::assertResponseIsSuccessful();

        // Verify firstCaughtAt is set
        $userPokemon = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);
        self::assertNotNull($userPokemon);
        self::assertInstanceOf(\DateTimeImmutable::class, $userPokemon->getFirstCaughtAt());
        self::assertEqualsWithDelta(
            time(),
            $userPokemon->getFirstCaughtAt()->getTimestamp(),
            5,
            'FirstCaughtAt should be recent'
        );
    }
}
