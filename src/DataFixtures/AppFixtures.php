<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (['Nilzen', 'rzr75'] as $username) {
            $user = new User();
            $user->setUsername($username);
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                'test'
            ));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
