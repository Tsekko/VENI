<?php

namespace App\Controller;

use App\Form\ProfilType;
use App\Service\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant', name: 'app_')]
class ParticipantController extends AbstractController
{
    #[Route('/{id}', name: 'details_participant', requirements: ['id' => '\d+'])]
    #[ParamConverter('participant', class:'App\Entity\Participant')]
    public function index($participant = null): Response
    {
        try {
            if ($participant == null) {
                throw new NotFoundHttpException('Ce profil n\'existe pas');
            }

            return $this->render('participant/details.html.twig', [
                'participant' => $participant,
            ]);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/monprofil', name: 'monprofil')]
    #[IsGranted('ROLE_USER')]
    public function monProfil(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UploadFile $uploadFile): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if ($form->get('password')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $this->addFlash('success', 'Le mot de passe a bien été changé');

            }

            $file = $form['photoNom']->getData();
            if ($file)
            {
                $filename = $uploadFile->upload($file);
                $user->setPhotoNom($filename);

            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont bien été faites');
            return $this->redirectToRoute('app_monprofil');
        }

        elseif ($form->isSubmitted() && !$form->isValid()) {


            $this->addFlash('error', 'Le formulaire n\'est pas valide : Le mail est déjà utilisé');
            return $this->redirectToRoute('app_home');

        }

        return $this->render('participant/monProfil.html.twig', [
            'monProfilForm' => $form->createView(),
            'profil' => $user
        ]);

    }

}
