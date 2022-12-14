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
                    throw new \Exception('La date limite d\'inscription ne peut ??tre sup??rieure ?? celle de la sortie');
                }
                // L'utilisateur connect?? est set en tant qu'organisateur
                $sortie->setOrganisateur($this->getUser());
                // On l'inscrit d'office ?? la sortie
                $sortie->addParticipant($this->getUser());
                if ($form->get('enregistrer')->isClicked()) {
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "En Cr??ation"]);
                } elseif ($form->get('publier')->isClicked()){
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
                }
                $sortie->setEtat($etat);
                $sortie->setArchive(false);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien ??t?? enregistr??e');
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
                    throw new \Exception('La date limite d\'inscription ne peut ??tre sup??rieure ?? celle de la sortie');
                }
                if ($sortie->getEtat()->getNom() !== "En cr??ation") {
                    throw new \Exception('Cette sortie ne peut plus ??tre modifi??e');
                }
                if ($form->get('publier')->isClicked()){
                    $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
                    $sortie->setEtat($etat);
                }
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success' ,'Les modifications ont bien ??t?? effectu??es');
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
                throw new \Exception('Vous ne pouvez pas vous inscrire ?? cette sortie');
            }
            if ($sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax()) {
                throw new \Exception('La sortie ne comporte plus de places disponibles');
            }
            if ($sortie->getParticipants()->contains($this->getUser())) {
                throw new \Exception('Vous ??tes d??j?? inscrit ?? cette sortie');
            }
            // L'utilisateur connect?? est set en tant que participant
            $sortie->addParticipant($this->getUser());
            if ($sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax()) {
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Ferm??']));
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Votre inscription a bien ??t?? prise en compte');
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
                throw new \Exception('Vous n\'??tes pas inscrit ?? cette sortie');
            }
            // L'utilisateur connect?? est d??sister en tant que participant
            $sortie->removeParticipant($this->getUser());
            if ($sortie->getEtat()->getNom() == "Ferm??" && $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax() && $sortie->getDateLimiteInscription() > new \DateTime()) {
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Ouvert']));
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez bien ??t?? d??sinscrit');
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
            if ($sortie->getEtat()->getNom() !== 'En cr??ation') {
                throw new \Exception('Vous ne pouvez pas publier une annonce d??j?? publi??e');
            }
            $etat = $entityManager->getRepository(Etat::class)->findOneBy(["nom" => "Ouvert"]);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a bien ??t?? publi??e');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }


        // Redirection vers la home page une fois la mise ?? jour effectu??e
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
            if ($sortie->getEtat()->getNom() !== 'En cr??ation') {
                throw new \Exception('Vous ne pouvez pas supprimer une sortie d??j?? publi??e');
            }
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La suppression de la sortie a ??t?? effectu??e');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_home');
    }

    /**
     * @throws \Exception
     * Passe ?? l'??tat Annul??e la sortie correspondant ?? l'id pass??
     */
    #[Route('/cancel/{id}', name: 'annuler_sortie', requirements: ['id' => '\d+'])]
    #[ParamConverter('sortie', class: 'App\Entity\Sortie')]
    #[IsGranted('ROLE_USER')]
    public function annulerSortie(Sortie $sortie = null, EntityManagerInterface $entityManager, Request $request): Response {
        try {
            // Renvoie une exception si aucune sortie ne correspond ?? l'id
            if ($sortie == null) {
                throw new NotFoundHttpException('Cette sortie n\'existe pas');
            }
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
        // Cr??ation du formulaire d'annulation
        $form = $this->createForm(AnnulerSortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Renvoie une exception si l'utilisateur connect?? n'est pas l'organisateur de la sortie
                if ($this->getUser() !== $sortie->getOrganisateur()) {
                    throw new \Exception('Vous ne pouvez pas annuler une sortie que vous n\'organisez pas');
                }
                // Renvoie une exception si la sortie n'est pas dans l'??tat Ouvert ou Ferm?? et que la date de d??but est d??pass??e
                if ($sortie->getEtat()->getNom() != "Ouvert" && $sortie->getEtat()->getNom() != "Ferm??" && $sortie->getDateHeureDebut() <= new \DateTime()) {
                    throw new \Exception('Vous ne pouvez pas annuler cette sortie');
                }
                // Modification de l'??tat en Annul??e
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneBy(['nom' => 'Annul??e']));
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien ??t?? annul??e');
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
