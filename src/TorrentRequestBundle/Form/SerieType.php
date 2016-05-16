<?php

namespace TorrentRequestBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label' => 'Titre : '
            ))
            ->add('season', ChoiceType::class, array(
                'choices' => $this->getIntegerMap(20),
                'label' => 'Saison : ',
            ))
            ->add('episode', ChoiceType::class, array(
                'choices' => $this->getIntegerMap(60),
                'label' => 'Episode : ',
            ))
        ;
    }

    private function getIntegerMap($max)
    {
        $ar_num = array();
        
        for($i=1;$i<=$max;$i++)
        {
            $ar_num[$i] = $i;
        }
        
        return $ar_num;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TorrentRequestBundle\Entity\Serie'
        ));
    }
}