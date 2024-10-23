<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CheckType;
use App\Helper\PokedexHelper;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use App\Repository\UserRepository;
use App\Service\OpenAI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(
        Request               $request,
        PokemonRepository     $pokemonRepository,
        UserPokemonRepository $userPokemonRepository,
        OpenAI $openAI
    ): Response
    {
        $pokedexs = [];

        foreach (PokedexHelper::POKEDEX as $type => $name) {
            $pokedexs[] = [
                'type' => $type,
                'name' => $name,
                'caught' => $userPokemonRepository->countByPokedex($this->getUser(), $type),
                'total' => $pokemonRepository->countUnique($type),
                'details' => $userPokemonRepository->countByGeneration($this->getUser(), $type),
            ];
        }

        $form = $this->createForm(CheckType::class);
        $form->handleRequest($request);

        $isValid = $this->checkPictureUpload($form, $openAI, $pokedexs);

        return $this->render('app/index.html.twig', [
            'pokedexs' => $pokedexs,
            'form' => $form->createView(),
            'isValid' => $isValid,
            'submitted' => $form->isSubmitted()
        ]);
    }


    public function users(UserRepository $userRepository, RequestStack $request): Response
    {
        return $this->render('app/users.html.twig', [
            'users' => $userRepository->findAll(),
            'userId' => $request->getParentRequest()?->get('id')
        ]);
    }

    #[Route('/trade/{id}', name: 'app_trade')]
    public function trade(UserRepository $userRepository, int $id, PokemonRepository $pokemonRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $connectedUser = $this->getUser();

        if (!$user instanceof User || !$connectedUser instanceof User) {
            throw $this->createNotFoundException('User not found');
        }

        $missingPokemonsUser = $pokemonRepository->missingShinyPokemons($connectedUser, $user);
        $missingPokemonFriend = $pokemonRepository->missingShinyPokemons($user, $connectedUser);

        $evolutionMissingUser = $pokemonRepository->missingShinyPokemonEvolution($connectedUser, $user);
        $evolutionMissingFriend = $pokemonRepository->missingShinyPokemonEvolution($user, $connectedUser);

        $allMissingPokemonsUser = array_unique(array_merge($missingPokemonsUser, $evolutionMissingUser), SORT_REGULAR);
        $allMissingPokemonsFriend = array_unique(array_merge($missingPokemonFriend, $evolutionMissingFriend), SORT_REGULAR);

        return $this->render('app/trade.html.twig', [
            'friend' => $user,
            'userPokemons' => $allMissingPokemonsUser,
            'friendPokemons' => $allMissingPokemonsFriend,
        ]);
    }

    private function checkPictureUpload(FormInterface $form, OpenAI $openAI, array $pokedexs): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ?UploadedFile $pictureFile */
            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                $newFilename = uniqid() . '.' . $pictureFile->guessExtension();
                $uploadFolder = __DIR__ . '/../../var/upload/';
                $completePath = $uploadFolder . $newFilename;

                if (is_dir($uploadFolder) === false) {
                    mkdir($uploadFolder);
                }

                try {
                    $hasError = false;

                    $pictureFile->move(
                        __DIR__ . '/../../var/upload/',
                        $newFilename
                    );

                    $result = $openAI->getTextFromPicture($completePath);

                    foreach($result as $item) {
                        $outputName = $item['text'];
                        $number = $item['number'];

                        if (isset(PokedexHelper::POKEDEX_SCREENSHOT_MAPPING[$outputName])) {
                            $name = PokedexHelper::POKEDEX_SCREENSHOT_MAPPING[$outputName];

                            foreach($pokedexs as $pokedex) {
                                if ($pokedex['type'] === $name) {
                                    if ($pokedex['caught'] !== $number) {
                                        $this->addFlash('error', sprintf(
                                            '<b class="text-gray-50">%s</b> : excepted <b class="text-gray-50">%d</b>, got <b class="text-gray-50">%d</b>',
                                            $pokedex['name'],
                                            $pokedex['caught'],
                                            $number
                                        ));
                                        $hasError = true;
                                    }
                                    break;
                                }
                            }

                        }
                    }

                unlink($completePath);

                return !$hasError;

                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                    return false;
                }
            }
        } elseif ($form->isSubmitted() && $form->isValid() === false) {
            $this->addFlash('error', 'Invalid form submission');
            return false;
        }

        return true;
    }
}
