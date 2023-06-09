<?php

namespace App\Form;

use App\Entity\Trick;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('newPicture', FileType::class, [
                'attr'=>[
                    'class'=> 'form-control mb-4'
                ],
                'label'=>"Image aux formats ( '.png'  .'jpeg'  '.webp' )",
                'label_attr'=>[
                    'class'=>'form-label'
                ],
                'mapped' => false,
                'required' => true,
                'constraints' => [
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
            'data_class' => Trick::class,
        ]);
    }
}
