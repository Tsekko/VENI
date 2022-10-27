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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\throwException;

#[Route('/sortie', name: 'app_')]
class SortieController extends AbstractController
{
    #[Route('/{id}', name: 'details', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    public function details(Sortie $sortie = null): Response
    {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('La sortie n\'existe pas');
            }
            return $this->render('sortie/details.html.twig', [
                'sortie' => $sortie,
                'controller_name' => 'MainController',
                'user' => $this->getUser()
            ]);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
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
                $sortie->setArchive(false);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien été enregistrée');
            } catch (\Exception $e) {
                echo($e->getMessage());
                $this->addFlash('error', $e->getMessage());
                return $this->render('sortie/ajoutSortie.html.twig', [
                    'sortieForm' => $form->createView(),
                ]);
            }
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/ajoutSortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'editer', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Sortie $sortie = null, EntityManagerInterface $entityManager): Response {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($sortie->getDateHeureDebut() <= $sortie->getDateLimiteInscription()) {
                    throw new \Exception('La date limite d\'inscription ne peut être supérieure à celle de la sortie');
                }
                if ($sortie->getEtat()->getNom() !== "En création") {
                    throw new \Exception('Cette sortie ne peut plus être modifiée');
                }
                if ($form->get('publier')->isClicked()){
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
                    $sortie->setEtat($etat);
                }
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success' ,'Les modifications ont bien été effectuées');
            } catch (NotFoundHttpException $error) {
                $this->addFlash('error', $error->getMessage());
            } catch (\Exception $e) {
                echo($e->getMessage());
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/editerSortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }

    #[Route('/sinscrire/{id}', name: 'sinscrire', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function sinscrire(Sortie $sortie = null, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
            if ($sortie->getEtat()->getNom() !== "Ouvert") {
                throw new \Exception('Vous ne pouvez pas vous inscrire à cette sortie');
            }
            if ($sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax()) {
                throw new \Exception('La sortie ne comporte plus de places disponibles');
            }
            if ($sortie->getParticipants()->contains($this->getUser())) {
                throw new \Exception('Vous êtes déjà inscrit à cette sortie');
            }
            // L'utilisateur connecté est set en tant que participant
            $sortie->addParticipant($this->getUser());
            if ($sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax()) {
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Fermé']));
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Votre inscription a bien été prise en compte');
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }


        return $this->redirectToRoute('app_details', [
            'sortie' => $sortie,
            'id' => $sortie->getId(),
            'controller_name' => 'MainController',
            'user' => $this->getUser()
        ]);
    }

    #[Route('/desister/{id}', name: 'desister', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function desister(Sortie $sortie = null, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
            if (!$sortie->getParticipants()->contains($this->getUser())) {
                throw new \Exception('Vous n\'êtes pas inscrit à cette sortie');
            }
            // L'utilisateur connecté est désister en tant que participant
            $sortie->removeParticipant($this->getUser());
            if ($sortie->getEtat()->getNom() == "Fermé" && $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax() && $sortie->getDateLimiteInscription() > new \DateTime()) {
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Ouvert']));
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez bien été désinscrit');
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_details', [
            'sortie' => $sortie,
            'id' => $sortie->getId(),
            'controller_name' => 'MainController',
            'user' => $this->getUser()
        ]);
    }

    #[Route('/publier/{id}', name: 'publier', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function publier(Sortie $sortie = null, EntityManagerInterface $entityManager): Response {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
            if ($this->getUser() !== $sortie->getOrganisateur()) {
                throw new \Exception('Vous ne pouvez pas publier une sortie que vous n\'organisez pas');
            }
            if ($sortie->getEtat()->getNom() !== 'En création') {
                throw new \Exception('Vous ne pouvez pas publier une annonce déjà publiée');
            }
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a bien été publiée');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }


        // Redirection vers la home page une fois la mise à jour effectuée
        return $this->redirectToRoute('app_home');
    }


    #[Route('/delete/{id}', name: 'supprimer_sortie', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function supprimerSortie(Sortie $sortie = null, EntityManagerInterface $entityManager): Response {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
            if ($this->getUser() !== $sortie->getOrganisateur()) {
                throw new \Exception('Vous ne pouvez pas supprimer une sortie que vous n\'organisez pas');
            }
            if ($sortie->getEtat()->getNom() !== 'En création') {
                throw new \Exception('Vous ne pouvez pas supprimer une sortie déjà publiée');
            }
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La suppression de la sortie a été effectuée');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @throws \Exception
     */
    #[Route('/cancel/{id}', name: 'annuler_sortie', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function annulerSortie(Sortie $sortie = null, EntityManagerInterface $entityManager, Request $request): Response {
        try {
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
        $form = $this->createForm(AnnulerSortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($this->getUser() !== $sortie->getOrganisateur()) {
                    throw new \Exception('Vous ne pouvez pas annuler une sortie que vous n\'organisez pas');
                }
                if ($sortie->getEtat()->getNom() != "Ouvert" && $sortie->getEtat()->getNom() != "Fermé" && $sortie->getDateHeureDebut() <= new \DateTime()) {
                    throw new \Exception('Vous ne pouvez pas annuler cette sortie');
                }
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Annulée']));
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien été annulée');
            } catch (\Exception $e) {
                $this->addFlash('error' ,$e->getMessage());
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/annulerSortie.html.twig', [
            'sortie' => $sortie,
            'annulerForm' => $form->createView()
        ]);
    }

}
