<?php

namespace App\Form;

use App\Entity\Homepage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType; //pour image

class HomepageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('video', FileType::class, [
                'label' => 'Video (mp4)',
                'required' => false,
                'mapped'=>false, 
            ])  
            ->add('poster', FileType::class, [
                'label' => 'Poster (jpeg)',
                'required' => false,
                'mapped'=>false, 
            ])  
            ->add('bgNews', FileType::class, [
                'label' => 'Arrière-plan de la section news (jpeg)',
                'required' => false,
                'mapped'=>false, 
            ])  
            ->add('bgCommunity', FileType::class, [
                'label' => 'Arrière-plan de la section communauté (jpeg)',
                'required' => false,
                'mapped'=>false, 
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo en header pour les petits écrans (png 640px/160px)',
                'required' => false,
                'mapped'=>false,
            ])            
            ->add('promoCommunity')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Homepage::class,
        ]);
    }
}
