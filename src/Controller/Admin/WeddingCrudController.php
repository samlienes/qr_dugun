<?php
namespace App\Controller\Admin;

use App\Entity\Wedding;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class WeddingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Wedding::class; }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        // Salon Yöneticisi ise sadece kendi salonunun düğünlerini görsün
        if ($user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $user->getWeddingHall()) {
            $qb->andWhere('entity.weddingHall = :hall')->setParameter('hall', $user->getWeddingHall());
        }

        // Eğer belirli bir İç Salon (Oda) seçildiyse (WeddingRoomCrudController'dan geliyorsa)
        if (isset($_GET['room_id']) && is_numeric($_GET['room_id'])) {
            $qb->andWhere('entity.weddingRoom = :room_id')
                ->setParameter('room_id', (int) $_GET['room_id']);
        }

        return $qb;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();
        // Yeni bir düğün eklenirken, Süper Admin değilse otomatik olarak kullanıcının salonunu ata
        if ($entityInstance instanceof Wedding && $user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $user->getWeddingHall()) {
            $entityInstance->setWeddingHall($user->getWeddingHall());
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewPhotos = Action::new('viewPhotos', '📸 Fotoğrafları Gör')
            ->linkToUrl(function ($entity) {
                $urlGenerator = $this->container->get(\EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator::class);
                return $urlGenerator
                    ->unsetAll()
                    ->setController(PhotoCrudController::class)
                    ->setAction('index')
                    ->set('wedding_id', $entity->getId())
                    ->generateUrl();
            })
            ->setCssClass('btn btn-info text-white');

        return $actions->add(Crud::PAGE_INDEX, $viewPhotos);
    }

    public function configureFields(string $pageName): iterable
    {
        $user = $this->getUser();

        // Değişken isimleri (title ve date) Entity ile eşleşecek şekilde düzeltildi
        yield TextField::new('title', 'Düğün Adı');
        yield TextField::new('weddingCode', 'Düğün Kodu');
        yield DateTimeField::new('date', 'Düğün Tarihi');

        // Belirli bir oda içinden listeleme yapılmıyorsa Oda seçimini göster
        if (!isset($_GET['room_id'])) {
            yield AssociationField::new('weddingRoom', 'İç Salon / Oda');
        }

        // Sadece Süper Adminler düğünün hangi Ana Tesise (Salon) ait olduğunu seçebilir/görebilir
        if ($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            yield AssociationField::new('weddingHall', 'Ana Tesis');
        }
    }
}
