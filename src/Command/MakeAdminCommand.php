<?php

namespace App\Command;

use App\Entity\AppUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:make-admin',
    description: 'Verilen telefon numarasına sahip kullanıcıyı SÜPER ADMİN (Sistem Sahibi) yapar.',
)]
class MakeAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->addArgument('phone', InputArgument::REQUIRED, 'Kullanıcının telefon numarası');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $phone = $input->getArgument('phone');

        $user = $this->entityManager->getRepository(AppUser::class)->findOneBy(['phoneNumber' => $phone]);

        if (!$user) {
            $io->error(sprintf('%s numaralı sistemde kayıtlı bir kullanıcı bulunamadı!', $phone));
            return Command::FAILURE;
        }

        $roles = $user->getRoles();

        // BURASI DEĞİŞTİ: Artık ROLE_SUPER_ADMIN veriyoruz!
        if (!in_array('ROLE_SUPER_ADMIN', $roles)) {
            $roles[] = 'ROLE_SUPER_ADMIN';
            $roles[] = 'ROLE_ADMIN'; // Ne olur ne olmaz bunu da ekleyelim
            $user->setRoles(array_unique($roles));

            $this->entityManager->flush();
            $io->success('Harika! Kullanıcıya başarıyla SÜPER ADMİN yetkisi verildi. Tüm panele erişebilirsin.');
            $io->warning('ÖNEMLİ: Yetkilerin aktif olması için lütfen tarayıcıda siteden ÇIKIŞ YAPIP (Logout) TEKRAR GİRİŞ YAPIN!');
        } else {
            $io->note('Bu kullanıcı zaten SÜPER ADMİN yetkisine sahip.');
        }

        return Command::SUCCESS;
    }
}
