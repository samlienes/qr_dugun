<?php

namespace App\Controller;

use App\Entity\UserContract;
use App\Repository\ContractRepository;
use App\Repository\UserContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContractController extends AbstractController
{
    #[Route('/contract', name: 'app_contract')]
    public function index(ContractRepository $contractRepository): Response
    {
        // En son aktif sözleşmeyi getirir
        $latestContract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['version' => 'DESC']);

        if (!$latestContract) {
            throw $this->createNotFoundException('Aktif bir sözleşme bulunamadı.');
        }

        return $this->render('contract/index.html.twig', [
            'contract' => $latestContract,
        ]);
    }

    #[Route('/contract/confirm', name: 'app_contract_confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        ContractRepository $contractRepository,
        EntityManagerInterface $entityManager,
        UserContractRepository $userContractRepository
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Giriş yapılmamışsa login'e fırlat
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $latestContract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['version' => 'DESC']);

        if ($latestContract) {
            // Kullanıcı bu sözleşmeyi daha önce onaylamış mı?
            $existing = $userContractRepository->findOneBy([
                'appUser' => $user,
                'contract' => $latestContract
            ]);

            if (!$existing) {
                $userContract = new UserContract();
                $userContract->setAppUser($user);
                $userContract->setContract($latestContract);
                $userContract->setAcceptedAt(new \DateTimeImmutable());

                // IP Adresini kaydediyoruz (Az önceki hatayı çözen satır)
                $userContract->setIpAddress($request->getClientIp());

                $entityManager->persist($userContract);
                $entityManager->flush();
            }
        }

        // Onay işleminden sonra ana sayfaya yönlendir
        return $this->redirectToRoute('app_home');
    }
}
