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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PhotoController extends AbstractController
{
    #[Route('/wedding/{weddingCode}/upload', name: 'app_photo_upload', methods: ['POST'])]
    public function upload(
        string $weddingCode,
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $entityManager
    ): Response {
        $wedding = $entityManager->getRepository(Wedding::class)->findOneBy(['weddingCode' => $weddingCode]);
        $photoFile = $request->files->get('photo');

        // Session'dan AppUser'ı buluyoruz
        $appUserId = $request->getSession()->get('app_user_id');
        $appUser = $entityManager->getRepository(AppUser::class)->find($appUserId);

        if (!$appUser) {
            return $this->json(['status' => 'error', 'message' => 'Lütfen önce giriş yap kanka.'], 403);
        }

        if ($photoFile && $wedding) {
            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

            try {
                $photoFile->move($this->getParameter('photos_directory'), $newFilename);

                $photo = new Photo();
                $photo->setFilename($newFilename);
                $photo->setWedding($wedding);
                $photo->setAppUser($appUser);
                $photo->setUploadedAt(new \DateTimeImmutable());
                $photo->setStatus('pending');
                $photo->setIpAddress($request->getClientIp());

                $entityManager->persist($photo);
                $entityManager->flush();

                return $this->json([
                    'status' => 'success',
                    'filename' => $newFilename,
                    'message' => 'Anın kaydedildi, moderatör onayından sonra galeride görünecek!'
                ]);
            } catch (FileException $e) {
                return $this->json(['status' => 'error', 'message' => 'Dosya yüklenemedi.'], 500);
            }
        }

        return $this->json(['status' => 'error', 'message' => 'Geçersiz istek.'], 400);
    }
}
