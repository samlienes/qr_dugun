<?php
namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/salon-admin', routeName: 'salon_admin')]
#[IsGranted('ROLE_ADMIN')]
class SalonDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        if (isset($_GET['crudAction']) || isset($_GET['crudControllerFqcn'])) {
            return parent::index();
        }

        // Ana ekrana girince direkt 1. Klasör olan "İç Salonlar" sayfasına yönlendir
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
            ->unsetAll()
            ->setDashboard(self::class)
            ->setController(WeddingRoomCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Salon Yönetim Paneli');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Ana Sayfa', 'fa fa-home');

        yield MenuItem::section('Klasörler');

        $urlGen = $this->container->get(AdminUrlGenerator::class)->setDashboard(self::class);

        // Menüde SADECE en üst klasör olan İç Salonlar görünecek
        yield MenuItem::linkToUrl('📁 İç Salonlarım (Odalar)', 'fas fa-door-open', $urlGen->unsetAll()->setController(WeddingRoomCrudController::class)->generateUrl());
    }
}
