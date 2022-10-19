<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @throws \Exception
     */
    #[Route('/add', name: 'ajout_sortie')]
    #[IsGranted('ROLE_USER')]
    public function ajout(Request $request, EntityManagerInterface $entityManager): Response
    {

        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($sortie->getDateHeureDebut() <= $sortie->getDateLimiteInscription()) {
                    throw new \Exception('La date limite d\'inscription ne peut être supérieure à celle de la sortie');
                }
                // L'utilisateur connecté est set en tant qu'organisateur
                $sortie->setOrganisateur($this->getUser());
                // On l'inscrit d'office à la sortie
                $sortie->addParticipant($this->getUser());
                $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouverte"]);
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();
            } catch (\Exception $e) {
                echo($e->getMessage());
            }

        }

        return $this->render('sortie/ajoutSortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }
}
