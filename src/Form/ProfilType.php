<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\BlankValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('pseudo', TextType::class, [
              //  'disabled' => true,
            //])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom :'
            ])
            ->add('telephone', TelType::class , [
                'label' => 'Téléphone :',
                'constraints' => [
                    new Length([
                        'min' => 10,
                    ]),
                ],
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email :'
            ])
            ->add('password', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match',
                'first_options' => ['label' => 'Mot de passe :'],
                'second_options' => ['label' => 'Répéter le mot de passe :'],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'required' => false,
            ])
            ->add('site',EntityType::class, [
                'class' => Site::class,
                'label' => 'Site :',
                'choice_label' => 'nom',
                'query_builder' => function(SiteRepository $siteRepository) {
                    return $siteRepository-> createQueryBuilder('s')->addOrderBy('s.nom', 'ASC');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
