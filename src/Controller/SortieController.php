<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'app_')]
class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'details', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function details(Sortie $sortie, SortieRepository $sortieRepository): Response
    {
        //
        //$sorties = $sortieRepository->findAll();

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie
        ]);
    }
}
