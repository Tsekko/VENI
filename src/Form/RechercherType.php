<?php

namespace App\Form;

use App\Entity\Rechercher;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', TextType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' =>false
                ])
            ->add('debut', DateType::class, [
                'label' => 'Entre : ',
                'widget' => 'single_text',
                'required' =>false,
            ])
            ->add('fin', DateType::class, [
                'label' => 'et : ',
                'widget' => 'single_text',
                'required' =>false,
           ])
            ->add('checkbox_organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' =>false,
            ])
            ->add('checkbox_inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' =>false,
            ])
            ->add('checkbox_non_inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' =>false,
            ])
            ->add('checkbox_passes', CheckboxType::class, [
                'label' => 'Sorties passés',
                'required' =>false,
            ])
            ->add('site', EntityType::class, [
                'choice_label' => "nom",
                'label' => 'Site : ',
                'class' => Site::class,
                'required' => false,
            ])
            ->add('rechercher', SubmitType::class, ['label' => 'Rechercher']);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rechercher::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}
