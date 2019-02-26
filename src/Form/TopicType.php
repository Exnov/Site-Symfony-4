<?php

namespace App\Form;

use App\Entity\Topic;
use App\Entity\Forum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TopicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('category',EntityType::class,[
                'class'=>Forum::class,
                'choice_label' => 'category',
                'label'=>'Catégorie'
            ])
        ;

        //on intégre le form de Reaction au form de Topic; cf https://symfony.com/doc/current/form/form_collections.html
        $builder->add('reactions', CollectionType::class, array(
            'entry_type' => ReactionType::class,
            'entry_options' => array('label' => false),
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Topic::class,
        ]);
    }
}
