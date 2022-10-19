<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
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

        $manager->persist($site);
        $manager->persist($user);
        $manager->flush();

        $ville = new Ville();
        $ville->setNom("Rennes");
        $ville->setCodePostal("35000");

        $etat = new Etat();
        $etat->setNom("Créée");

        $etat2 = new Etat();
        $etat2->setNom("Ouverte");

        $lieu = new Lieu();
        $lieu->setNom("Place Saint-Anne");
        $lieu->setRue("27 Rue Legraverend");
        $lieu->setLatitude("48.11448");
        $lieu->setLongitude("-1.680525");
        $lieu->setVille($ville);

        $sortie = new Sortie();
        $sortie->setNom("Sortie au bar");
        $sortie->setDateHeureDebut(new \DateTime('11/11/2022'));
        $sortie->setDuree("120");
        $sortie->setDateLimiteInscription(new \DateTime('09/11/2022'));
        $sortie->setNbInscriptionsMax("25");
        $sortie->setInfosSortie("Une nouvelle sortie s'annonce");
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($user);
        $sortie->setLieu($lieu);

        $sortie2 = new Sortie();
        $sortie2->setNom("Sortie au bowling");
        $sortie2->setDateHeureDebut(new \DateTime('11/18/2022'));
        $sortie2->setDuree("120");
        $sortie2->setDateLimiteInscription(new \DateTime('11/16/2022'));
        $sortie2->setNbInscriptionsMax("25");
        $sortie2->setInfosSortie("Une nouvelle sortie s'annonce");
        $sortie2->setOrganisateur($user);
        $sortie2->setEtat($etat2);
        $sortie2->setLieu($lieu);


        $manager->persist($ville);
        $manager->persist($etat);
        $manager->persist($etat2);
        $manager->persist($lieu);
        $manager->persist($sortie);
        $manager->persist($sortie2);

        $manager->flush();
    }
}
