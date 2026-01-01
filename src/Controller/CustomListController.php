<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\CustomListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CustomListController extends AbstractController
{
    public function __construct(
        private readonly CustomListRepository $customListRepository,
    ) {
    }

    #[Route('/lists', name: 'app_custom_lists', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $customLists = $this->customListRepository->findAllByUser($user);

        return $this->render('custom_list/index.html.twig', [
            'customLists' => $customLists,
        ]);
    }
}
