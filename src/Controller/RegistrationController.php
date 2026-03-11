<?php

// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User; // AppUser yerine User kullanıyoruz
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
#[Route('/register', name: 'app_register', methods: ['POST'])]
public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
$user = new User();
$user->setFullName($request->request->get('firstName') . ' ' . $request->request->get('lastName'));
$user->setPhone($request->request->get('phoneNumber'));
$user->setIsVerified(false);

// Şirket dökümanına göre e-posta login bilgisidir [cite: 86]
// Geçici olarak telefon numarasını email alanına veya benzersiz bir değere atayabilirsin
$user->setEmail($request->request->get('phoneNumber') . '@dugunani.com');

// Şifre dökümanda zorunlu değilse bile Security için boş geçilemez
$user->setPassword($passwordHasher->hashPassword($user, 'temporary_password'));

$code = (string)rand(100000, 999999);
$user->setVerificationCode($code);

$entityManager->persist($user);
$entityManager->flush();

error_log("---------- SMS KODU: $code ----------");

return $this->render('registration/verify.html.twig', [
'phoneNumber' => $user->getPhone(),
'weddingCode' => $request->request->get('weddingCode')
]);
}

#[Route('/verify', name: 'app_verify', methods: ['POST'])]
public function verify(Request $request, EntityManagerInterface $entityManager): Response
{
$phone = $request->request->get('phoneNumber');
$code = $request->request->get('code');
$weddingCode = $request->request->get('weddingCode');

$user = $entityManager->getRepository(User::class)->findOneBy([
'phone' => $phone,
'verificationCode' => $code
]);

if ($user) {
$user->setIsVerified(true);
$user->setVerificationCode(null); // Kod kullanıldıktan sonra temizliyoruz
$entityManager->flush();

// Giriş işlemini manuel olarak tetikleyebilir veya Security üzerinden yönlendirebilirsin
$this->addFlash('success', 'Doğrulama başarılı!');

return $this->redirectToRoute('app_wedding_show', ['weddingCode' => $weddingCode]);
}

$this->addFlash('danger', 'Kod hatalı!');
return $this->render('registration/verify.html.twig', [
'phoneNumber' => $phone,
'weddingCode' => $weddingCode
]);
}
}
