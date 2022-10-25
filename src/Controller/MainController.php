<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SortieRepository $sortieRepository,  SiteRepository $siteRepository, EntityManagerInterface $entityManager): Response
    {
        //liste déroulante des sites
        $sites = $siteRepository->findAll();
        
        //liste des sorties actives
        $sorties = $sortieRepository->findBy(["archive" => false], ["dateHeureDebut" => "ASC"]);

        // On crée une date à partir de laquelle on pourra archiver les sorties
        $now  = new DateTime();
        $interval = new DateInterval('P1M');
        $dateDepassee = $now->sub($interval);

        // on parcourt la liste des sorties à chaque consultation de la page home
        // si la date de début de la sortie est inférieure à la date dépassée (date du jour - 1 mois) alors on archive la sortie
        foreach ($sorties as $sortie) {
            if ($sortie->getDateHeureDebut() < $dateDepassee) {
                $sortie->setArchive(true);
                $entityManager->persist($sortie);
            }
            if ($sortie->getDateLimiteInscription() < $now) {
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Fermé"]);
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
            }
        }
        // On lance la modification dans la base de données pour l'archivage des sorties
        $entityManager->flush();


        return $this->render('main/home.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
            'controller_name' => 'MainController',
        ]);
    }
}
