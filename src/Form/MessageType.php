<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\User;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content',CKEditorType::class,[
                'config'=>[
                    'toolbar' => 'full', 
                    'language' => 'fr',
                    'removeButtons' => 'Image,Flash,Print,Templates,Form,Radio,TextField,Textarea,Button,ImageButton,Checkbox,HiddenField,CreateDiv,Iframe,Scayt',                        
                ],
                'label'=>'Message',
            ])
            ->add('target', EntityType::class,[
                'class' => User::class,
                'multiple' => true, 
                'choice_label' => 'username',
                'label'=>'Destinataire',

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
