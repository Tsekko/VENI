<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ParticipantType;
use App\Form\SupprimerParticipantType;
use App\Form\UploadCSVType;
use App\Service\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/administration', name: 'app_administration_')]
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
}
