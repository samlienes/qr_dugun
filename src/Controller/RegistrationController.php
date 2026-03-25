<?php
namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\UserContract;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ContractRepository $contractRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $phone = $request->request->get('phoneNumber');
            $plainPassword = $request->request->get('password');
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');

            // 1. Kullanıcı zaten var mı kontrol et
            if ($entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone])) {
                $this->addFlash('error', 'Bu numara zaten kayıtlı. Lütfen giriş yap.');
                return $this->redirectToRoute('app_login');
            }

            // 2. Yeni AppUser oluşturuluyor
            $user = new AppUser();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPhoneNumber($phone);
            $user->setIsVerified(false);
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // SMS Kodu simülasyonu
            $code = (string)rand(100000, 999999);
            $user->setVerificationCode($code);

            // Kullanıcıyı persist et (henüz flush etme)
            $entityManager->persist($user);

            // 3. --- KRİTİK: SÖZLEŞME ONAY KAYDI (user_contract tablosu için) ---
            $latestContract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['id' => 'DESC']);

            if ($latestContract) {
                $userContract = new UserContract();
                $userContract->setAppUser($user); // Onaylayan kullanıcı
                $userContract->setContract($latestContract); // Onaylanan sözleşme
                $userContract->setAcceptedAt(new \DateTimeImmutable()); // Onay tarihi
                $userContract->setIpAddress($request->getClientIp()); // Onay IP adresi

                $entityManager->persist($userContract);
            }

            // Tüm işlemleri tek seferde veritabanına yaz
            $entityManager->flush();

            error_log("---------- KAYIT SMS KODU [{$phone}]: {$code} ----------");

            return $this->render('registration/verify.html.twig', ['phoneNumber' => $phone]);
        }

        // GET isteği: Sayfayı sözleşme içeriğiyle birlikte göster
        $contract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['id' => 'DESC']);

        return $this->render('registration/register.html.twig', [
            'contract' => $contract
        ]);
    }

    #[Route('/verify', name: 'app_verify', methods: ['POST'])]
    public function verify(Request $request, EntityManagerInterface $entityManager): Response
    {
        $phone = $request->request->get('phoneNumber');
        $code = $request->request->get('code');

        $user = $entityManager->getRepository(AppUser::class)->findOneBy([
            'phoneNumber' => $phone,
            'verificationCode' => $code
        ]);

        if ($user) {
            $user->setIsVerified(true);
            $user->setVerificationCode(null);
            $entityManager->flush();

            $this->addFlash('success', 'Kayıt ve doğrulama başarılı! Şimdi giriş yapabilirsin.');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('danger', 'Doğrulama kodu hatalı!');
        return $this->render('registration/verify.html.twig', ['phoneNumber' => $phone]);
    }
}
