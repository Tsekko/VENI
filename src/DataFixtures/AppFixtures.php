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

        $site = new Site();
        $site->setNom("Chartres-de-Bretagne");
        $manager->persist($site);

        $site1 = new Site();
        $site1->setNom("Nantes");
        $manager->persist($site1);

        $site2 = new Site();
        $site2->setNom("Quimper");
        $manager->persist($site2);

        $site3 = new Site();
        $site3->setNom("Niort");
        $manager->persist($site3);

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
        $manager->persist($user);


        $user1 = new Participant();
        $user1->setMail("fabrice.hure.35@gmail.com");
        $user1->setPseudo("Bibiss");
        $user1->setNom("Huré");
        $user1->setPrenom("Fabrice");
        $user1->setRoles(["ROLE_USER"]);
        $user1->setSite($site);
        $password = "bonjour";
        $user1->setPassword($this->encoder->encodePassword($user, $password));
        $user1->setAdministrateur(0);
        $user1->setActif(1);
        $manager->persist($user1);

        $user2 = new Participant();
        $user2->setMail("marieocelline@hotmail.fr");
        $user2->setPseudo("Mario");
        $user2->setNom("Ceschino");
        $user2->setPrenom("Marie");
        $user2->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $user2->setSite($site);
        $password = "12345678";
        $user2->setPassword($this->encoder->encodePassword($user, $password));
        $user2->setAdministrateur(1);
        $user2->setActif(1);
        $manager->persist($user2);

        $user3 = new Participant();
        $user3->setMail("delphine.lemee@gmail.fr");
        $user3->setPseudo("Dede");
        $user3->setNom("Le Mée");
        $user3->setPrenom("Delphine");
        $user3->setRoles(["ROLE_USER"]);
        $user3->setSite($site);
        $password = "12345678";
        $user3->setPassword($this->encoder->encodePassword($user, $password));
        $user3->setAdministrateur(0);
        $user3->setActif(1);
        $manager->persist($user3);


        $ville = new Ville();
        $ville->setNom("Rennes");
        $ville->setCodePostal(35000);
        $manager->persist($ville);

        $ville1 = new Ville();
        $ville1->setNom("Saint-Malo");
        $ville1->setCodePostal(35400);
        $manager->persist($ville1);


        $etat = new Etat();
        $etat->setNom("En création");
        $manager->persist($etat);

        $etat2 = new Etat();
        $etat2->setNom("Ouvert");
        $manager->persist($etat2);

        $etat3 = new Etat();
        $etat3->setNom("En cours");
        $manager->persist($etat3);

        $etat4 = new Etat();
        $etat4->setNom("Fermé");
        $manager->persist($etat4);

        $etat5 = new Etat();
        $etat5->setNom("Annulée");
        $manager->persist($etat5);


        $lieu = new Lieu();
        $lieu->setNom("Place Saint-Anne");
        $lieu->setRue("27 Rue Legraverend");
        $lieu->setLatitude(48.11448);
        $lieu->setLongitude(-1.680525);
        $lieu->setVille($ville);
        $manager->persist($lieu);

        $lieu1 = new Lieu();
        $lieu1->setNom("Cinéma Gaumont");
        $lieu1->setRue("Esplanade Charles de Gaulle");
        $lieu1->setLatitude(48.106123);
        $lieu1->setLongitude(-361.675681);
        $lieu1->setVille($ville);
        $manager->persist($lieu1);

        $lieu2 = new Lieu();
        $lieu2->setNom("Plage du Sillon");
        $lieu2->setRue("Chaussée du Sillon");
        $lieu2->setLatitude(48.659385);
        $lieu2->setLongitude(-361.999362);
        $lieu2->setVille($ville1);
        $manager->persist($lieu2);

        $lieu3 = new Lieu();
        $lieu3->setNom("Salle Glaz Arena");
        $lieu3->setRue("Chemin du Bois de la Justice");
        $lieu3->setLatitude(48.113577);
        $lieu3->setLongitude(-361.595531);
        $lieu3->setVille($ville);
        $manager->persist($lieu3);

        $sortie = new Sortie();
        $sortie->setNom("Sortie au bar");
        $sortie->setDateHeureDebut(new \DateTime('11/11/2022'));
        $sortie->setDuree(120);
        $sortie->setDateLimiteInscription(new \DateTime('09/11/2022'));
        $sortie->setNbInscriptionsMax(25);
        $sortie->setInfosSortie("Une nouvelle sortie s'annonce");
        $sortie->setEtat($etat);
        $sortie->setOrganisateur($user);
        $sortie->addParticipant($user);
        $sortie->addParticipant($user1);
        $sortie->addParticipant($user3);
        $sortie->setLieu($lieu);
        $sortie->setArchive(false);
        $manager->persist($sortie);

        $sortie2 = new Sortie();
        $sortie2->setNom("Sortie au bowling");
        $sortie2->setDateHeureDebut(new \DateTime('11/18/2022'));
        $sortie2->setDuree(120);
        $sortie2->setDateLimiteInscription(new \DateTime('11/16/2022'));
        $sortie2->setNbInscriptionsMax(25);
        $sortie2->setInfosSortie("Une nouvelle sortie s'annonce");
        $sortie2->setOrganisateur($user1);
        $sortie2->addParticipant($user);
        $sortie2->addParticipant($user2);
        $sortie2->addParticipant($user3);
        $sortie2->setEtat($etat2);
        $sortie2->setLieu($lieu);
        $sortie2->setArchive(false);
        $manager->persist($sortie2);

        $sortie3 = new Sortie();
        $sortie3->setNom("Cinéma");
        $sortie3->setDateHeureDebut(new \DateTime('01/14/2023'));
        $sortie3->setDuree(90);
        $sortie3->setDateLimiteInscription(new \DateTime('01/10/2023'));
        $sortie3->setNbInscriptionsMax(25);
        $sortie3->setInfosSortie("Une soirée au cinéma");
        $sortie3->setOrganisateur($user);
        $sortie3->addParticipant($user);
        $sortie3->addParticipant($user1);
        $sortie3->addParticipant($user2);
        $sortie3->addParticipant($user3);
        $sortie3->setEtat($etat);
        $sortie3->setLieu($lieu1);
        $sortie3->setArchive(false);
        $manager->persist($sortie3);

        $sortie4 = new Sortie();
        $sortie4->setNom("Balade");
        $sortie4->setDateHeureDebut(new \DateTime('09/14/2022'));

        $sortie4->setDuree(120);
        $sortie4->setDateLimiteInscription(new \DateTime('09/11/2022'));
        $sortie4->setNbInscriptionsMax(25);
        $sortie4->setInfosSortie("Balade à Saint Malo");
        $sortie4->setOrganisateur($user1);
        $sortie4->addParticipant($user);
        $sortie4->addParticipant($user1);
        $sortie4->addParticipant($user2);
        $sortie4->setEtat($etat4);
        $sortie4->setLieu($lieu2);
        $sortie4->setArchive(false);
        $manager->persist($sortie4);

        $sortie5 = new Sortie();
        $sortie5->setNom("Match de basket");
        $sortie5->setDateHeureDebut(new \DateTime('09/03/2022'));
        $sortie5->setDuree(120);
        $sortie5->setDateLimiteInscription(new \DateTime('09/01/2022'));
        $sortie5->setNbInscriptionsMax(25);
        $sortie5->setInfosSortie("Balade à Saint Malo");
        $sortie5->setOrganisateur($user2);
        $sortie5->addParticipant($user2);
        $sortie5->addParticipant($user);
        $sortie5->addParticipant($user1);
        $sortie5->setEtat($etat5);
        $sortie5->setLieu($lieu3);
        $sortie5->setArchive(false);
        $manager->persist($sortie5);

        $sortie6 = new Sortie();
        $sortie6->setNom("Match de basket");
        $sortie6->setDateHeureDebut(new \DateTime('01/09/2022'));
        $sortie6->setDuree("120");
        $sortie6->setDateLimiteInscription(new \DateTime('09/01/2023'));
        $sortie6->setNbInscriptionsMax("25");
        $sortie6->setInfosSortie("Balade à Saint Malo");
        $sortie6->setOrganisateur($user2);
        $sortie6->addParticipant($user2);
        $sortie6->addParticipant($user);
        $sortie6->addParticipant($user1);
        $sortie6->setEtat($etat4);
        $sortie6->setLieu($lieu2);
        $sortie6->setArchive(false);
        $manager->persist($sortie6);

        $manager->flush();
    }
}
