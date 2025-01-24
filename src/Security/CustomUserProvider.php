<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomUserProvider implements UserProviderInterface
{
    // Constructor that injects the UserRepository dependency to interact with the database
    public function __construct(private readonly UserRepository $userRepository)
    {

    }

    // This method is responsible for loading a user based on the identifier (email or username)
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // First try to find the user by email, if not found, try by username
        $user = $this->userRepository->findOneBy(['email' => $identifier])
            ?? $this->userRepository->findOneBy(['username' => $identifier]);

        // If no user is found, throw an exception
        if (!$user) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        // Return the user object if found
        return $user;
    }

    // This method is called to refresh a user object, typically used for reloading the user data from the database
    public function refreshUser(UserInterface $user): UserInterface
    {
        // Check if the provided user is an instance of the User class
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Invalid user class.');
        }

        // Reload the user by their identifier (e.g., email or username) and return the refreshed user
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    // This method checks if the provided class is supported by this user provider (in this case, it's for the User class or its subclasses)
    public function supportsClass(string $class): bool
    {
        // Only support the User class or classes that extend User
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
