<?php
namespace App\Controller\Admin;

use App\Entity\Contract;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class ContractCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contract::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Sözleşme Başlığı'),
            TextField::new('type', 'Türü (Standart: user_agreement)'),
            TextField::new('version', 'Versiyon (Örn: 1.0)'),
            DateTimeField::new('createdAt', 'Oluşturulma Tarihi'),
            TextEditorField::new('content', 'Sözleşme Metni (HTML Destekler)'),
        ];
    }
}
