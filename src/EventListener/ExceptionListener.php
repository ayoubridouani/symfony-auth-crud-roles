<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

final class ExceptionListener
{
    private RouterInterface  $router;
    private Environment $twig;

    public function __construct(RouterInterface $router, Environment $twig)
    {
        $this->router = $router;
        $this->twig = $twig;
    }

    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedHttpException) {
            $content = $this->twig->render('errors/error403.html.twig');
            $response = new Response($content, 403);
            $event->setResponse($response);
        }
    }
}
