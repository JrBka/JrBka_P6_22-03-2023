<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\PictureType;
use App\Form\TricksType;
use App\Form\VideoType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Service\MoveImage;
use App\Service\RemoveImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
     * @param RemoveImage $removeImage
     * @param MoveImage $moveImage
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/tricks/{slug}/{name}', name: 'app_tricks_managetrick', methods: ['GET','POST'])]
    public function manageTrick(Request $request, EntityManagerInterface $manager,TrickRepository $trickRepository,RemoveImage $removeImage,MoveImage $moveImage): Response
    {
        $slug = $request->get('slug');
        $trickName = $request->get('name');
        $user = $this->getUser();
        $trick = "";
        $images = [];
        $videos = [];

        // Define trick
        switch ($slug){
            case 'create':
                $trick = new Trick();
                break;
            case 'update':
                $trick = $trickRepository->findOneBy(['name' => $trickName]);
                if ($this->getUser() === $trick->getUser()){
                    $images = $trick->getPictures();
                    $videos = $trick->getVideos();
                }else{
                    $this->addFlash('danger', 'Vous n\'êtes pas autorisé à accéder à ce contenu !');
                    return $this->redirectToRoute('app_home');
                }
                break;
            case 'delete':
                // Delete trick
                $trick = $trickRepository->findOneBy(['name' => $trickName]);
                if ($this->getUser() === $trick->getUser()){
                    $images = $trick->getPictures();
                    // Removes the pictures in 'images' directory
                    $trickRepository->remove($trick,$removeImage,$images,true);
                    $this->addFlash('success', 'Votre figure a bien été supprimé');
                }else{
                    $this->addFlash('danger', 'Vous n\'êtes pas autorisé à effectuer cette action !');
                }
                return $this->redirectToRoute('app_home');
        }

        // Creation oh the form
        $updateForm = $this->createForm(PictureType::class, $trick);
        $form = $this->createForm(TricksType::class, $trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                $trick = $form->getData();

                $trick->setUser($user);

                $name = $form->get('name')->getData();
                $name = ucfirst(strtolower($name));
                $trick->setName($name);

                $groupe = $form->get('tricksGroup')->getData();
                $groupe = ucfirst(strtolower($groupe));
                $trick->setTricksGroup($groupe);

                $newPictures = $form->get('images')->getData();
                $newVideos = $form->get('videos')->getData();

                if ($newPictures) {
                    $newFilesName = ($slug == 'create') ? [] : $images;
                    foreach ($newPictures as $image) {
                        //Define a new name
                        $newFilename = time() . uniqid() . '.' . $image->guessExtension();
                        $newFilesName [] = $newFilename;
                        // Saves the image in 'images' directory
                        $moveImage->moveImages($image,$newFilename);
                    }
                    $trick->setPictures($newFilesName);
                }
                if ($newVideos) {
                    $srcVideos = ($slug == 'create') ? [] : $videos;
                    // Get content of 'src' in the tag
                    preg_match_all('/src=\"[^\"]*\"/', $newVideos, $result);
                    if (!empty($result[0])){
                        foreach ($result[0] as $src){
                            $src = explode("\"", $src);
                                $srcVideos [] = $src[1];
                        }
                    }else{
                        $this->addFlash('danger', 'Balise video invalide !');
                        return $this->redirectToRoute('app_home');
                    }
                    $trick->setVideos($srcVideos);
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
            // For an update if the name is changed to an empty value the rendering of the page is impossible
            if (empty($trick->getName())){
                $trick->setName($trickName);
            }
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
     * @param RemoveImage $removeImage
     * @param MoveImage $moveImage
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/tricks/{trickName}/{slug}/{mediaIndex}', name: 'app_tricks_editmedia',methods: ['GET','POST'])]
    public function editMedia(Request $request,TrickRepository $trickRepository,EntityManagerInterface $manager,RemoveImage $removeImage,MoveImage $moveImage): Response
    {

        $trickName = $request->get('trickName');
        $slug = $request->get('slug');
        $index = $request->get('mediaIndex');

        // Get Trick entity
        $trick = $trickRepository->findOneBy(['name' => $trickName]);

        // Checks if the current user is the same as the user who created the trick
        if ($this->getUser() !== $trick->getUser()){
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à accéder à ce contenu !');
            return $this->redirectToRoute('app_home');
        }else {
            // Get pictures array
            $pictures = $trick->getPictures();
            $image = "";
            if (isset($pictures[$index])) {
                $image = $pictures[$index];
            }

            // Get videos array
            $videos = $trick->getVideos();
            $video = "";
            if (isset($videos[$index])) {
                $video = $videos[$index];
            }

            // Creation of the form
            $formType = ($slug == 'updatePicturePage' || $slug == 'updatePicture' ? PictureType::class : VideoType::class);
            $form = $this->createForm($formType, $trick);

            //Get the request
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($slug == 'updatePicture') {
                    // Define a new name for the image
                    $file = $form->get('newPicture')->getData();
                    $newFilename = time() . uniqid() . '.' . $file->guessExtension();
                    // Saves the image in 'images' directory
                    $moveImage->moveImages($file, $newFilename);
                    // Removes the old image
                    $removeImage->removeImages([$image]);
                    //Replace the old picture with the new one in the 'picture' array of the trick entity
                    $pictures = array_replace($pictures, [$index => $newFilename]);
                    $trick->setPictures($pictures);
                    $this->addFlash('success', 'Votre image a bien été modifié !');
                } elseif ($slug == 'updateVideo') {
                    // Get content of 'src' in the tag
                    $newVideo = $form->get('video')->getData();
                    preg_match('/src=\"[^\"]*\"/', $newVideo, $result);
                    if (!empty($result[0])) {
                        $newVideo = explode("\"", $result[0]);
                    } else {
                        $this->addFlash('danger', 'Balise video invalide !');
                        return $this->redirectToRoute('app_home');
                    }
                    //Replace the old picture with the new one in the 'picture' array of the trick entity
                    $videos = array_replace($videos, [$index => $newVideo[1]]);
                    $trick->setVideos($videos);
                    $this->addFlash('success', 'Votre video a bien été modifié !');
                }
                // Save new trick entity values to the database
                $manager->persist($trick);
                $manager->flush();
                return $this->redirect('/tricks/update/' . $trickName);
            }

            switch ($slug) {
                case 'updatePicture':
                    if (!empty($image)) {
                        return $this->render('tricks/updatePicture.html.twig', [
                            'form' => $form->createView(),
                            'trickName' => $trickName,
                            'index' => $index,
                            'picture' => $image
                        ]);
                    } else {
                        return $this->redirect('/tricks/update/' . $trickName);
                    }
                case 'updateVideo':
                    if (!empty($video)) {
                    return $this->render('tricks/updateVideo.html.twig', [
                        'form' => $form->createView(),
                        'trickName' => $trickName,
                        'index' => $index,
                        'video' => $video
                    ]);
                    } else {
                        return $this->redirect('/tricks/update/' . $trickName);
                    }
                case 'deleteVideo':
                    //Remove video from array 'videos' and sort
                    unset($videos[$index]);
                    sort($videos);
                    $this->addFlash('success', 'Votre vidéo a bien été supprimé !');
                    break;
                case 'deletePicture':
                    //Remove image from array 'pictures' and sort
                    $removeImage->removeImages([$image]);
                    unset($pictures[$index]);
                    sort($pictures);
                    $this->addFlash('success', 'Votre image a bien été supprimé !');
                    break;
            }

            // Save new trick entity values to the database
            $trick->setPictures($pictures);
            $trick->setVideos($videos);
            $manager->persist($trick);
            $manager->flush();

            //Remove the image in 'images' directory
            if ($slug == 'deletePicture' || $slug == 'updatePicture') {
                $imageToBeDeleted = $this->getParameter('images_directory') . '/' . $image;
                if (file_exists($imageToBeDeleted)) {
                    unlink($imageToBeDeleted);
                }
            }

            // Redirect to updateTrick page
            return $this->redirect('/tricks/update/' . $trickName);

        }
    }

}

