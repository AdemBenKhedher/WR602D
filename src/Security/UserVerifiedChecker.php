<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class UserVerifiedChecker implements BadgeInterface
{
    public function __construct()
    {
    }

    public function isResolved(): bool
    {
        return true;
    }
    public function checkCredentials($credentials, $user): bool
    {
        if (!$user instanceof User) {
            return true;
        }
        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException(
                'Veuillez vérifier votre email avant de vous connecter.'
            );
        }
        return true;
    }
}
