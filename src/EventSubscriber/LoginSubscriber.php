<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSubscriber implements EventSubscriberInterface
{
    private bool $shouldClearCookie = false;

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        // Flag to clear cookie on next response
        $this->shouldClearCookie = true;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->shouldClearCookie) {
            return;
        }

        $response = $event->getResponse();
        
        // Clear the welcome banner cookie by setting it expired
        $response->headers->setCookie(
            Cookie::create('welcome-banner-dismissed')
                ->withExpires(new \DateTime('-1 day'))
                ->withPath('/')
                ->withSameSite(Cookie::SAMESITE_LAX)
        );

        $this->shouldClearCookie = false;
    }
}
