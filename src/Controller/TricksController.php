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

    /**
     * This function show all tricks
     *
     * @param TrickRepository $trickRepository
     * @return Response
     */
    #[Route('/tricks', name: 'app_tricks', methods: ['GET'])]
    public function getTricks(TrickRepository $trickRepository): Response{

        $tricks = $trickRepository->findAll();

        return $this->render('home/index.html.twig',[
            'tricks'=>$tricks
        ]);
    }

    /**
     * This function show a trick and those comments
     *
     * @param TrickRepository $trickRepository
     * @param CommentRepository $commentRepository
     * @param CommentsController $commentsController
     * @param Request $request
     * @return Response
     */
    #[Route('/tricks/details/{slug}', name: 'app_tricks_getonetrick', methods: ['GET'])]
    public function getOneTrick(TrickRepository $trickRepository,CommentRepository $commentRepository,
                                CommentsController $commentsController, Request $request):Response{

        $name = $request->attributes->get('slug');

        $trick = $trickRepository->findOneBy(['name'=>$name]);
        $trickId = $trick->getId();

        $page = $request->query->getInt('page',1);

        $comments = $commentsController->getComments($commentRepository,$page,$name,$trickId);

        $form = $commentsController->createFormComment();

        return $this->render('tricks/showTrick.html.twig',[
            'trick'=>$trick,
            'comments'=>$comments,
            'form'=>$form
        ]);
    }


    /**
     * This function manage the creation, the update and the deletion for the Trick entity.
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TrickRepository $trickRepository
     * @return Response
     */
    #[Route('/tricks/{slug}/{name}', name: 'app_tricks_managetrick', methods: ['GET','POST'])]
    public function manageTrick(Request $request, EntityManagerInterface $manager,TrickRepository $trickRepository): Response
    {
        $slug = $request->get('slug');
        $trickName = $request->get('name');

        //Define $trick
        if ($slug === 'create') {
            $trick = new Trick();
        } elseif ($slug === 'update') {
            $trick = $trickRepository->findOneBy(['name' => $trickName]);
            $images = $trick->getPicture();
            $videos = $trick->getVideo();
        }

        //Delete $trick
        if ($slug === 'delete'){
            $trick = $trickRepository->findOneBy(['name' => $trickName]);
            $images = $trick->getPicture();
            if (!empty($images)){
                foreach ($images as $image){
                    //Delete the images in 'images' directory
                    $imageToBeDeleted = $this->getParameter('images_directory').'/'.$image;
                    if (file_exists($imageToBeDeleted)){
                        unlink($imageToBeDeleted);
                    }
                }

            }
            $trickRepository->remove($trick,true);
            $this->addFlash('success', 'Votre figure a bien été supprimé');
            return $this->redirect('/#tricks');
        }

        // Creation oh the form
        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $trick = $form->getData();

            $name = $form->get('name')->getData();
            $name = ucfirst(strtolower($name));
            $trick->setName($name);

            $groupe = $form->get('tricksGroup')->getData();
            $groupe = ucfirst(strtolower($groupe));
            $trick->setTricksGroup($groupe);

            $picture = $form->get('images')->getData();
            $video = $form->get('video')->getData();

            if ($picture) {
                $newFilesName = ($slug == 'create') ? [] : $images;
                foreach ($picture as $image) {
                    //Define a new name
                    $newFilename = time() . uniqid() . '.' . $image->guessExtension();
                    $newFilesName [] = $newFilename;
                    //Save the image in 'images' directory
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
            if ($video) {
                $srcVideo = ($slug == 'create') ? [] : $videos;
                //Get content of 'src'
                preg_match('/src=\"[^\"]*\"/', $video, $src);
                $src = explode("\"", $src[0]);
                $srcVideo [] = $src[1];

                $trick->setVideo($srcVideo);
            }

            //Save $trick in database
            $manager->persist($trick);
            $manager->flush();

            $var = ($slug=='create') ? 'ajouté' : 'modifié';
            $this->addFlash('success', 'Votre figure a bien été '.$var);
            return $this->redirect('/#tricks');

        }

        //Return the form
        if ($slug === 'create'){
            return $this->render('tricks/createTrick.html.twig', [
                'form' => $form->createView()
            ]);
        }else{
            return $this->render('tricks/updateTrick.html.twig',[
                'form'=>$form->createView(),
                'trick'=>$trick
            ]);
        }
    }



}

