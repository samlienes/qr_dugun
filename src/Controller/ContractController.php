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
        /** @var \App\Entity\AppUser $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $latestContract = $contractRepository->findOneBy(['type' => 'user_agreement'], ['version' => 'DESC']);

        if ($latestContract) {
            $existing = $userContractRepository->findOneBy([
                'appUser' => $user,
                'contract' => $latestContract
            ]);

            if (!$existing) {
                $userContract = new UserContract();
                $userContract->setAppUser($user);
                $userContract->setContract($latestContract);
                $userContract->setAcceptedAt(new \DateTimeImmutable());
                $userContract->setIpAddress($request->getClientIp());

                $entityManager->persist($userContract);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_home');
    }
}
