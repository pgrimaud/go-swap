<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CustomList;
use App\Entity\User;
use App\Form\CustomListType;
use App\Repository\CustomListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CustomListController extends AbstractController
{
    public function __construct(
        private readonly CustomListRepository $customListRepository,
        private readonly EntityManagerInterface $entityManager,
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

    #[Route('/lists/new', name: 'app_custom_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $customList = new CustomList();
        $customList->setUser($user);

        $form = $this->createForm(CustomListType::class, $customList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customList);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your list has been created successfully!');

            return $this->redirectToRoute('app_custom_lists');
        }

        return $this->render('custom_list/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/lists/{id}/edit', name: 'app_custom_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $customList = $this->customListRepository->find($id);

        if (!$customList) {
            throw $this->createNotFoundException('List not found.');
        }

        // Check that the user owns this list
        if ($customList->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot edit this list.');
        }

        $form = $this->createForm(CustomListType::class, $customList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Your list has been updated successfully!');

            return $this->redirectToRoute('app_custom_list_edit', ['id' => $id]);
        }

        return $this->render('custom_list/edit.html.twig', [
            'form' => $form,
            'customList' => $customList,
        ]);
    }

    #[Route('/lists/{id}', name: 'app_custom_list_view', methods: ['GET'])]
    public function view(int $id): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $customList = $this->customListRepository->find($id);

        if (!$customList) {
            throw $this->createNotFoundException('List not found.');
        }

        // Check that the user owns this list
        if ($customList->getUser() !== $user) {
            throw $this->createAccessDeniedException('You cannot view this list.');
        }

        return $this->render('custom_list/view.html.twig', [
            'customList' => $customList,
        ]);
    }
}
