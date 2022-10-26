<?php

namespace App\Controller;

use App\Entity\Lieu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lieu', name: 'app_')]
class LieuController extends AbstractController
{
    #[Route('/', name: 'lieu')]
    public function index(EntityManagerInterface $em): Response
    {
        $lieu = $em->getRepository(Lieu::class)->findAll();
        return $this->json($lieu, 200, [], ['groups' => 'place_group']);
    }

    #[Route('/{id}', name: 'get_place', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getPlace(EntityManagerInterface $em, int $id): Response
    {
        $lieu = $em->getRepository(Lieu::class)->find($id);
        return $this->json($lieu, 200, [], ['groups' => 'lieu_group']);
    }
}
