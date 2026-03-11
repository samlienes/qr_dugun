<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\ContractRepository;
use App\Repository\UserContractRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContractCheckerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private ContractRepository $contractRepository,
        private UserContractRepository $userContractRepository
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        // Sadece ana istekleri (main request) kontrol et, alt istekleri (sub-request) geç.
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        // Giriş yapmamışsa veya zaten sözleşme/login/logout sayfalarındaysa kontrol etme (sonsuz döngü olur)
        if (!$this->security->getUser() ||
            in_array($routeName, ['app_contract', 'app_contract_confirm', 'app_logout', 'app_login', '_wdt', '_profiler'])) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // En son aktif sözleşmeyi bul
        $latestContract = $this->contractRepository->findOneBy(['type' => 'user_agreement'], ['version' => 'DESC']);

        if ($latestContract) {
            // Kullanıcı bu sözleşmeyi onaylamış mı?
            $isAccepted = $this->userContractRepository->findOneBy([
                'appUser' => $user,
                'contract' => $latestContract
            ]);

            if (!$isAccepted) {
                // Onay yoksa sözleşme sayfasına fırlat
                $url = $this->urlGenerator->generate('app_contract');
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
