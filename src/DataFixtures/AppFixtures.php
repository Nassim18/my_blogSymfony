<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Cocur\Slugify\Slugify;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadPosts($manager);

    }

    public function loadPosts(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');
        $slugify = new Slugify();

        for($i=0;$i<20;$i++){
            $post = new Post();
            $post -> setTitre($faker->sentence($nbWords = 2, $variableNbWords = true))
                ->setContent($faker->sentence($nbWords = 10, $variableNbWords = true))
                //->setUrlAlias(" url\article ")
                ->setUrlAlias($slugify->slugify($post->getTitre()))
                ->setUser($this->getReference('Nassim'))
                ->setPublishedAt($faker->dateTimeBetween('-2 months'));
            $manager->persist($post);
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
            $user = new User();
            $user -> setUsername('Neo18')
                ->setFullname('Nassim Sehout')
                ->setEmail('neo.dz18@gmail.com')
                ->setPassword(
                    $this->passwordEncoder->encodePassword(
                        $user,'azerty'
                    )
                );
                $this->addReference('Nassim',$user);
                $manager->persist($user);
                $manager->flush();
    }
}
