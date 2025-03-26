<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setSearchFields(['id', 'email', 'firstname', 'lastname'])
            ->setPaginatorPageSize(10);
    }
    /**
     * @SuppressWarnings("StaticAccess")
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('firstname', 'Prénom');
        yield TextField::new('lastname', 'Nom');
        yield ArrayField::new('roles', 'Rôles');


        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('plainPassword', 'Mot de passe')
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->setHelp(Crud::PAGE_EDIT === $pageName ? 'Laissez vide pour conserver le mot de passe actuel' : '');
        }

        yield BooleanField::new('isVerified', 'Vérifié');
        yield AssociationField::new('subscription', 'Abonnement')
            ->setFormTypeOption('choice_label', 'name')
            ->renderAsNativeWidget();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;

        if ($user->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setPassword($hashedPassword);
        }


        if (!$user->isVerified()) {
            $user->setIsVerified(true);
        }

        parent::persistEntity($entityManager, $user);
    }

/** @SuppressWarnings("ElseExpression") */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var User $user */
        $user = $entityInstance;


        if ($user->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setPassword($hashedPassword);
        } else {
            $originalUser = $entityManager->getRepository(User::class)->find($user->getId());
            if ($originalUser) {
                $user->setPassword($originalUser->getPassword());
            }
        }

        parent::updateEntity($entityManager, $user);
    }
}
