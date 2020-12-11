<?php


namespace App\Form;


use http\Client\Curl\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\Security;

class NewArticle extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('movie_title', TextType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control')
            ))
            ->add('summary', TextareaType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control')
            ))
            ->add('message_body', TextareaType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control')
            ))
            ->add('rating', NumberType::class, array('attr' => array('min' => '1',
                'max' => '10', 'class' => 'form-control')))

            ->add('director', TextType::class, array('attr' => array('class' => 'form-control')))

            ->add('actors', TextType::class, array('attr' => array('class' => 'form-control')))
            ->add('duration', null, array('widget' => 'single_text'))

            ->add('picture', TextType::class, array('attr' => array('class' => 'form-control')))

            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ;
//        $testt = cast
//
//        $var = (User::class) $options['data']['user'];

//        dd();
    }


}