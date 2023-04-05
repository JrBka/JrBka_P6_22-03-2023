<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content',TextareaType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Message',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\NotBlank()
                ]
            ])
            ->add('submit',SubmitType::class,[
                'attr'=>[
                    'class'=>'btn btn-outline-primary px-5'
                ],
                'label' => 'Envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
