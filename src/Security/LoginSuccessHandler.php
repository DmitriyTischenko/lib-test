<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Получаем роли пользователя
        $roles = $token->getRoleNames();

        // Перенаправляем в зависимости от роли
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_book_index')); // Маршрут для админа
        }

        return new RedirectResponse($this->router->generate('app_default')); // Маршрут для обычного пользователя
    }
}
