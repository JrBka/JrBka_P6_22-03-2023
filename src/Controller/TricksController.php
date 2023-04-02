<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TricksType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TricksController extends AbstractController
{

    #[Route('/tricks', name: 'app_tricks', methods: ['GET'])]
    public function getTricks(TrickRepository $trickRepository): Response{

        $tricks = $trickRepository->findAll();

        return $this->render('home/index.html.twig',[
            'tricks'=>$tricks
        ]);
    }

    #[Route('/tricks/create', name: 'app_tricks_createtrick', methods: ['GET','POST'])]
    public function createTrick(Request $request, EntityManagerInterface $manager): Response{

        $trick = new Trick();

        $form = $this->createForm(TricksType::class,$trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $trick = $form->getData();

            $picture = $form->get('images')->getData();

            $video = $form->get('video')->getData();

            if ($picture){

                $newFilesName = [];

                foreach ($picture as $image){

                        $newFilename = time() . uniqid().'.'.$image->guessExtension();
                        $newFilesName [] = $newFilename;

                    try {
                        $image->move(
                            $this->getParameter('images_directory'),
                            $newFilename

                        );
                    } catch (FileException $e) {
                        echo $e->getMessage();
                    }
                }


                $trick->setPicture($newFilesName);

            }

            if ($video){

                $src = explode("src=",$video);
                $src = explode("\"",$src[1]);

                $trick->setVideo($src[1]);

            }

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash('success','Votre figure a bien été ajouté');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('tricks/createTrick.html.twig',[
            'form'=>$form->createView()
        ]);

    }

}
