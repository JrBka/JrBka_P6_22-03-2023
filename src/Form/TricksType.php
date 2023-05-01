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
                'required' => true,
            ])
            ->add('description',TextareaType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4',
                    'style'=> 'height:250px'
                ],
                'label'=>'Description * ',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'required' => true,
            ])
            ->add('tricksGroup',TextType::class,[
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Groupe *',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'required' => true,
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
                        'maxSizeMessage'=> 'La taille d\'image maximale autorisé est de 1024KB',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/webp'
                        ],
                        'mimeTypesMessage'=> 'Les extensions autorisé sont ( .png , .jpeg , .webp )',
                    ])
                    ])
                ]
            ])
            ->add('videos',TextareaType::class,[
                'required'=>false,
                'mapped' => false,
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>'Vidéo ( balise embed )',
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'constraints'=>[
                    new Assert\Regex(['pattern'=>'/src=\"[^\"]*\"/',
                        'match'=>true,
                        'message' => 'Vous devez renseigner une balise embed valide !']),
                ],
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
            'data_class' => Trick::class,
        ]);
    }
}
