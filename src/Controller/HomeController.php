<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @param TrickRepository $trickRepository
     * @return Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(TrickRepository $trickRepository): Response
    {
        // Numbers of tricks
        $nbTricks = $trickRepository->countTricks();
        // Tricks pagination
        $tricks = $trickRepository->findTricksPaginated(15);

        return $this->render('home/index.html.twig', [
            'tricks' => $tricks,
            'nbTricks' => $nbTricks
        ]);
    }

}

