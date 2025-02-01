<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // This route is for the login page, accessible via the '/login' path
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the user is already logged in. If so, redirect them to the 'profile' route.
        if ($this->getUser()) {
            return $this->redirectToRoute('profile');
        }

        // Get the last authentication error (if any)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get the last username entered by the user during login attempts
        $lastUsername = $authenticationUtils->getLastUsername();

        // Render the login form template with the last username and the error (if any)
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // This route is for logging out the user, accessible via the '/logout' path
    // The logout logic is handled automatically by Symfony's firewall, so we leave the method empty
    #[Route(path: '/logout', name: 'logout')]
    public function logout(): RedirectResponse
    {
        // Redirect to the login page
        return $this->redirectToRoute('login');
    }

    // This route is for the user profile page, accessible via the '/profile' path
    #[Route(path: '/profile', name: 'profile')]
    public function profile(): Response
    {
        // Render the user's profile page template
        return $this->render('user_profile/index.html.twig');
    }

    #[NoReturn] #[Route(path: '/loginManually', name: 'loginManually')]
    public function loginManually(Request $request, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager): Response {
        $id = $request->query->get('id', '1');

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        if (!$user) {
            return $this->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage->setToken($token);

        // return $this->json(['status' => 'success', 'message' => 'User logged in successfully.']);
        return $this->redirectToRoute('login');
    }
}
