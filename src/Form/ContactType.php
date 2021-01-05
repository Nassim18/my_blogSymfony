<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class, [
                "attr" => [
                    "class" => "form-control",
                    'placeholder' => "Please enter your name"
                ]
            ])
            ->add('email',EmailType::class, [
                "attr" => [
                    "class" => "form-control",
                    'placeholder' => "Please enter your email"
                ]
            ])
            ->add('description',TextareaType::class, [
                "attr" => [
                    "class" => "form-control",
                    'placeholder' => "Please write your request here.."
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
