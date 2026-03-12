<?php
namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\UserContract;
use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

            if ($entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone])) {
                $this->addFlash('error', 'Bu numara zaten kayıtlı. Lütfen giriş yap.');
                return $this->redirectToRoute('app_login');
            }

            // Yeni AppUser oluşturuluyor
            $user = new AppUser();
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setPhoneNumber($phone);
            $user->setIsVerified(false);

            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $code = (string)rand(100000, 999999);
            $user->setVerificationCode($code);

            $entityManager->persist($user);

            // --- YENİ EKLENEN SÖZLEŞME ONAY KISMI ---
            $latestContract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['version' => 'DESC']);

            if ($latestContract) {
                $userContract = new UserContract();
                $userContract->setAppUser($user); // AppUser ile bağlıyoruz
                $userContract->setContract($latestContract);
                $userContract->setAcceptedAt(new \DateTimeImmutable());
                $userContract->setIpAddress($request->getClientIp());

                $entityManager->persist($userContract);
            }
            // ----------------------------------------

            $entityManager->flush();

            error_log("---------- KAYIT SMS KODU: $code ----------");

            return $this->render('registration/verify.html.twig', ['phoneNumber' => $phone]);
        }

        // GET isteği ile sayfa açılırken sözleşmeyi de gönderiyoruz
        $contract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['version' => 'DESC']);

        return $this->render('registration/register.html.twig', [
            'contract' => $contract
        ]);
    }

    // --- SİLİNEN VERIFY METODU BURADA ---
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

            // Kayıt sonrası doğrulama bitti, direkt Login sayfasına atıyoruz.
            $this->addFlash('success', 'Kayıt ve doğrulama başarılı! Şimdi şifrenle giriş yapabilirsin.');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('danger', 'Doğrulama kodu hatalı!');
        return $this->render('registration/verify.html.twig', ['phoneNumber' => $phone]);
    }
}
