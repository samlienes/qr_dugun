<?php

namespace App\Controller\Admin;

use App\Entity\Wedding;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

class WeddingCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private RequestStack $requestStack;

    // AdminUrlGenerator ve RequestStack servislerini güvenli bir şekilde dahil ediyoruz
    public function __construct(AdminUrlGenerator $adminUrlGenerator, RequestStack $requestStack)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->requestStack = $requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Wedding::class;
    }

    // Arayüz başlıklarını daha anlaşılır hale getiriyoruz
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Düğün')
            ->setEntityLabelInPlural('Düğünler')
            ->setPageTitle(Crud::PAGE_INDEX, 'Planlanan Düğünler');
    }

    // 1. ADIM: LİSTELEME EKRANINI KISITLAMA
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        // Salon Yöneticisi ise sadece kendi salonunun düğünlerini görsün
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && $user && $user->getWeddingHall()) {
            $qb->andWhere('entity.weddingHall = :hall')
                ->setParameter('hall', $user->getWeddingHall());
        }

        // $_GET yerine Request nesnesi üzerinden güvenli parametre alımı
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->query->has('room_id') && is_numeric($request->query->get('room_id'))) {
            $qb->andWhere('entity.weddingRoom = :room_id')
                ->setParameter('room_id', (int) $request->query->get('room_id'));
        }

        return $qb;
    }

    // 2. ADIM: YENİ KAYITTA SALONU OTOMATİK ATAMA
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $user = $this->getUser();

        // Yeni bir düğün eklenirken, Süper Admin değilse otomatik olarak kullanıcının salonunu ata
        if ($entityInstance instanceof Wedding && !$this->isGranted('ROLE_SUPER_ADMIN') && $user && $user->getWeddingHall()) {
            $entityInstance->setWeddingHall($user->getWeddingHall());
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    // 3. ADIM: URL KORUMASI VE AKSİYONLAR
// WeddingRoomCrudController.php içindeki configureActions metodunu bununla değiştirin:
    public function configureActions(Actions $actions): Actions
    {
        $viewWeddings = Action::new('viewWeddings', '📁 Düğünleri Gör', 'fas fa-folder-open')
            ->linkToUrl(function (WeddingRoom $entity) {
                return $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(WeddingCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('room_id', $entity->getId())
                    ->generateUrl();
            })
            ->setCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, $viewWeddings)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Yeni Oda Ekle');
            })
            // GÜVENLİK: Başka salonun odasına URL'den girmeyi engelle
            ->setPermission(Action::EDIT, 'TENANT_EDIT')
            ->setPermission(Action::DELETE, 'TENANT_EDIT')
            ->setPermission(Action::DETAIL, 'TENANT_VIEW');
    }

    // 4. ADIM: FORM ALANLARINDAKİ (DROPDOWN) SEÇENEKLERİ KISITLAMA
    public function configureFields(string $pageName): iterable
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->getUser();

        yield TextField::new('title', 'Düğün Adı');
        yield TextField::new('weddingCode', 'Düğün Kodu');
        yield DateTimeField::new('date', 'Düğün Tarihi');

        // Belirli bir oda içinden listeleme yapılmıyorsa Oda seçimini göster
        if (!$request || !$request->query->has('room_id')) {

            $roomField = AssociationField::new('weddingRoom', 'İç Salon / Oda');

            // GÜVENLİK: Form açıldığında, sadece giriş yapan yöneticinin salonuna ait odalar dropdown'da listelensin.
            // Bu sayede "Arena" görevlisi "Yıldız" salonuna ait odaları kesinlikle göremez.
            if (!$this->isGranted('ROLE_SUPER_ADMIN') && $user && $user->getWeddingHall()) {
                $roomField->setQueryBuilder(function (QueryBuilder $qb) use ($user) {
                    return $qb->andWhere('entity.weddingHall = :hall')
                        ->setParameter('hall', $user->getWeddingHall());
                });
            }

            yield $roomField;
        }

        // Sadece Süper Adminler düğünün hangi Ana Tesise (Salon) ait olduğunu seçebilir/görebilir
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield AssociationField::new('weddingHall', 'Ana Tesis');
        }
    }
}
