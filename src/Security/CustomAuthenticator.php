<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

// https://symfony.com/doc/current/security.html#authentication-events
class CustomAuthenticator extends AbstractAuthenticator
{
    // Using TargetPathTrait to handle the redirection after login
    use TargetPathTrait;

    // Constant for the login route name
    private const LOGIN_ROUTE_NAME = 'login';

    // Constant for the profile route name
    private const PROFILE_ROUTE_NAME = 'profile';

    // Constructor that takes the URL generator to generate login URL
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {

    }

    // This method checks if the current request supports this authenticator (i.e., if it's a POST request and the login URL matches)
    public function supports(Request $request): ?bool
    {
        // Only supports POST requests to the login route
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getBaseUrl().$request->getPathInfo();
    }

    // This method handles the authentication process
    public function authenticate(Request $request): Passport
    {
        // Retrieve the login credentials (identifier and password) from the request
        $credentials = [
            'identifier' => $request->request->get('identifier'), // Could be email or username
            'password' => $request->request->get('password'),
        ];

        // Create an array of badges (additional information used for authentication)
        $badges = [
            // Adding a CSRF token badge for security purposes
            new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token'))
        ];

        // Check if the "remember me" option is checked in the request and add the RememberMe badge if true
        $rememberMe = $request->request->get('_remember_me', false);
        if ($rememberMe) {
            $badges[] = new RememberMeBadge();
        }

        // Create a Passport object which contains the credentials and badges for the authentication process
        return new Passport(
            new UserBadge($credentials['identifier']), // UserBadge contains the user identifier (email or username)
            new PasswordCredentials($credentials['password']), // PasswordCredentials for user password
            $badges // Adding the badges (CSRF and RememberMe)
        );
    }

    // This method is called when authentication is successful
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // If there is a target path stored in the session (e.g., where the user was trying to go before being redirected to login),
        // redirect to that path, otherwise redirect to the "profile" page
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Clear sensitive information like password after authentication
        $user = $token->getUser();

        if ($user instanceof UserInterface) {
            $user->eraseCredentials(); // Erase sensitive data (like password)
        }

        return new RedirectResponse(self::PROFILE_ROUTE_NAME); // Redirect to profile page after login success
    }

    // This method is called when authentication fails
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // If the request has a session, store the authentication error in the session
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        // Redirect to the login page in case of failure
        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url); // Redirect to login page on failure
    }

    // This method is called to start the authentication process (e.g., when the user is not authenticated)
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Redirect to the login page if the authentication process starts
        return new RedirectResponse(self::LOGIN_ROUTE_NAME); // Redirect to the login route
    }

    // This method generates the URL for the login page
    public function getLoginUrl(Request $request): string
    {
        // Generate the login URL using the URL generator
        return $this->urlGenerator->generate(self::LOGIN_ROUTE_NAME);
    }
}
