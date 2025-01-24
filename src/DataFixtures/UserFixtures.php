<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private \Faker\Generator $faker;

    // Constructor: injects the password hasher service and initializes Faker for generating fake data
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create();  // Create a new Faker instance
    }

    // This method is called to load the fixture data into the database
    public function load(ObjectManager $manager): void
    {
        // Array of possible user roles
        $roles = ['ROLE_USER', 'ROLE_MANAGER', 'ROLE_ADMIN'];

        // Loop to create 20 users with random data
        for($i = 0; $i < 20; $i++) {
            $user = new User();

            // Set fake user data using Faker
            $user->setFirstname($this->faker->firstName);  // Random first name
            $user->setLastname($this->faker->lastName);    // Random last name
            $user->setEmail($this->faker->safeEmail);      // Random email address
            $user->setUsername($this->faker->userName);    // Random username

            // Assign a random role from the $roles array
            $user->setRoles([$roles[rand(0, count($roles) - 1)]]);

            // Hash the password using the password hasher and set it
            $password = $this->passwordHasher->hashPassword($user, '12345678');  // Default password
            $user->setPassword($password);

            // Persist the user entity (prepare it to be saved in the database)
            $manager->persist($user);
        }

        // Flush all persisted entities to the database
        $manager->flush();
    }
}
