<?php
namespace App\Controller\Admin;

use App\Entity\WeddingRoom;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class WeddingRoomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return WeddingRoom::class; }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        if ($user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $user->getWeddingHall()) {
            $qb->andWhere('entity.weddingHall = :hall')->setParameter('hall', $user->getWeddingHall());
        }
        return $qb;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();
        if ($entityInstance instanceof WeddingRoom && $user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && $user->getWeddingHall()) {
            $entityInstance->setWeddingHall($user->getWeddingHall());
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    // 📁 KLASÖRÜ AÇ BUTONU (Özel 'room_id' parametresi gönderiyor)
    public function configureActions(Actions $actions): Actions
    {
        $viewWeddings = Action::new('viewWeddings', '📁 Düğünleri Gör', 'fas fa-folder-open')
            ->linkToUrl(function (WeddingRoom $entity) {
                $urlGenerator = $this->container->get(AdminUrlGenerator::class);
                return $urlGenerator
                    ->unsetAll()
                    ->setController(WeddingCrudController::class)
                    ->setAction('index')
                    ->set('room_id', $entity->getId()) // Garantili yöntem
                    ->generateUrl();
            });

        return $actions->add(Crud::PAGE_INDEX, $viewWeddings);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'İç Salon/Oda Adı');

        $user = $this->getUser();
        if ($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            yield AssociationField::new('weddingHall', 'Bağlı Olduğu Ana Tesis');
        }
    }
}
