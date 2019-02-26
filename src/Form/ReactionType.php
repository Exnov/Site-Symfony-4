<?php

namespace App\Form;

use App\Entity\Reaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use FOS\CKEditorBundle\Form\Type\CKEditorType;

class ReactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content',CKEditorType::class,[
                'config'=>[
                    'toolbar' => 'full',
                    'language' => 'fr',
                    'removeButtons' => 'Image,Flash,Print,Templates,Form,Radio,TextField,Textarea,Button,ImageButton,Checkbox,HiddenField,CreateDiv,Iframe,Scayt',
                ],
                'label'=>'Message'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reaction::class,
        ]);
    }
}
