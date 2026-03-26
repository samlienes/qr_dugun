<?php

namespace App\Controller\Admin;

use App\Entity\WeddingRoom;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class WeddingRoomCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    // AdminUrlGenerator'ı güvenli bir şekilde dahil ediyoruz (Constructor Injection)
    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return WeddingRoom::class;
    }

    // Arayüzdeki başlıkları daha anlaşılır hale getiriyoruz
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('İç Salon / Oda')
            ->setEntityLabelInPlural('İç Salonlar / Odalar')
            ->setPageTitle(Crud::PAGE_INDEX, 'Mevcut Odalarınız');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        // Rol kontrolünü isGranted ile yapmak her zaman daha güvenlidir
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && $user && $user->getWeddingHall()) {
            $qb->andWhere('entity.weddingHall = :hall')
                ->setParameter('hall', $user->getWeddingHall());
        }

        return $qb;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();

        // Yeni kayıt oluşturulurken Süper Admin değilse salon atamasını yapıyoruz
        if ($entityInstance instanceof WeddingRoom && !$this->isGranted('ROLE_SUPER_ADMIN') && $user && $user->getWeddingHall()) {
            $entityInstance->setWeddingHall($user->getWeddingHall());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    // 📁 KLASÖRÜ AÇ BUTONU
    public function configureActions(Actions $actions): Actions
    {
        $viewWeddings = Action::new('viewWeddings', '📁 Düğünleri Gör', 'fas fa-folder-open')
            ->linkToUrl(function (WeddingRoom $entity) {
                // Sınıfa enjekte ettiğimiz url generator'ı kullanıyoruz
                return $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(WeddingCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('room_id', $entity->getId())
                    ->generateUrl();
            })
            // Opsiyonel: Butonun rengini veya tasarımını belirleyebilirsiniz
            ->setCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $viewWeddings)
            // Yeni ekle butonunun metnini özelleştiriyoruz
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Yeni Oda Ekle');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'İç Salon/Oda Adı');

        // Yalnızca Süper Admin hangi ana tesise bağlı olduğunu seçebilsin/görebilsin
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield AssociationField::new('weddingHall', 'Bağlı Olduğu Ana Tesis');
        }
    }
}
