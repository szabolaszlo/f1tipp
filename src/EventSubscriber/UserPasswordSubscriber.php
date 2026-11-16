<?php

namespace App\EventSubscriber;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Az adminban (UserCrudController) a jelszó sima szövegmező: amit az admin beír, azt itt hash-eljük,
 * különben nyers szövegként kerülne az adatbázisba, és a user nem tudna vele belépni.
 * Szerkesztéskor a mező a meglévő hash-t mutatja – ha nem nyúltak hozzá, azt nem hash-eljük újra.
 */
class UserPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'hashPassword',
            BeforeEntityUpdatedEvent::class => 'hashPassword',
        ];
    }

    public function hashPassword(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $user = $event->getEntityInstance();

        if (!$user instanceof User) {
            return;
        }

        $password = $user->getPassword();

        if ($password === '' || password_get_info($password)['algo'] !== null) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
    }
}
