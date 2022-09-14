<?php

namespace App\EventListener;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use League\Bundle\OAuth2ServerBundle\Event\UserResolveEvent;

final class UserResolveListener
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    public function __construct(UserProviderInterface $userProvider, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userProvider = $userProvider;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function onUserResolve(UserResolveEvent $event): void
    {
        try {
            $user = $this->userProvider->loadUserByIdentifier($event->getUsername());
        } catch (AuthenticationException $e) {
            return;
        }

        if (null === $user || !($user instanceof PasswordAuthenticatedUserInterface)) {
            return;
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $event->getPassword())) {
            return;
        }

        $event->setUser($user);
    }
}
