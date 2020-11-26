<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');

        for($i=0;$i<20;$i++){
            $post = new Post();
            $post -> setTitre($faker->sentence($nbWords = 2, $variableNbWords = true))
                ->setContent($faker->sentence($nbWords = 10, $variableNbWords = true))
                ->setUrlAlias(" url\article ")
                ->setPublishedAt($faker->dateTimeBetween('-2 months'));
            $manager->persist($post);
        }

        $manager->flush();
    }
}
