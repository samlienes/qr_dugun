<?php

namespace App\Controller\Admin;

use App\Entity\Photo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('wedding', 'Düğün'),
            AssociationField::new('appUser', 'Yükleyen'),
            ImageField::new('filename', 'Fotoğraf')
                ->setBasePath('uploads/photos')
                ->setUploadDir('public/uploads/photos')
                ->setUploadedFileNamePattern('[randomhash].[extension]'),
            ChoiceField::new('status', 'Durum')
                ->setChoices([
                    'Beklemede' => 'pending',
                    'Onaylandı' => 'approved',
                    'Reddedildi' => 'rejected',
                ]),
            TextField::new('ipAddress', 'IP Adresi')->hideOnForm(),
            DateTimeField::new('uploadedAt', 'Tarih')->hideOnForm(),
        ];
    }
}
