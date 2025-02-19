<?php

namespace App\EventSubscriber;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener {
    public function onSymfonyComponentSecurityHttpEventLoginSuccessEvent(LoginSuccessEvent $loginEvent) {
        $passport = $loginEvent->getPassport();
        $passport->addBadge(new RememberMeBadge());

        // Add _remember_me from JSON body to attributes
        $request = $loginEvent->getRequest();
        $data = json_decode($request->getContent());
        $request->attributes->set('_remember_me', $data->_remember_me ?? '');
    }
}
