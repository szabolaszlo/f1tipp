<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()
            || !$request->hasSession()
            || $request->isXmlHttpRequest()
            || str_contains($request->getRequestUri(), '_fragment')) {
            return;
        }

        $this->saveTargetPath($request->getSession(), 'main', $request->getUri());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest']
        ];
    }
}
