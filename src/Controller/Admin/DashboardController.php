<?php
// src/Controller/Admin/DashboardController.php

namespace App\Controller\Admin;

use App\Entity\AppUser;
use App\Entity\Wedding;
use App\Entity\Photo;
use App\Entity\Contract;
use App\Entity\WeddingHall;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // YÖNLENDİRME HATASI BURADAN KAYNAKLANIYORDU, BU ŞEKİLDE DÜZELTTİK:
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
            ->setController(WeddingCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('QR Düğün Anı Yönetim');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Düğün Yönetimi');
        yield MenuItem::linkToRoute('Düğünler', 'fas fa-ring', 'admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => WeddingCrudController::class
        ]);
        yield MenuItem::linkToRoute('Düğün Salonları', 'fas fa-map-marker-alt', 'admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => WeddingHallCrudController::class
        ]);

        yield MenuItem::section('Kullanıcı ve İçerik');
        yield MenuItem::linkToRoute('Kullanıcılar', 'fas fa-users', 'admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => AppUserCrudController::class
        ]);
        yield MenuItem::linkToRoute('Fotoğraflar', 'fas fa-camera', 'admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => PhotoCrudController::class
        ]);

        yield MenuItem::section('Yasal');
        yield MenuItem::linkToRoute('Sözleşmeler', 'fas fa-file-contract', 'admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => ContractCrudController::class
        ]);
        yield MenuItem::linkToRoute('Sözleşme Onayları', 'fas fa-check-signature', 'admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => UserContractCrudController::class
        ]);
    }
}
