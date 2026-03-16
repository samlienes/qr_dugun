<?php
namespace App\Controller;

use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityController extends AbstractController
{
    // --- 1. GİRİŞ YAP (LOGIN) EKRANI ---
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

            // ESKİ SİSTEM: Fotoğraf yükleme vs. için senin id tutma mantığın
            $request->getSession()->set('app_user_id', $user->getId());

            // YENİ SİSTEM: Symfony'nin güvenlik duvarında oturumu başlatır (Hatasız yöntem)
            $security->login($user);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig');
    }

    // --- ÇIKIŞ YAP (LOGOUT) ---
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Request $request, Security $security): Response
    {
        $request->getSession()->remove('app_user_id');
        $security->logout(false);
        return $this->redirectToRoute('app_home');
    }

    // --- 2. ŞİFREMİ UNUTTUM (TELEFON GİRME) ---
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $phone = $request->request->get('phoneNumber');
            $user = $entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone]);

            if (!$user) {
                $this->addFlash('error', 'Numaranız sisteme kayıtlı değil.');
                return $this->redirectToRoute('app_forgot_password');
            }

            $code = (string)rand(100000, 999999);
            $user->setVerificationCode($code);
            $entityManager->flush();

            error_log("---------- ŞİFRE SIFIRLAMA KODU: $code ----------");

            $request->getSession()->set('reset_phone', $phone);
            return $this->redirectToRoute('app_forgot_password_verify');
        }
        return $this->render('security/forgot_password.html.twig');
    }

    // --- 3. ŞİFREMİ UNUTTUM (KOD DOĞRULAMA) ---
    #[Route('/forgot-password/verify', name: 'app_forgot_password_verify', methods: ['GET', 'POST'])]
    public function forgotPasswordVerify(Request $request, EntityManagerInterface $entityManager): Response
    {
        $phone = $request->getSession()->get('reset_phone');
        if (!$phone) return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            $user = $entityManager->getRepository(AppUser::class)->findOneBy([
                'phoneNumber' => $phone,
                'verificationCode' => $code
            ]);

            if ($user) {
                $user->setVerificationCode(null);
                $entityManager->flush();
                $request->getSession()->set('reset_authorized', true);
                return $this->redirectToRoute('app_reset_password');
            }
            $this->addFlash('error', 'Doğrulama kodu hatalı!');
        }
        return $this->render('security/forgot_password_verify.html.twig', ['phoneNumber' => $phone]);
    }

    // --- 4. YENİ ŞİFRE OLUŞTURMA ---
    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!$request->getSession()->get('reset_authorized')) return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            $phone = $request->getSession()->get('reset_phone');
            $user = $entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone]);

            if ($user) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $entityManager->flush();

                $request->getSession()->remove('reset_phone');
                $request->getSession()->remove('reset_authorized');

                $this->addFlash('success', 'Şifreniz başarıyla güncellendi! Yeni şifrenizle giriş yapabilirsiniz.');
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('security/reset_password.html.twig');
    }
}
