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
            'sortie' => $sortie,
            'controller_name' => 'MainController',
            'user' => $this->getUser()
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
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Créée"]);
                } elseif ($form->get('publier')->isClicked()){
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouverte"]);
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
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouverte"]);
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

    #[Route('/sinscrire/{id}', name: 'sinscrire', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function sinscrire(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {

        // L'utilisateur connecté est set en tant que participant
        $sortie->addParticipant($this->getUser());

        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
            'controller_name' => 'MainController',
            'user' => $this->getUser()
        ]);
    }

    #[Route('/desister/{id}', name: 'desister', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function desister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {

        // L'utilisateur connecté est désister en tant que participant
        $sortie->removeParticipant($this->getUser());

        $entityManager->persist($sortie);
        $entityManager->flush();

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie,
            'controller_name' => 'MainController',
            'user' => $this->getUser()
        ]);
    }

    #[Route('/publier/{id}', name: 'publier', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function publier(Sortie $sortie, EntityManagerInterface $entityManager): Response {
        $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouverte"]);
        $sortie->setEtat($etat);
        $entityManager->persist($sortie);
        $entityManager->flush();

        // Redirection vers la home page une fois la mise à jour effectuée
        return $this->redirectToRoute('app_home');
    }

}
