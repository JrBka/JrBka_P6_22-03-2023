<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(TrickRepository $trickRepository): Response
    {

        $tricks = $trickRepository->findTricksPaginated(15);

        return $this->render('home/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    #[Route('/tricks', name: 'app_home_tricks', methods: ['GET'])]
    public function getTricks(TrickRepository $trickRepository): Response{

        $tricks = $trickRepository->findAll();

        return $this->render('home/index.html.twig',[
            'tricks'=>$tricks
        ]);
    }

}
