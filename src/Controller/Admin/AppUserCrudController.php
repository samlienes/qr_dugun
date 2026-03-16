<?php
namespace App\Controller\Admin;

use App\Entity\AppUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

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
TelephoneField::new('phoneNumber', 'Telefon'),
BooleanField::new('isVerified', 'Doğrulandı mı?'),
ChoiceField::new('roles', 'Roller')->setChoices([
'Kullanıcı' => 'ROLE_USER',
'Yönetici' => 'ROLE_ADMIN'
])->allowMultipleChoices(),
];
}
}
