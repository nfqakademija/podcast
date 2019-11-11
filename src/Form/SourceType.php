<?php

namespace App\Form;

use App\Entity\Source;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('url')
            ->add('createdAt')
            ->add('mainElementSelector')
            ->add('imageSelector')
            ->add('titleSelector')
            ->add('descriptionSelector')
            ->add('audioSelector')
            ->add('audioSourceAttribute')
            ->add('publicationDateSelector')
            ->add('imageSourceAttribute')
            ->add('sourceType', ChoiceType::class, [
                'choices' => [
                    'Video',
                    'Audio'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Source::class,
        ]);
    }
}
