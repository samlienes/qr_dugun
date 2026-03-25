<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Entity\Photo;
use App\Entity\UserContract;
use App\Repository\WeddingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WeddingController extends AbstractController
{
    #[Route('/w/{weddingCode}/welcome', name: 'app_wedding_teaser', methods: ['GET'])]
    public function teaser(Wedding $wedding, Request $request): Response
    {
        // Session'a hedef düğün kodunu kaydet
        $request->getSession()->set('target_wedding_code', $wedding->getWeddingCode());

        return $this->render('wedding/index.html.twig', [
            'wedding' => $wedding
        ]);
    }

    #[Route('/wedding/join', name: 'app_wedding', methods: ['POST'])]
    public function join(Request $request, WeddingRepository $weddingRepository): Response
    {
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('join_wedding', $csrfToken)) {
            $this->addFlash('danger', 'Geçersiz istek işlemi (CSRF).');
            return $this->redirectToRoute('app_home');
        }

        $code = $request->request->get('wedding_code');
        $wedding = $weddingRepository->findOneBy(['weddingCode' => $code]);

        if (!$wedding) {
            $this->addFlash('danger', 'Hatalı düğün kodu girdiniz!');
            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_wedding_show', [
            'weddingCode' => $wedding->getWeddingCode()
        ]);
    }

    #[Route('/wedding/{weddingCode}', name: 'app_wedding_show', methods: ['GET'])]
    public function show(Wedding $wedding, EntityManagerInterface $entityManager): Response
    {
        $appUser = $this->getUser();

        if ($appUser) {
            // Veritabanından güncel kullanıcıyı çek
            $managedUser = $entityManager->getRepository(\App\Entity\AppUser::class)->find($appUser->getId());

            if ($managedUser && !$managedUser->getJoinedWeddings()->contains($wedding)) {
                $managedUser->addJoinedWedding($wedding);
                $entityManager->flush();
            }
        }

        // Onaylanmış fotoğraf sayısını getir
        $photoCount = $entityManager->getRepository(Photo::class)->count([
            'wedding' => $wedding,
            'status' => 'approved'
        ]);

        return $this->render('wedding/show.html.twig', [
            'wedding' => $wedding,
            'isLoggedIn' => ($appUser !== null),
            'photoCount' => $photoCount
        ]);
    }

    #[Route('/wedding/{weddingCode}/upload', name: 'app_wedding_upload', methods: ['GET'])]
    public function uploadPage(Wedding $wedding): Response
    {
        return $this->render('wedding/upload.html.twig', [
            'wedding' => $wedding
        ]);
    }

    #[Route('/wedding/{weddingCode}/gallery', name: 'app_wedding_gallery', methods: ['GET'])]
    public function gallery(Wedding $wedding, EntityManagerInterface $entityManager): Response
    {
        $photos = $entityManager->getRepository(Photo::class)->findBy([
            'wedding' => $wedding,
            'status' => 'approved'
        ], ['uploadedAt' => 'DESC']);

        return $this->render('wedding/gallery.html.twig', [
            'wedding' => $wedding,
            'photos' => $photos
        ]);
    }

    #[Route('/wedding/{weddingCode}/profile', name: 'app_wedding_profile', methods: ['GET'])]
    public function profile(Wedding $wedding, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $request->getSession()->set('_security.main.target_path', $request->getUri());
            $this->addFlash('error', 'Profilini görmek için önce giriş yapmalısın.');
            return $this->redirectToRoute('app_login');
        }

        $userContracts = $entityManager->getRepository(UserContract::class)->findBy(['appUser' => $user]);

        $photoCounts = [];
        // Kullanıcının katıldığı düğünlerdeki fotoğraf sayılarını hesapla
        foreach ($user->getJoinedWeddings() as $jw) {
            $count = $entityManager->getRepository(Photo::class)->count([
                'appUser' => $user,
                'wedding' => $jw
            ]);
            $photoCounts[$jw->getId()] = $count;
        }

        return $this->render('wedding/profile.html.twig', [
            'wedding' => $wedding,
            'user' => $user,
            'userContracts' => $userContracts,
            'photoCounts' => $photoCounts
        ]);
    }
}
