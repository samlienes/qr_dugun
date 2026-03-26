<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// routePath değeri /salon olarak güncellendi
#[AdminDashboard(routePath: '/salon', routeName: 'salon_admin')]
#[IsGranted('ROLE_ADMIN')]
class SalonDashboardController extends AbstractDashboardController
{
public function index(): Response
{
if (isset($_GET['crudAction']) || isset($_GET['crudControllerFqcn'])) {
return parent::index();
}

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

yield MenuItem::section('Yönetim');

$urlGen = $this->container->get(AdminUrlGenerator::class)->setDashboard(self::class);

// Görevli kendi salonunun odalarını, düğünlerini ve fotoğraflarını yönetebilsin
yield MenuItem::linkToUrl('Odalarım', 'fas fa-door-open', $urlGen->unsetAll()->setController(WeddingRoomCrudController::class)->generateUrl());
yield MenuItem::linkToUrl('Düğünler', 'fas fa-ring', $urlGen->unsetAll()->setController(WeddingCrudController::class)->generateUrl());
yield MenuItem::linkToUrl('Fotoğraflar', 'fas fa-camera', $urlGen->unsetAll()->setController(PhotoCrudController::class)->generateUrl());
}
}
