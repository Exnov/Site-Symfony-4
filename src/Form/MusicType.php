<?php

namespace App\Form;

use App\Entity\Music;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType; //pour image
use Symfony\Component\Form\Extension\Core\Type\ChoiceType; //statut publication
use Symfony\Component\Form\Extension\Core\Type\DateType; //date de publication

class MusicType extends AbstractType
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
            ->add('year',null,[
                'label' => 'Année de sortie',
            ])       
            ->add('playlist',null,[
                'label' => 'playlist YouTube (facultatif)',
                'attr' => [
                    'placeholder' => 'ex: https://www.youtube.com/playlist?list=OLAK5uy_le0n6mbBWFvpUnUnUcA3RuGY7yrghhA_o'],
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
            'data_class' => Music::class,
        ]);
    }
}
