<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Entity\WeddingHall;
use App\Repository\WeddingHallRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/salon', name: 'app_salon_')]
class ExplorerController extends AbstractController
{
    /**
     * Kök Dizin (Root)
     * Admin ise tüm salonları, Salon Yöneticisi ise sadece kendi salonunun içini görür.
     */
    #[Route('/', name: 'index')]
    public function index(WeddingHallRepository $hallRepository): Response
    {
        $user = $this->getUser();

        // SADECE SUPER ADMIN tüm salonları görür
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $halls = $hallRepository->findAll();

            return $this->render('explorer/index.html.twig', [
                'type' => 'root',
                'items' => $halls,
                'breadcrumbs' => ['Dosya Yöneticisi' => null]
            ]);
        }

        // Salon Yöneticisi (ROLE_ADMIN) için: Kendi salonunu bul ve direkt içine yönlendir.
        $userHall = $user->getWeddingHall();

        if (!$userHall) {
            throw $this->createAccessDeniedException('Size atanmış bir salon bulunamadı. Lütfen sistem yöneticisi ile iletişime geçin.');
        }

        return $this->redirectToRoute('app_salon_hall', ['id' => $userHall->getId()]);
    }

    /**
     * 2. Seviye: Bir Salonun İçi (Düğünleri Listeler)
     */
    #[Route('/icerik/{id}', name: 'hall')]
    public function showHall(WeddingHall $hall, AdminUrlGenerator $adminUrlGenerator): Response
    {
        // TenantVoter devreye girer. Başka salonun ID'si yazılırsa 403 fırlatır!
        $this->denyAccessUnlessGranted('TENANT_VIEW', $hall);

        // Yeni Düğün Ekleme Formuna Giden URL (setDashboard eklendi)
        $addWeddingUrl = $adminUrlGenerator
            ->unsetAll()
            ->setDashboard(\App\Controller\Admin\DashboardController::class) // YENİ EKLENEN SATIR
            ->setController(\App\Controller\Admin\WeddingCrudController::class)
            ->setAction('new')
            ->generateUrl();

        return $this->render('explorer/index.html.twig', [
            'type' => 'hall',
            'hall' => $hall,
            'items' => $hall->getWeddings(),
            'breadcrumbs' => [
                'Dosya Yöneticisi' => $this->isGranted('ROLE_SUPER_ADMIN') ? $this->generateUrl('app_salon_index') : null,
                $hall->getName() => null
            ],
            'addUrl' => $addWeddingUrl
        ]);
    }

    /**
     * 3. Seviye: Bir Düğünün İçi (Fotoğrafları/Alt Klasörleri Listeler)
     */
    #[Route('/dugun/{id}', name: 'wedding')]
    public function showWedding(Wedding $wedding, AdminUrlGenerator $adminUrlGenerator): Response
    {
        // TenantVoter yine devrede.
        $this->denyAccessUnlessGranted('TENANT_VIEW', $wedding);

        $hall = $wedding->getWeddingHall();

        // Fotoğraf Yükleme Formuna Giden URL (setDashboard eklendi)
        $addPhotoUrl = $adminUrlGenerator
            ->unsetAll()
            ->setDashboard(\App\Controller\Admin\DashboardController::class) // YENİ EKLENEN SATIR
            ->setController(\App\Controller\Admin\PhotoCrudController::class)
            ->setAction('new')
            ->set('wedding_id', $wedding->getId())
            ->generateUrl();

        return $this->render('explorer/index.html.twig', [
            'type' => 'wedding',
            'wedding' => $wedding,
            'hall' => $hall,
            'items' => $wedding->getPhotos(),
            'breadcrumbs' => [
                'Dosya Yöneticisi' => $this->isGranted('ROLE_SUPER_ADMIN') ? $this->generateUrl('app_salon_index') : null,
                $hall->getName() => $this->generateUrl('app_salon_hall', ['id' => $hall->getId()]),
                $wedding->getTitle() => null
            ],
            'addUrl' => $addPhotoUrl
        ]);
    }
}
