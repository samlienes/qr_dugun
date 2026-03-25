<?php
namespace App\Controller;

use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security): Response
    {
        if ($request->isMethod('POST')) {
            $phone = $request->request->get('phoneNumber');
            $plainPassword = $request->request->get('password');
            $user = $entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone]);

            if (!$user || !$passwordHasher->isPasswordValid($user, $plainPassword)) {
                $this->addFlash('error', 'Numaranız veya şifreniz hatalı.');
                return $this->redirectToRoute('app_login');
            }

            $security->login($user);

            // AKILLI YÖNLENDİRME: Session'da düğün kodu varsa oraya git
            $targetWeddingCode = $request->getSession()->get('target_wedding_code');
            if ($targetWeddingCode) {
                $request->getSession()->remove('target_wedding_code');
                return $this->redirectToRoute('app_wedding_show', ['weddingCode' => $targetWeddingCode]);
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Security $security): Response
    {
        $security->logout(false);
        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $phone = $request->request->get('phoneNumber');
            $user = $entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone]);

            if (!$user) {
                $this->addFlash('error', 'Bu numaraya ait bir hesap bulunamadı.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // SMS Kodu oluşturma simülasyonu
            $code = (string)rand(100000, 999999);
            $user->setVerificationCode($code);
            $entityManager->flush();

            error_log("---------- ŞİFRE SIFIRLAMA SMS KODU: $code ----------");

            return $this->render('security/forgot_password_verify.html.twig', [
                'phoneNumber' => $phone
            ]);
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route(path: '/forgot-password-verify', name: 'app_forgot_password_verify', methods: ['POST'])]
    public function forgotPasswordVerify(Request $request, EntityManagerInterface $entityManager): Response
    {
        $phone = $request->request->get('phoneNumber');
        $code = $request->request->get('code');

        $user = $entityManager->getRepository(AppUser::class)->findOneBy([
            'phoneNumber' => $phone,
            'verificationCode' => $code
        ]);

        if ($user) {
            // Kod doğruysa, şifre sıfırlama sayfasına izin ver
            $request->getSession()->set('reset_password_phone', $phone);
            return $this->redirectToRoute('app_reset_password');
        }

        $this->addFlash('error', 'Doğrulama kodu hatalı!');
        return $this->render('security/forgot_password_verify.html.twig', [
            'phoneNumber' => $phone
        ]);
    }

    #[Route(path: '/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Session'dan kullanıcının numarasını çekiyoruz ki başkası direkt bu sayfaya giremesin
        $phone = $request->getSession()->get('reset_password_phone');

        if (!$phone) {
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $plainPassword = $request->request->get('password');
            $user = $entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone]);

            if ($user && $plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                // Güvenlik için doğrulama kodunu temizle
                $user->setVerificationCode(null);
                $entityManager->flush();

                // İşlem bittiği için session'ı temizle
                $request->getSession()->remove('reset_password_phone');

                $this->addFlash('success', 'Şifren başarıyla güncellendi. Şimdi giriş yapabilirsin.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig');
    }
}
