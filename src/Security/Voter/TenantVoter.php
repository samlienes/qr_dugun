<?php

namespace App\Security\Voter;

use App\Entity\AppUser;
use App\Entity\Wedding;
use App\Entity\WeddingHall;
use App\Entity\WeddingRoom; // BUNU EKLEDİK
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TenantVoter extends Voter
{
    public const VIEW = 'TENANT_VIEW';
    public const EDIT = 'TENANT_EDIT';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // WeddingRoom da artık destekleniyor
        return in_array($attribute, [self::VIEW, self::EDIT])
            && ($subject instanceof WeddingHall || $subject instanceof Wedding || $subject instanceof WeddingRoom);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AppUser) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        $userHall = $user->getWeddingHall();
        if (!$userHall) {
            return false;
        }

        // 1. Düğün Salonu Kontrolü
        if ($subject instanceof WeddingHall) {
            return $subject->getId() === $userHall->getId();
        }

        // 2. Düğün (Wedding) Kontrolü
        if ($subject instanceof Wedding) {
            return $subject->getWeddingHall() && $subject->getWeddingHall()->getId() === $userHall->getId();
        }

        // 3. YENİ EKLENEN: İç Salon/Oda (WeddingRoom) Kontrolü
        if ($subject instanceof WeddingRoom) {
            return $subject->getWeddingHall() && $subject->getWeddingHall()->getId() === $userHall->getId();
        }

        return false;
    }
}
