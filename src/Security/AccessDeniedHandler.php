<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Kullanıcıyı başka sayfaya fırlatmaz (URL değişmez), sadece ekrana bu tasarımı basar ve 403 kodu döndürür.
        return new Response($this->twig->render('security/access_denied.html.twig'), 403);
    }
}
