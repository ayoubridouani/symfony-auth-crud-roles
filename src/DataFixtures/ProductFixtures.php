<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    private \Faker\Generator $faker;
    public const PRODUCT_REFERENCE = 'product_reference';

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        // Loop to create 150 products with random data
        for($i = 0; $i < 150; $i++) {
            $product = new Product();

            // Set fake product data using Faker
            $product->setName(implode(' ', $this->faker->words(3)));
            $product->setDescription($this->faker->text(500));
            $product->setPrice($this->faker->randomFloat(2, 100, 500));
            //$product->setOwner($manager->find(User::class, rand(1, 20)));
            $product->setOwner($this->getReference(UserFixtures::USER_REFERENCE . rand(0, 19), User::class));

            // Persist the product entity (prepare it to be saved in the database)
            $manager->persist($product);
        }

        // Flush all persisted entities to the database
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
