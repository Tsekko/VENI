<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Ville;
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


        $ville = new Ville();
        $ville->setNom("Rennes");
        $ville->setCodePostal("35000");

        $etat = new Etat();
        $etat->setNom("Créée");

        $lieu = new Lieu();
        $lieu->setNom("Place Saint-Anne");
        $lieu->setRue("27 Rue Legraverend");
        $lieu->setLatitude("48.11448");
        $lieu->setLongitude("-1.680525");
        $lieu->setVille($ville);


        $manager->persist($site);
        $manager->persist($user);
        $manager->persist($ville);
        $manager->persist($etat);
        $manager->persist($lieu);

        $manager->flush();

        $site = new Site();
        $site->setNom("Chartres-de-Bretagne");

        $user = new Participant();
        $user->setMail("fabrice.hure.35@gmail.com");
        $user->setPseudo("Bibiss");
        $user->setNom("Huré");
        $user->setPrenom("Fabrice");
        $user->setRoles(["ROLE_USER"]);
        $user->setSite($site);
        $password = "bonjour";
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setAdministrateur(0);
        $user->setActif(1);
        $manager->persist($site);
        $manager->persist($user);
        $manager->flush();
    }
}
