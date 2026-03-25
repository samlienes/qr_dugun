<?php
namespace App\Controller\Admin;

use App\Entity\AppUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
class AppUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AppUser::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstName', 'Ad'),
            TextField::new('lastName', 'Soyad'),
            TelephoneField::new('phoneNumber', 'Telefon Numarası'),

            // Şifreyi sadece formda (eklerken/düzenlerken) göster, listede gizle
            TextField::new('password', 'Şifre')->hideOnIndex(),

            BooleanField::new('isVerified', 'Doğrulandı mı?'),

            // Kullanıcı Rolü Belirleme Alanı
            ChoiceField::new('roles', 'Yetki Seviyesi')
                ->setChoices([
                    'Müşteri / Kullanıcı' => 'ROLE_USER',
                    'Salon Yöneticisi' => 'ROLE_ADMIN',
                    'Sistem Sahibi (Süper Admin)' => 'ROLE_SUPER_ADMIN'
                ])
                ->allowMultipleChoices(),

            // Süper Admin bu alandan "Salon Yöneticisini" ilgili salona bağlar
            AssociationField::new('weddingHall', 'Sorumlu Olduğu Salon')
                ->setHelp('Eğer bu kişi bir salon yöneticisiyse, yöneteceği salonu seçin.'),
        ];
    }
}
