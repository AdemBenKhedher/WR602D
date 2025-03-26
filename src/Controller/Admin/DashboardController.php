<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Subscription;
use App\Entity\File;
use App\Repository\UserRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\FileRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository,
        private FileRepository $fileRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->entityManager = $entityManager;
    }

    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {

        $stats = [
            'users' => $this->entityManager->getRepository(User::class)->count([]),
            'subscriptions' => $this->entityManager->getRepository(Subscription::class)->count([]),
            'files' => $this->entityManager->getRepository(File::class)->count([]),
        ];


        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);

        // Ancien code redirigeant vers la liste des utilisateurs
        // return $this->redirect(
        //     $this->adminUrlGenerator
        //         ->setController(UserCrudController::class)
        //         ->generateUrl()
        // );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration');
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Abonnements', 'fas fa-credit-card', Subscription::class);
        yield MenuItem::linkToCrud('Fichiers', 'fas fa-file-pdf', File::class);
    }
}
