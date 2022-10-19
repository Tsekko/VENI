<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'app_')]
class SortieController extends AbstractController
{
    #[Route('/liste', name: 'liste')]
    public function liste(SortieRepository $sortieRepository): Response
    {

        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties
        ]);
    }
}
