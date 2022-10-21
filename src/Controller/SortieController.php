<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\AnnulerSortieType;
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
                if ($form->get('enregistrer')->isClicked()) {
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "En Création"]);
                } elseif ($form->get('publier')->isClicked()){
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
                }
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();
            } catch (\Exception $e) {
                echo($e->getMessage());
            }
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/ajoutSortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'editer', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($sortie->getDateHeureDebut() <= $sortie->getDateLimiteInscription()) {
                    throw new \Exception('La date limite d\'inscription ne peut être supérieure à celle de la sortie');
                }
                if ($form->get('publier')->isClicked()){
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
                    $sortie->setEtat($etat);
                }
                $entityManager->persist($sortie);
                $entityManager->flush();
            } catch (\Exception $e) {
                echo($e->getMessage());
            }
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/editerSortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }

    #[Route('/publier/{id}', name: 'publier', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function publier(Sortie $sortie, EntityManagerInterface $entityManager): Response {
        $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
        $sortie->setEtat($etat);
        $entityManager->persist($sortie);
        $entityManager->flush();

        // Redirection vers la home page une fois la mise à jour effectuée
        return $this->redirectToRoute('app_home');
    }

    #[Route('/delete/{id}', name: 'supprimer_sortie', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function supprimerSortie(Sortie $sortie, EntityManagerInterface $entityManager): Response {
        try {
            $entityManager->remove($sortie);
            $entityManager->flush();
        } catch (\Exception $e) {
            dd($e);
        }


        return $this->redirectToRoute('app_home');
    }

    /**
     * @throws \Exception
     */
    #[Route('/cancel/{id}', name: 'annuler_sortie', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function annulerSortie(Sortie $sortie, EntityManagerInterface $entityManager, Request $request): Response {
        $form = $this->createForm(AnnulerSortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($sortie->getEtat()->getNom() != "Ouverte" && $sortie->getEtat()->getNom() != "Fermée") {
                throw new \Exception('Vous ne pouvez pas annuler cette sortie');
            }
            $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Annulée']));
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/annulerSortie.html.twig', [
            'sortie' => $sortie,
            'annulerForm' => $form->createView()
        ]);
    }
}
