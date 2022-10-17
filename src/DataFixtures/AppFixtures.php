<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $site = new Site();
        $site->setNom("Chartres-de-Bretagne");

        $user = new Participant();
        $user->setMail("maxime.kervadec@gmail.com");
        $user->setPseudo("Gecko");
        $user->setNom("Kervadec");
        $user->setPrenom("Maxime");
        $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $user->setSite($site);
        $password = "aaaaaaaa";
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setAdministrateur(0);
        $user->setActif(1);
        $manager->persist($site);
        $manager->persist($user);
        $manager->flush();
    }
}
