<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CustomListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicListController extends AbstractController
{
    public function __construct(
        private readonly CustomListRepository $customListRepository,
    ) {
    }

    #[Route('/list/{uid}', name: 'app_public_list', methods: ['GET'])]
    public function view(string $uid): Response
    {
        $customList = $this->customListRepository->findOneBy(['uid' => $uid]);

        if (!$customList) {
            throw $this->createNotFoundException('List not found.');
        }

        // Only public lists are accessible
        if (!$customList->isPublic()) {
            throw $this->createNotFoundException('List not found.');
        }

        return $this->render('public_list/view.html.twig', [
            'customList' => $customList,
        ]);
    }
}
