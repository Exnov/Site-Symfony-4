<?php

namespace App\Form;

use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType; //pour image
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; //statut publication
use Symfony\Component\Form\Extension\Core\Type\DateType; //date de publication

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('image', FileType::class, [
                'label' => 'Image (jpeg)',
                'required' => false,
                'mapped'=>false, 
            ])   
            ->add('authorPseudo')
            ->add('description')  
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'Brouillon' => 0,
                    'Publié' => 1,
                ],     
            ]) 
            ->add('publishedAt', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])    
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
