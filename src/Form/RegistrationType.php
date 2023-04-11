<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username',TextType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4',
                    'maxlength' => '180'
                ],
                'label'=>'Nom d\'utilisateur',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\Length(['max'=>180]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('plainPassword', RepeatedType::class,[
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'form-control mb-4'
                    ],
                    'label'=>'Mot de passe',
                    'label_attr'=>[
                        'class'=>'form-label'
                    ]
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'form-control mb-4'
                    ],
                    'label'=>'Confirmation du mot de passe',
                    'label_attr'=>[
                        'class'=>'form-label'
                    ]
                ],
                'invalid_message' => 'les mots de passe ne correspondent pas',
                'constraints' => new Assert\Regex(['pattern' => '/^(?=.*\d)(?=.*[A-Z])(?=.*[@#$%])(?!.*(.)\1{2}).*[a-z]/m',
                    'match' => true,
                    'message' => 'Votre mot de passe doit comporter au moins huit caractères, dont des lettres majuscules et minuscules, un chiffre et un symbole',
                ])
            ])
            ->add('profilePhoto', FileType::class, [
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>"Photo de profile aux formats ( '.png'  .'jpeg'  '.webp' )",
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/webp'
                        ]
                    ])
                ]
            ])
            ->add('email',EmailType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4',
                    'maxlength' => '255'
                ],
                'label'=>'Email',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\Email(),
                    new Assert\Length(['max'=>255]),
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
            'data_class' => User::class,
        ]);
    }
}
