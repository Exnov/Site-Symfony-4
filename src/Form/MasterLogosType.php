<?php

namespace App\Form;

use App\Entity\MasterLogos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MasterLogosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('logos', CollectionType::class, [
            'entry_type' => LogosType::class,
            'entry_options' => ['label' => false],
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MasterLogos::class,
        ]);
    }
}
