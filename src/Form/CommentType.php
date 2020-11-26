<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("author",TextType::class,[
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add("content",TextareaType::class,[
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add("submit",SubmitType::class,[
                "attr" => [
                    "class" => "btn btn-primary"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
