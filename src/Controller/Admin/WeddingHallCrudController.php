<?php
namespace App\Controller\Admin;

use App\Entity\WeddingHall;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class WeddingHallCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return WeddingHall::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Salon Adı'),
            NumberField::new('latitude', 'Enlem (Latitude)'),
            NumberField::new('longitude', 'Boylam (Longitude)'),
        ];
    }
}
