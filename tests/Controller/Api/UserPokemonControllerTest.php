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

    public function testApiPokedexWithNoPokemon(): void
    {
        $client = static::createClient();
        $this->loginUser($client);

        // API now returns ALL Pokemon (filtering is client-side)
        $client->request('GET', '/api/pokedex');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('pokemon', $data);
        self::assertIsArray($data['pokemon']);
        // API returns all Pokemon, filtering is done in JavaScript
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
