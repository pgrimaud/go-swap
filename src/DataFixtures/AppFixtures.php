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
        $username =['Nilzen', 'rzr75'];
        for ($i = 0; $i < 2; $i++) {
            $user = new User();
            $user->setUsername($username[$i]);
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                'test'
            ));
            $manager->persist($user);
        }
        $manager->flush();
    }
}
