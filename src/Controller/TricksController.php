<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\PictureType;
use App\Form\TricksType;
use App\Form\VideoType;
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

        // Define trick
        switch ($slug){
            case 'create':
                $trick = new Trick();
                break;
            case 'update':
                $trick = $trickRepository->findOneBy(['name' => $trickName]);
                $images = $trick->getPicture();
                $videos = $trick->getVideo();
                break;
            case 'delete':
                // Delete trick
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
                return $this->redirectToRoute('app_home');
        }

        // Creation oh the form
        $updateForm = $this->createForm(PictureType::class, $trick);
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
                $srcVideos = ($slug == 'create') ? [] : $videos;
                //Get content of 'src' in the tag
                preg_match_all('/src=\"[^\"]*\"/', $video, $result);
                foreach ($result[0] as $src){
                    $src = explode("\"", $src);
                    $srcVideos [] = $src[1];
                }
                $trick->setVideo($srcVideos);
            }

            //Save $trick in database
            $manager->persist($trick);
            $manager->flush();

            $var = ($slug=='create') ? 'ajouté' : 'modifié';
            $this->addFlash('success', 'Votre figure a bien été '.$var);
            return $this->redirectToRoute('app_home');

        }

        //Return the form
        if ($slug === 'create'){
            return $this->render('tricks/createTrick.html.twig', [
                'form' => $form->createView()
            ]);
        }else{
            return $this->render('tricks/updateTrick.html.twig',[
                'form'=>$form->createView(),
                'updateForm' => $updateForm->createView(),
                'trick'=>$trick
            ]);
        }
    }



    /**
     * This function change or remove pictures and videos
     *
     * @param Request $request
     * @param TrickRepository $trickRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/tricks/{trickName}/{slug}/{mediaIndex}', name: 'app_tricks_editmedia',methods: ['GET','POST'])]
    public function editMedia(Request $request,TrickRepository $trickRepository,EntityManagerInterface $manager): Response
    {
        $trickName = $request->get('trickName');
        $slug = $request->get('slug');
        $index = $request->get('mediaIndex');

        // Get Trick entity
        $trick = $trickRepository->findOneBy(['name' => $trickName]);

        // Get pictures array
        $pictures = $trick->getPicture();
        if (isset($pictures[$index])) {
            $image = $pictures[$index];
        }

        // Get videos array
        $videos = $trick->getVideo();
        if (isset($videos[$index])){
            $video = $videos[$index];
        }

        // Creation of the form
        $formType = $slug == 'update-picture' || $slug == 'updatePicture' ? PictureType::class : VideoType::class;
        $form = $this->createForm($formType, $trick);
        //Get the request
        $form->handleRequest($request);

        switch ($slug){
            case 'update-picture':
                 return $this->render('tricks/updatePicture.html.twig',[
                    'form'=>$form->createView(),
                    'trickName'=>$trickName,
                    'index'=>$index,
                    'picture'=>$image
                ]);
            case 'update-video':
                return $this->render('tricks/updateVideo.html.twig',[
                    'form'=>$form->createView(),
                    'trickName'=>$trickName,
                    'index'=>$index,
                    'video'=>$video
                ]);
            case 'deleteVideo':
                //Remove video from array 'videos' and sort
                unset($videos[$index]);
                sort($videos);
                break;
            case 'deletePicture':
                //Remove image from array 'pictures' and sort
                unset($pictures[$index]);
                sort($pictures);
                break;
        }

        if ($form->isSubmitted()) {
            if ($slug == 'updatePicture') {
                // Define a new name for the image
                $file = $form->get('images')->getData();
                $newFilename = time() . uniqid() . '.' . $file->guessExtension();
                //Save the image in 'images' directory
                try {
                    $file->move(
                        // Call the service
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    echo $e->getMessage();
                }
                //Replace the old picture with the new one in the 'picture' array of the trick entity
                $pictures = array_replace($pictures, [$index => $newFilename]);
            } elseif ($slug == 'updateVideo') {
                // Extract the src of the embed tag
                $newVideo = $form->get('video')->getData();
                preg_match('/src=\"[^\"]*\"/', $newVideo, $result);
                $newVideo = explode("\"", $result[0]);
                //Replace the old picture with the new one in the 'picture' array of the trick entity
                $videos = array_replace($videos, [$index=> $newVideo[1]]);
            }
        }

        // Sets new trick entity values and saves them to the database
        $trick->setVideo($videos);
        $trick->setPicture($pictures);
        $manager->persist($trick);
        $manager->flush();

        if ($slug == 'deletePicture' || $slug == 'updatePicture' ){
            //Remove the image in 'images' directory
            $imageToBeDeleted = $this->getParameter('images_directory').'/'.$image;
            if (file_exists($imageToBeDeleted)){
                unlink($imageToBeDeleted);
            }
        }

        // Redirect to updateTrick page and show flash message
        $var = $slug == 'deletePicture' || $slug == 'deleteVideo' ? 'supprimé' : 'modifié';
        $this->addFlash('success', 'Votre image a bien été '.$var);
        return $this->redirect('/tricks/update/'.$trickName);
    }

}

