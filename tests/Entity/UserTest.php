<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');

        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('hashedpassword', $user->getPassword());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $this->assertSame('user@example.com', $user->getUserIdentifier());
    }

    public function testUserRoles(): void
    {
        $user = new User();

        // Default role
        $this->assertContains('ROLE_USER', $user->getRoles());

        // Set custom roles
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testUserHasRoleUserByDefault(): void
    {
        $user = new User();
        $roles = $user->getRoles();

        $this->assertIsArray($roles);
        $this->assertContains('ROLE_USER', $roles);
    }
}
