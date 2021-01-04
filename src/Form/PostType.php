<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre',TextType::class, [
                "attr" => [
                    "class" => "form-control",
                    'placeholder' => "Please enter a title"
                ]
            ])
            ->add('content',TextareaType::class, [
                "attr" => [
                    "class" => "form-control",
                    'placeholder' => "Please write your content's post here.."
                ]
            ])
            ->add('image', TextType::class,[
                "attr" => [
                    "class" => "form-control",
                    'placeholder' => "Please past your image link here",
                    'pattern'=>"https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,4}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)(.jpg|.png|.jpeg|.gif)"
                ],
                "label"=>"Image (.png/jpg/jpeg/gif)"

            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
