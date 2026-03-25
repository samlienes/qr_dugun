<?php

namespace App\Controller;

use App\Repository\WeddingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request, WeddingRepository $weddingRepository): Response
    {
        $weddingCode = $request->query->get('weddingCode');

        if ($request->isMethod('POST')) {
            // DÜZELTME: CSRF Koruması eklendi
            $csrfToken = $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('search_wedding', $csrfToken)) {
                $this->addFlash('error', 'Düğün bulunamadı, lütfen bilgileri kontrol edin ve tekrar deneyin.');
                return $this->redirectToRoute('app_home');
            }

            $submittedCode = $request->request->get('wedding_code');
            $wedding = $weddingRepository->findOneBy(['weddingCode' => strtoupper($submittedCode)]);

            if ($wedding) {
                return $this->redirectToRoute('app_wedding_teaser', [
                    'weddingCode' => $wedding->getWeddingCode()
                ]);
            }

            $this->addFlash('error', 'Girdiğiniz kod hatalıdır. Lütfen bilgilerinizi kontrol ederek tekrar deneyiniz.');
        }

        return $this->render('main/index.html.twig', [
            'weddingCode' => $request->query->get('weddingCode')
        ]);
    }

    #[Route('/yetkisiz-erisim', name: 'app_access_denied', methods: ['GET'])]
    public function accessDenied(): Response
    {
        $this->addFlash('error', 'Bu sayfayı görüntülemek için yeterli yetkiniz bulunmamaktadır. Lütfen sistem yöneticisi ile iletişime geçiniz.');
        return $this->redirectToRoute('app_home');
    }
}
