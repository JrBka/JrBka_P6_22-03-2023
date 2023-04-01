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
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function createTrick(Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response{

        $trick = new Trick();
        $form = $this->createForm(TricksType::class,$trick);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $trick = $form->getData();

            $picture = $form->get('image')->getData();

            if ($picture){

                $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$picture->guessExtension();

                try {
                    $picture->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    echo $e->getMessage();
                }

                $trick->setPicture($newFilename);

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
