<?php

namespace App\Form;

use App\Entity\Traitement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TraitementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRenouvellement', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('dose', IntegerType::class, [
                'required' => false,
                'attr' => ['id' => 'dose'], // Ajout de l'ID pour le champ 'dose'
            ])
            ->add('frequence', ChoiceType::class, [
                'choices' => [
                    'Tous les jours' => 'jour',
                    'Toutes les semaines' => 'semaine',
                ],
                'required' => true,
                'expanded' => true, // Affichage en boutons radio
                'attr' => ['id' => 'frequence'], // Ajout de l'ID pour le champ 'frequence'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Traitement::class,
        ]);
    }
}
