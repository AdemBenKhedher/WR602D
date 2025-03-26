<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ResetPasswordRequest;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/mon-compte')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/supprimer', name: 'app_account_delete', methods: ['POST'])]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): JsonResponse {
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete_account', $request->request->get('_token'))) {
            try {
                $resetPasswordRequests = $entityManager->getRepository(ResetPasswordRequest::class)->findBy(
                    ['user' => $user]
                );
                foreach ($resetPasswordRequests as $resetPasswordRequest) {
                    $entityManager->remove($resetPasswordRequest);
                }
                foreach ($user->getFiles() as $file) {
                    $entityManager->remove($file);
                }
                $entityManager->remove($user);
                $entityManager->flush();
                $tokenStorage->setToken(null);
                $session->invalidate();
                return new JsonResponse([
                    'success' => true,
                    'redirectUrl' => $urlGenerator->generate('app_login'),
                ]);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la suppression de votre compte: ' . $e->getMessage()
                ], 400);
            }
        }

        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide.'], 400);
    }

    #[Route('/become-admin', name: 'app_become_admin', methods: ['POST'])]
    public function becomeAdmin(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le CSRF token
        if (!$this->isCsrfTokenValid('promote-admin', $request->request->get('_token'))) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide'
            ]);
        }
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $user->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes maintenant administrateur.');

        return $this->redirectToRoute('app_account');
    }
}
