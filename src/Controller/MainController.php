<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SortieRepository $sortieRepository, SiteRepository $siteRepository): Response
    {
        //liste des sorties
        $sorties = $sortieRepository->findAll();
        //liste déroulante des sites
        $sites = $siteRepository->findAll();

        return $this->render('main/home.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
            'controller_name' => 'MainController',
        ]);
    }
}
