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
    description: 'Verilen telefon numarasına sahip kullanıcıyı admin yapar.',
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
        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles(array_unique($roles));
            $this->entityManager->flush();
            $io->success('Kullanıcıya başarıyla admin yetkisi verildi kanka! Artık panele girebilirsin.');
        } else {
            $io->note('Bu kullanıcı zaten admin.');
        }

        return Command::SUCCESS;
    }
}
