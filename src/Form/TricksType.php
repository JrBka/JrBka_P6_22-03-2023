<?php

namespace App\Form;

use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints as Assert;

class TricksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Nom *',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\Length(['max'=>100]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('description',TextareaType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Description * ',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\NotBlank()
                ]
            ])
            ->add('tricksGroup',TextType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Groupe *',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\Length(['max'=>50]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('images', FileType::class, [
                'multiple' => true,
                'attr'=>[
                    'class'=> 'form-control mb-4',
                    'multiple'=>true
                ],
                'label'=>"Image aux formats ( '.png'  .'jpeg'  '.webp' )",
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new All([

                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Format d\'image invalide',
                    ])
                    ])
                ]
            ])
            ->add('video',TextareaType::class,[
                'required'=>false,
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Vidéo ( balise embed )',
                'label_attr'=>[
                    'class'=>'form-label'
                ]
            ])
            ->add('submit',SubmitType::class,[
                'attr'=>[
                    'class'=>'btn btn-outline-primary px-5'
                ],
                'label' => 'Ajouter'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
