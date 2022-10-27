<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\ParticipantType;
use App\Form\RechercheVilleLieuType;
use App\Form\SupprimerParticipantType;
use App\Form\UploadCSVType;
use App\Form\VilleType;
use App\Service\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/administration', name: 'app_administration_')]
#[IsGranted('ROLE_ADMIN')]
class AdministrationController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }

    #[Route('/utilisateurs', name: 'utilisateurs')]
    public function utilisateurs(EntityManagerInterface $em, Request $request): Response {
        //$utilisateurs = $em->getRepository(Participant::class)->findAll();
        $form = $this->createForm(SupprimerParticipantType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $users = $form['utilisateurs']->getData();
            foreach ($users as $user) {
                $participant = $em->getRepository(Participant::class)->findOneBy(['id' => $user]);
                if ($form->get('desactiver')->isClicked()) {
                    $participant->setActif(0);
                    $em->persist($participant);
                } elseif ($form->get('activer')->isClicked()) {
                    $participant->setActif(1);
                    $em->persist($participant);
                } elseif ($form->get('supprimer')->isClicked()) {
                    $em->remove($participant);
                }
            }
            $em->flush();
            return $this->redirectToRoute('app_administration_utilisateurs');
        }

        return $this->render('administration/utilisateurs.html.twig', [
            'utilisateurs' => $form->createView(),
        ]);
    }

    #[Route('/utilisateurs/ajout', name: 'utilisateurs_ajout')]
    public function ajoutUtilisateur(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response {
        $user = new Participant();
        $form = $this->createForm(ParticipantType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }
            $user->setActif(true);
            if ($form->get('administrateur')->getData()) {
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_administration_utilisateurs');
        }

        return $this->render('administration/ajoutParticipant.html.twig', [
            'monProfilForm' => $form->createView(),
        ]);
    }

    #[Route('/utilisateurs/csv', name: 'csv')]
    public function utilisateursCSV(Request $request, UploadFile $uploadFile, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response {
        $form = $this->createForm(UploadCSVType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['csv']->getData();
            if ($file)
            {
                $file_name = $uploadFile->upload($file);
                if (null !== $file_name) // for example
                {
                    $directory = $uploadFile->getTargetDirectory();
                    $full_path = $directory.'/'.$file_name;
                    // Do what you want with the full path file...
                    // Why not read the content or parse it !!!
                    if(($handle = fopen($full_path, "r")) !== false) {
                        $i = 0;
                        while(($data = fgetcsv($handle, null, ";")) !== false) {
                            // On itére tant que l'on a une ligne et on crée un nouvel utilisateur
                            $i++;
                            $user = new Participant();
                            if($i == 1) {continue;}
                            $user->setPseudo($data[0]);
                            $user->setPassword(
                                $userPasswordHasher->hashPassword(
                                    $user,
                                    $data[1]
                                )
                            );
                            $user->setNom($data[2]);
                            $user->setPrenom($data[3]);
                            $user->setMail($data[4]);
                            if ($data[5] !== null) {
                                $data[5] = str_replace(" ", "", $data[5]);
                                $user->setTelephone($data[5]);
                            }
                            $user->setSite($em->getRepository(Site::class)->findOneBy(['nom' => $data[6]]));
                            $user->setAdministrateur($data[7]);
                            if ($data[7]) {
                                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
                            } else {
                                $user->setRoles(['ROLE_USER']);
                            }
                            $user->setActif(true);

                            $em->persist($user);
                        }
                        $em->flush();
                        $filesystem = new Filesystem();
                        // Supprime le fichier une fois le traitement effectué
                        $filesystem->remove($full_path);

                        return $this->redirectToRoute('app_administration_accueil');
                    }
                }
                else
                {
                    // Oups, an error occured !!!
                }
            }
        }

        return $this->render('administration/csv.html.twig', [
            'csvForm' => $form->createView(),
        ]);
    }

    #[Route('/lieux', name: 'lieux')]
    public function afficherLieux(EntityManagerInterface $em, Request $request): Response {
        $form = $this->createForm(RechercheVilleLieuType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->get('nom')->getData() ? $form->get('nom')->getData() : "";
            if ($search != "") {
                $lieux = $em->getRepository(Lieu::class)->rechercheNom($search);
            } else {
                $lieux = $em->getRepository(Lieu::class)->findAll();
            }
        } else {
            $lieux = $em->getRepository(Lieu::class)->findAll();
        }

        return $this->render('administration/lieux.html.twig', [
            'lieux' => $lieux,
            'rechercheLieuForm' => $form->createView()
        ]);
    }

    #[Route('/lieux/add', name: 'ajout_lieu')]
    public function ajouterLieu(Request $request, EntityManagerInterface $em): Response {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lieu);
            $em->flush();
            return $this->redirectToRoute('app_administration_lieux');
        }

        return $this->render('administration/ajoutLieu.html.twig', [
            'lieuForm' => $form->createView()
        ]);
    }

    #[Route('/lieux/edit/{id}', name: 'edit_lieu', requirements: ['id' => '\d+'])]
    #[ParamConverter('lieu', class: 'App\Entity\Lieu')]
    public function editerLieu(Request $request, EntityManagerInterface $em, Lieu $lieu = null): Response {
        try {
            if ($lieu == null) {
                throw new NotFoundHttpException('Cette ville n\'existe pas');
            }
            $form = $this->createForm(LieuType::class, $lieu);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($lieu);
                $em->flush();
                return $this->redirectToRoute('app_administration_lieux');
            }
        } catch (NotFoundHttpException $e) {
            $this->addErrorFlash($e->getMessage());
            return  $this->redirectToRoute('app_administration_lieux');
        }


        return $this->render('administration/ajoutLieu.html.twig', [
            'lieuForm' => $form->createView()
        ]);
    }

    #[Route('lieux/delete/{id}', name: 'delete_lieu', requirements: ['id' => '\d+'])]
    #[ParamConverter('lieu', class: 'App\Entity\Lieu')]
    public function supprimerLieu(Lieu $lieu = null, EntityManagerInterface $em): Response {
        try {
            if ($lieu == null) {
                throw new NotFoundHttpException('Cette ville n\'existe pas');
            }
            $em->getRepository(Lieu::class)->remove($lieu);
            $em->flush();
        } catch (NotFoundHttpException $e) {
            $this->addErrorFlash($e->getMessage());
        }

        return $this->redirectToRoute('app_administration_lieux');
    }

    #[Route('/villes', name: 'villes')]
    public function afficherVille(EntityManagerInterface $em, Request $request): Response {
        $form = $this->createForm(RechercheVilleLieuType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->get('nom')->getData() ? $form->get('nom')->getData() : "";
            if ($search != "") {
                $villes = $em->getRepository(Ville::class)->rechercheNom($search);
            } else {
                $villes = $em->getRepository(Ville::class)->findAll();
            }
        } else {
            $villes = $em->getRepository(Ville::class)->findAll();
        }

        return $this->render('administration/villes.html.twig', [
            'villes' => $villes,
            'rechercheLieuForm' => $form->createView()
        ]);
    }

    #[Route('/villes/add', name: 'ajout_ville')]
    public function ajouterVille(Request $request, EntityManagerInterface $em): Response {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ville);
            $em->flush();
            return $this->redirectToRoute('app_administration_villes');
        }

        return $this->render('administration/ajoutVille.html.twig', [
            'villeForm' => $form->createView()
        ]);
    }

    #[Route('/villes/edit/{id}', name: 'editer_ville', requirements: ['id' => '\d+'])]
    #[ParamConverter('ville', class: 'App\Entity\Ville')]
    public function editerVille(Request $request, EntityManagerInterface $em, Ville $ville = null): Response {
        try {
            if ($ville == null) {
                throw new NotFoundHttpException('Cette ville n\'existe pas');
            }
            $form = $this->createForm(VilleType::class, $ville);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($ville);
                $em->flush();
                return $this->redirectToRoute('app_administration_villes');
            }

            return $this->render('administration/ajoutVille.html.twig', [
                'villeForm' => $form->createView()
            ]);
        } catch (NotFoundHttpException $e) {
            $this->addErrorFlash($e->getMessage());
            return $this->redirectToRoute('app_administration_villes');
        }

    }

    #[Route('/villes/delete/{id}', name: 'supprimer_ville', requirements: ['id' => '\d+'])]
    #[ParamConverter('ville', class: 'App\Entity\Ville')]
    public function supprimerVille(EntityManagerInterface $em, Ville $ville = null): Response {
        try {
            if ($ville == null) {
                throw new NotFoundHttpException('Cette ville n\'existe pas');
            }
            $em->getRepository(Ville::class)->remove($ville);
            $em->flush();
        } catch (NotFoundHttpException $e) {
            $this->addErrorFlash($e->getMessage());
        }

        return $this->redirectToRoute('app_administration_villes');
    }

    private function addErrorFlash(string $message) {
        $this->addFlash('error', $message);
    }
}
