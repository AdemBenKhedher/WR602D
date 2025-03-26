<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $entityManager;

    public function __construct(
        private EmailVerifier $emailVerifier,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if ($user) {
            return $this->redirectToRoute('app_Home');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            // Set isVerified to false by default
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('ademkhdher@gmail.com', 'Mailer'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Don't automatically log in the user - redirect to a thank you page
            $this->addFlash('success', 'Votre compte a été créé. 
            Veuillez vérifier votre email pour confirmer votre inscription.');
            return $this->redirectToRoute('app_registration_confirmation');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        try {
            $id = $request->query->get('id');
            if ($id) {
                $user = $this->entityManager->getRepository(User::class)->find($id);
                if ($user) {
                    $this->emailVerifier->handleEmailConfirmation($request, $user);

                    $this->addFlash('success', 'Votre adresse email a été vérifiée avec succès!
                     Vous pouvez maintenant vous connecter.');

                    return $this->render('registration/confirmation_success.html.twig');
                }
            }

            $this->addFlash('error', 'Le lien de vérification n\'est pas valide ou a expiré.');
            return $this->redirectToRoute('app_login');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }
    }

    #[Route('/registration/confirmation', name: 'app_registration_confirmation')]
    public function registrationConfirmation(): Response
    {
        return $this->render('registration/confirmation.html.twig');
    }
}
