<?php

namespace App\Security\Voter;

use App\Entity\AppUser;
use App\Entity\Wedding;
use App\Entity\WeddingHall;
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
        // Bu voter sadece TENANT_VIEW veya TENANT_EDIT yetkisi istendiğinde çalışır
        return in_array($attribute, [self::VIEW, self::EDIT])
            && ($subject instanceof WeddingHall || $subject instanceof Wedding);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Kullanıcı giriş yapmamışsa direkt reddet
        if (!$user instanceof AppUser) {
            return false;
        }

        // Süper Admin her salonu görebilir ve düzenleyebilir, ona hep izin ver
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // Kullanıcının atandığı bir salon yoksa (sadece normal müşteriyse) hiçbir yeri göremez
        $userHall = $user->getWeddingHall();
        if (!$userHall) {
            return false;
        }

        // Eğer girilmeye çalışılan yer Düğün Salonu klasörü ise:
        if ($subject instanceof WeddingHall) {
            // Sisteme kayıtlı olduğu salon ID'si ile girmek istediği salon ID'si aynı mı?
            return $subject->getId() === $userHall->getId();
        }

        // Eğer girilmeye çalışılan yer bir Düğünün içi ise:
        if ($subject instanceof Wedding) {
            // Düğünün yapıldığı salon ID'si ile kullanıcının yetkili olduğu salon ID'si aynı mı?
            return $subject->getWeddingHall() && $subject->getWeddingHall()->getId() === $userHall->getId();
        }

        return false;
    }
}
