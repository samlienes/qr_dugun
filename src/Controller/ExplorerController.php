<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Entity\WeddingHall;
use App\Repository\WeddingHallRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
// BURASI DEĞİŞTİ: Artık /panel değil /salon rotasında çalışacak
#[Route('/salon', name: 'app_salon_')]
class ExplorerController extends AbstractController
{
    /**
     * 1. Seviye: Kök Dizin (Root)
     * Admin ise tüm salonları, Salon Yöneticisi ise sadece kendi salonunun içini görür.
     */
    #[Route('/', name: 'index')]
    public function index(WeddingHallRepository $hallRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $halls = $hallRepository->findAll();

            return $this->render('explorer/index.html.twig', [
                'type' => 'root',
                'items' => $halls,
                'breadcrumbs' => ['Dosya Yöneticisi' => null]
            ]);
        }

        // Salon Yöneticisi (ROLE_ADMIN) giriş yaptığında:
        $userHall = $user->getWeddingHall();

        if (!$userHall) {
            throw $this->createAccessDeniedException('Size atanmış bir salon bulunamadı. Lütfen süper admin ile iletişime geçin.');
        }

        // Direkt kendi salonunun içine yönlendir. (İstediğiniz özellik)
        return $this->redirectToRoute('app_salon_hall', ['id' => $userHall->getId()]);
    }

    /**
     * 2. Seviye: Bir Salonun İçi (Düğünleri Listeler)
     */
    #[Route('/icerik/{id}', name: 'hall')]
    public function showHall(WeddingHall $hall): Response
    {
        // TenantVoter devreye girer. Başka salonun ID'si yazılırsa 403 Forbidden fırlatır! (İstediğiniz güvenlik)
        $this->denyAccessUnlessGranted('TENANT_VIEW', $hall);

        return $this->render('explorer/index.html.twig', [
            'type' => 'hall',
            'hall' => $hall,
            'items' => $hall->getWeddings(),
            'breadcrumbs' => [
                'Dosya Yöneticisi' => $this->isGranted('ROLE_SUPER_ADMIN') ? $this->generateUrl('app_salon_index') : null,
                $hall->getName() => null
            ]
        ]);
    }

    /**
     * 3. Seviye: Bir Düğünün İçi (Fotoğrafları/Galeriyi Listeler)
     */
    #[Route('/dugun/{id}', name: 'wedding')]
    public function showWedding(Wedding $wedding): Response
    {
        // TenantVoter yine devrede.
        $this->denyAccessUnlessGranted('TENANT_VIEW', $wedding);

        $hall = $wedding->getWeddingHall();

        return $this->render('explorer/index.html.twig', [
            'type' => 'wedding',
            'wedding' => $wedding,
            'hall' => $hall,
            'items' => $wedding->getPhotos(),
            'breadcrumbs' => [
                'Dosya Yöneticisi' => $this->isGranted('ROLE_SUPER_ADMIN') ? $this->generateUrl('app_salon_index') : null,
                $hall->getName() => $this->generateUrl('app_salon_hall', ['id' => $hall->getId()]),
                $wedding->getTitle() => null
            ]
        ]);
    }
}
