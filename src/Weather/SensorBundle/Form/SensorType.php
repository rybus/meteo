<?php

namespace Weather\SensorBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SensorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['label' => 'Nom'])
            ->add('rom', 'text', [
                'label' => 'Adresse ROM',
                'disabled' => true
            ])
            ->add('type', 'entity', [
                'class' => 'WeatherSensorBundle:SensorType',
                'label' => 'Type de capteur',
                'disabled' => true
            ])
            ->add('save', 'submit', ['label' => 'Enregistrer'])
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Weather\SensorBundle\Entity\Sensor'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'weather_sensorbundle_sensor';
    }
}
