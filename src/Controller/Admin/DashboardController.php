<?php
namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

// YENİ KULLANIM BURASI (Route yerine AdminDashboard kullanılıyor ve class'ın üzerine yazılıyor)
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        if (isset($_GET['crudAction']) || isset($_GET['crudControllerFqcn'])) {
            return parent::index();
        }

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
            ->unsetAll()
            ->setController(WeddingCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('QR Düğün Yönetim Paneli');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Ana Sayfa', 'fa fa-home');

        yield MenuItem::section('İşletme Yönetimi');

        $urlGen = $this->container->get(AdminUrlGenerator::class);

        // Salon yöneticilerinin görebileceği alanlar
        yield MenuItem::linkToUrl('İç Salonlar (Odalar)', 'fas fa-door-open', $urlGen->unsetAll()->setController(WeddingRoomCrudController::class)->generateUrl());
        yield MenuItem::linkToUrl('Düğünler', 'fas fa-ring', $urlGen->unsetAll()->setController(WeddingCrudController::class)->generateUrl());
        yield MenuItem::linkToUrl('Fotoğraflar', 'fas fa-camera', $urlGen->unsetAll()->setController(PhotoCrudController::class)->generateUrl());

        // ==========================================
        // SADECE SÜPER ADMİNİN GÖRECEĞİ KISIMLAR
        // ==========================================
        yield MenuItem::section('Sistem Yönetimi')->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::linkToUrl('Düğün Salonları (Ana)', 'fas fa-map-marker-alt', $urlGen->unsetAll()->setController(WeddingHallCrudController::class)->generateUrl())->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToUrl('Kullanıcılar / Yöneticiler', 'fas fa-users', $urlGen->unsetAll()->setController(AppUserCrudController::class)->generateUrl())->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToUrl('Sözleşmeler', 'fas fa-file-contract', $urlGen->unsetAll()->setController(ContractCrudController::class)->generateUrl())->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToUrl('Sözleşme Onayları', 'fas fa-check-signature', $urlGen->unsetAll()->setController(UserContractCrudController::class)->generateUrl())->setPermission('ROLE_SUPER_ADMIN');
    }
}
