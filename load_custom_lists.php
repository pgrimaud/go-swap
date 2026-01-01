<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();

$admin = $em->getRepository(App\Entity\User::class)->findOneBy(['email' => 'admin@go-swap.com']);
if (!$admin) {
    echo "Admin user not found\n";
    exit(1);
}

// Create Trade List with 3 Pokémon
$list1 = new App\Entity\CustomList();
$list1->setName('Trade List');
$list1->setDescription('Pokémon available for trading');
$list1->setIsPublic(true);
$list1->setUser($admin);
$em->persist($list1);

$pokemonNumbers = [1, 4, 7];
$position = 0;
foreach ($pokemonNumbers as $number) {
    $pokemon = $em->getRepository(App\Entity\Pokemon::class)->findOneBy(['number' => $number]);
    if ($pokemon) {
        $listPokemon = new App\Entity\CustomListPokemon();
        $listPokemon->setCustomList($list1);
        $listPokemon->setPokemon($pokemon);
        $listPokemon->setPosition($position++);
        $em->persist($listPokemon);
        echo "Added Pokémon #{$number} to Trade List\n";
    }
}

// Create empty Favorites list
$list2 = new App\Entity\CustomList();
$list2->setName('Favorites');
$list2->setDescription('My favorite Pokémon');
$list2->setIsPublic(false);
$list2->setUser($admin);
$em->persist($list2);

$em->flush();

echo "\n✅ Fixtures loaded successfully!\n";
echo '📋 Trade List UID: ' . $list1->getUid()->toRfc4122() . " (3 Pokémon)\n";
echo '📋 Favorites UID: ' . $list2->getUid()->toRfc4122() . " (empty)\n";
echo '🔍 Search string: ' . $list1->getSearchString() . "\n";
