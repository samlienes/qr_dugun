<?php

namespace App\Controller;

use App\Repository\WeddingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request, WeddingRepository $weddingRepository): Response
    {
        // 1. URL üzerinden gelen bir kod varsa yakala (Hata almamak için kritik)
        $weddingCode = $request->query->get('weddingCode');

        if ($request->isMethod('POST')) {
            $submittedCode = $request->request->get('wedding_code');

            // 2. Adım: Veritabanı kontrolü (Eşleşme sağlanamazsa hata döner)
            $wedding = $weddingRepository->findOneBy(['weddingCode' => strtoupper($submittedCode)]);

            if ($wedding) {
                return $this->redirectToRoute('app_wedding_show', [
                    'weddingCode' => $wedding->getWeddingCode()
                ]);
            }

            // Resmi Hata Mesajı
            $this->addFlash('error', 'Girdiğiniz kod hatalıdır. Lütfen bilgilerinizi kontrol ederek tekrar deneyiniz.');
        }
// src/Controller/MainController.php
        return $this->render('main/index.html.twig', [
            'weddingCode' => $request->query->get('weddingCode') // URL'deki kodu yakalar
        ]);
    }
}
