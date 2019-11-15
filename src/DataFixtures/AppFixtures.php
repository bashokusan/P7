<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Product;
use App\Entity\Client;
use App\Entity\ProductUser;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('FR-fr');

        $clients = [];
        for($c = 0; $c < 5; $c++){
          $client = new Client();
          $client->setEmail($faker->safeEmail)
               ->setPassword($this->encoder->encodePassword($client, 'password'));
          $manager->persist($client);
          $clients[] = $client;
        }

        for ($u = 0; $u < 10; $u++){
            $user = new ProductUser();
            $user->setName($faker->userName)
                ->setEmail($faker->safeEmail)
                ->setClient($faker->randomElement($clients))
                ->setPhone($faker->phoneNumber)
                ->setAddress($faker->address);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
