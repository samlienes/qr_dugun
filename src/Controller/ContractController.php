<?php

namespace App\Controller\Admin;

use App\Entity\UserContract;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserContractCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserContract::class;
    }

    // Sözleşme onay kayıtları sadece okunur olmalı (değiştirilemez/silinemez)
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('appUser', 'Kullanıcı'),
            AssociationField::new('contract', 'Sözleşme'),
            TextField::new('ipAddress', 'IP Adresi'),
            DateTimeField::new('acceptedAt', 'Onay Tarihi'),
        ];
    }
}
