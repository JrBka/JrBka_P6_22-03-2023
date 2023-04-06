<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TricksType;
use App\Repository\CommentRepository;
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

    #[Route('/tricks/details/{slug}', name: 'app_tricks_getonetrick', methods: ['GET','POST'])]
    public function getOneTrick(TrickRepository $trickRepository,CommentRepository $commentRepository,
                                CommentsController $commentsController, Request $request, EntityManagerInterface $manager):Response{

        $name = $request->attributes->get('slug');

        $trick = $trickRepository->findOneBy(['name'=>$name]);

        $page = $request->query->getInt('page',1);

        $comments = $commentsController->getComments($commentRepository,$page,$name);

        $form = $commentsController->createComment($request,$manager,$trickRepository);

        return $this->render('tricks/showTrick.html.twig',[
            'trick'=>$trick,
            'comments'=>$comments,
            'form'=>$form
        ]);
    }

    #[Route('/tricks/create', name: 'app_tricks_createtrick', methods: ['GET','POST'])]
    public function createTrick(Request $request, EntityManagerInterface $manager): Response{

        $trick = new Trick();

        $form = $this->createForm(TricksType::class,$trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $trick = $form->getData();

            $name = $form->get('name')->getData();
            $name = ucfirst(strtolower($name));

            $trick->setName($name);

            $groupe = $form->get('tricksGroup')->getData();
            $groupe = ucfirst(strtolower($groupe));

            $trick->setTricksGroup($groupe);

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
//regex
                $src = explode("src=",$video);
                $src = explode("\"",$src[1]);

                $trick->setVideo([$src[1]]);
            }

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash('success','Votre figure a bien été ajouté');

            return $this->redirect('/#tricks');
        }

        return $this->render('tricks/createTrick.html.twig',[
            'form'=>$form->createView()
        ]);

    }


    #[Route('/tricks/update/{slug}', name: 'app_tricks_updatetrick', methods: ['GET','POST'])]
    public function updateTrick(Request $request, EntityManagerInterface $manager, TrickRepository $trickRepository): Response{

        $slug = $request->get('slug');

        $trick = $trickRepository->findOneBy(['name'=>$slug]);
        $images = $trick->getPicture();
        $videos = $trick->getVideo();

        $form = $this->createForm(TricksType::class,$trick);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()){

            $trick = $form->getData();

            $name = $form->get('name')->getData();
            $name = ucfirst(strtolower($name));

            $trick->setName($name);

            $groupe = $form->get('tricksGroup')->getData();
            $groupe = ucfirst(strtolower($groupe));

            $trick->setTricksGroup($groupe);

            $picture = $form->get('images')->getData();

            $video = $form->get('video')->getData();

            if ($picture){

                $newFilesName = $images;

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
                $videos [] = $src[1];

                $trick->setVideo($videos);
            }

            $manager->persist($trick);
            $manager->flush();

            $this->addFlash('success','Votre figure a bien été modifié');

            return $this->redirect('/#tricks');
        }

        return $this->render('tricks/updateTrick.html.twig',[
            'form'=>$form->createView(),
            'trick'=>$trick
        ]);

    }

}
