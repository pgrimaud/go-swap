<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        // Store flag in session to persist across redirects
        $session = $this->requestStack->getSession();
        $session->set('clear_welcome_banner', true);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $session = $this->requestStack->getSession();

        if (!$session->get('clear_welcome_banner', false)) {
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

        // Clear the session flag
        $session->remove('clear_welcome_banner');
    }
}
