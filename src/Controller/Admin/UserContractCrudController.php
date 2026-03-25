<?php

namespace App\Controller\Admin;

use App\Entity\UserContract;
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

    public function configureFields(string $pageName): iterable
    {
        return [
            // Kullanıcı bilgileri (İsim, Soyisim, Tel otomatik gelir)
            AssociationField::new('appUser', 'Kullanıcı'),

            // Onaylanan Sözleşme Başlığı
            AssociationField::new('contract', 'Sözleşme'),

            // Kaydettiğimiz IP ve Tarih
            TextField::new('ipAddress', 'IP Adresi'),
            DateTimeField::new('acceptedAt', 'Onay Zamanı'),
        ];
    }
}
