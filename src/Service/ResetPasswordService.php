<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class ResetPasswordService
{
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private EntityManagerInterface $entityManager;
    private MailerInterface $mailer;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    public function processPasswordResetRequest(string $email): ?ResetPasswordToken
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return null;
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return null;
        }

        $email = (new TemplatedEmail())
            ->from(new Address('mailer@mmipdf.fr', 'MailerMmi'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context(['resetToken' => $resetToken]);

        $this->mailer->send($email);

        return $resetToken;
    }

    public function generateFakeResetToken(): ResetPasswordToken
    {
        $fakeToken = $this->resetPasswordHelper->generateFakeResetToken();

        return new ResetPasswordToken(
            $fakeToken->getToken(),
            $fakeToken->getExpiresAt(),
            time()
        );
    }

    public function validateTokenAndFetchUser(string $token, TranslatorInterface $translator): ?User
    {
        try {
            return $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return null;
        }
    }

    public function updateUserPassword(
        User $user,
        string $plainPassword,
        UserPasswordHasherInterface $passwordHasher
    ): void {
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $this->entityManager->flush();
    }
}
