<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Please sign in');
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }

    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'admin@go-swap.com',
            'password' => 'admin123',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/');
        $crawler = $client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function testFailedLoginWithWrongPassword(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'admin@go-swap.com',
            'password' => 'wrongpassword',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        // Login first
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'admin@go-swap.com',
            'password' => 'admin123',
        ]);
        $client->submit($form);

        // Logout
        $client->request('GET', '/logout');

        $this->assertResponseRedirects('/login');
    }
}
