<?php

namespace App\Form;

use App\Entity\MasterBackground;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MasterBackgroundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('backgrounds', CollectionType::class, [
            'entry_type' => BackgroundType::class,
            'entry_options' => ['label' => false],
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MasterBackground::class,
        ]);
    }
}
