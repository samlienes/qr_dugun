<?php

namespace App\Controller;

use App\Entity\Wedding;
use App\Repository\WeddingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Bunu ekledik
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeddingController extends AbstractController
{
    /**
     * Formdan gelen kodu kontrol eden ve yönlendiren kısım
     */
    #[Route('/wedding/join', name: 'app_wedding', methods: ['POST'])]
    public function join(Request $request, WeddingRepository $weddingRepository): Response
    {
        $code = $request->request->get('wedding_code');

        // Veritabanında kodu ara
        $wedding = $weddingRepository->findOneBy(['weddingCode' => $code]);

        if (!$wedding) {
            $this->addFlash('danger', 'Hatalı düğün kodu girdiniz!');
            return $this->redirectToRoute('app_home');
        }

        // Bulursa show metoduna, düğün koduyla beraber gönder
        return $this->redirectToRoute('app_wedding_show', [
            'weddingCode' => $wedding->getWeddingCode()
        ]);
    }

    /**
     * Düğün detay sayfasını (Galeriyi) gösteren kısım
     */

    #[Route('/wedding/{weddingCode}', name: 'app_wedding_show', methods: ['GET'])]
    public function show(Wedding $wedding, Request $request): Response
    {
        return $this->render('wedding/show.html.twig', [
            'wedding' => $wedding,
            'isLoggedIn' => $request->getSession()->has('app_user_id') // Giriş kontrolü
        ]);
    }
}
