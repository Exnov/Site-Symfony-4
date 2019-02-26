<?php

namespace App\Form;

use App\Entity\CriticSignalement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use FOS\CKEditorBundle\Form\Type\CKEditorType;

class CriticSignalementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content',CKEditorType::class,[
                'config'=>[
                    'toolbar' => 'basic', 
                    'language' => 'fr',                   
                ],
                'label'=>'Message'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CriticSignalement::class,
        ]);
    }
}
