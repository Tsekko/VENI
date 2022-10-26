<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo :'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe :'
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom :'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :'
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email :'
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone :',
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 10,
                    ])
                ]
            ])
            ->add('administrateur', CheckboxType::class, [
                'label' => 'Administrateur :',
                'required' => false,
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'label' => 'Site :',
                'choice_label' => 'nom',
                'query_builder' => function(SiteRepository $siteRepository) {
                    return $siteRepository-> createQueryBuilder('s')->addOrderBy('s.nom', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
