<?php
namespace App\Controller\Admin;

use App\Entity\Wedding;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class WeddingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Wedding::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Düğün Adı'),
            TextField::new('weddingCode', 'Düğün Kodu (Örn: AHMETYSA24)'),
            DateTimeField::new('weddingDate', 'Düğün Tarihi ve Saati'),
            AssociationField::new('weddingHall', 'Düğün Salonu'),
            AssociationField::new('activeContract', 'Aktif Sözleşme'),
        ];
    }
}
