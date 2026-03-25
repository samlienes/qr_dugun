<?php
namespace App\Controller\Admin;

use App\Entity\Photo;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class PhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Photo::class; }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        if ($user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $user->getWeddingHall()) {
            $qb->join('entity.wedding', 'w')
                ->andWhere('w.weddingHall = :hall')
                ->setParameter('hall', $user->getWeddingHall());
        }

        // KESİN ÇÖZÜM: Klasör açıldıysa (URL'de wedding_id varsa) diğer fotoğrafları tamamen sil
        if (isset($_GET['wedding_id']) && is_numeric($_GET['wedding_id'])) {
            $qb->andWhere('entity.wedding = :wedding_id')
                ->setParameter('wedding_id', (int) $_GET['wedding_id']);
        }

        return $qb;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['uploadedAt' => 'DESC'])
            ->setPageTitle('index', '📁 Bu Düğünün Fotoğrafları');
    }

    public function configureFields(string $pageName): iterable
    {
        yield ImageField::new('filename', 'Fotoğraf')
            ->setBasePath('/uploads/photos')
            ->setUploadDir('public/uploads/photos')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false);

        // Fotoğraflar zaten klasördeyse Düğün ismini gizle, klasör dışından girilmişse göster
        if (!isset($_GET['wedding_id'])) {
            yield AssociationField::new('wedding', 'Düğün');
        }

        yield AssociationField::new('appUser', 'Yükleyen Kişi');
        yield DateTimeField::new('uploadedAt', 'Tarih')->setFormat('dd.MM.yyyy HH:mm')->hideOnForm();
    }
}
