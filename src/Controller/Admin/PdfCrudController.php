<?php

namespace App\Controller\Admin;

use App\Entity\File;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class PdfCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return File::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('PDF')
            ->setEntityLabelInPlural('PDFs')
            ->setSearchFields(['id', 'name'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        yield $this->createIdField('id')->hideOnForm();
        yield $this->createAssociationField('user', 'Utilisateur')
            ->formatValue(function ($value, $entity) {
                return $entity->getUser() ? $entity->getUser()->getLastname() : 'Aucun utilisateur';
            });
        yield $this->createTextField('name', 'Nom du fichier');
        yield $this->createDateTimeField('created_at', 'Date de création')->hideOnForm();

        // Note: If you need to display/edit title and description,
        // make sure these fields exist in your File entity
        // yield $this->createTextField('title', 'Titre');
        // yield $this->createTextareaField('description');
    }

    // Helper methods using the recommended EasyAdmin factory pattern
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function createIdField(string $propertyName): IdField
    {
        return IdField::new($propertyName);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function createTextField(string $propertyName, ?string $label = null): TextField
    {
        return TextField::new($propertyName, $label);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function createTextareaField(string $propertyName, ?string $label = null): TextareaField
    {
        return TextareaField::new($propertyName, $label);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function createDateTimeField(string $propertyName, ?string $label = null): DateTimeField
    {
        return DateTimeField::new($propertyName, $label);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function createAssociationField(string $propertyName, ?string $label = null): AssociationField
    {
        return AssociationField::new($propertyName, $label);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
