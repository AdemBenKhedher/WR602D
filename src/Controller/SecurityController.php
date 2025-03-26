<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;
use LogicException;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        if ($this->getUser()) {

            /** @var User $user */
            $user = $this->getUser();
            if (!$user->isVerified()) {
                $this->addFlash('error', 'Votre compte n\'est pas vérifié.
                Veuillez vérifier votre email pour activer votre compte.');
                $this->container->get('security.token_storage')->setToken(null);
                return $this->redirectToRoute('app_login');
            }
            return $this->redirectToRoute('app_Home');
        }


        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException(
            'This method can be blank-it will be intercepted by the logout key on your firewall.'
        );
    }
}
