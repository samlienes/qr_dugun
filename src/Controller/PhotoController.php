<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Entity\Wedding;
use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PhotoController extends AbstractController
{
    #[Route('/wedding/{weddingCode}/upload', name: 'app_photo_upload', methods: ['POST'])]
    public function upload(
        string                 $weddingCode,
        Request                $request,
        SluggerInterface       $slugger,
        EntityManagerInterface $entityManager
    ): Response
    {
        // DÜZELTME: CSRF Kontrolü
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('photo_upload', $csrfToken)) {
            return $this->json(['status' => 'error', 'message' => 'Geçersiz güvenlik tokeni (CSRF).'], 403);
        }

        $wedding = $entityManager->getRepository(Wedding::class)->findOneBy(['weddingCode' => $weddingCode]);

        // DÜZELTME: Düğün bulunamazsa 404 dön
        if (!$wedding) {
            return $this->json(['status' => 'error', 'message' => 'Düğün bulunamadı.'], 404);
        }

        /** @var AppUser $appUser */
        $appUser = $this->getUser();
        if (!$appUser) {
            return $this->json(['status' => 'error', 'message' => 'Lütfen önce giriş yap kanka.'], 403);
        }

        $photoFile = $request->files->get('photo');
        $message = $request->request->get('message');

        // DÜZELTME: Dosya yoksa spesifik 400 hatası
        if (!$photoFile) {
            return $this->json(['status' => 'error', 'message' => 'Lütfen bir fotoğraf seçin.'], 400);
        }

        // DÜZELTME: Dosya uzantısı (MIME type) ve boyut doğrulama (Maksimum 5MB)
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($photoFile->getMimeType(), $allowedMimeTypes)) {
            return $this->json(['status' => 'error', 'message' => 'Sadece JPG, PNG veya WEBP formatında resim yükleyebilirsiniz.'], 400);
        }
        if ($photoFile->getSize() > 5 * 1024 * 1024) {
            return $this->json(['status' => 'error', 'message' => 'Dosya boyutu en fazla 5MB olabilir.'], 400);
        }

        $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

        try {
            $photoFile->move($this->getParameter('photos_directory'), $newFilename);

            $photo = new Photo();
            $photo->setFilename($newFilename);
            $photo->setWedding($wedding);
            $photo->setAppUser($appUser);
            $photo->setStatus('pending'); // Unutma: Onaylanana kadar galeride çıkmaz!
            $photo->setIpAddress($request->getClientIp());

            if (!empty($message)) {
                $photo->setMessage($message);
            }

            $entityManager->persist($photo);
            $entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Anın kaydedildi kanka! Onaydan sonra görünecek.'
            ]);
        } catch (\Exception $e) {
            // Hatanın ne olduğunu tarayıcı konsolunda görmek için:
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
