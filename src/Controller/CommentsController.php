<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentsType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentsController extends AbstractController
{
    /**
     * This function show comments
     *
     * @param CommentRepository $commentRepository
     * @param int $page
     * @param string $slug
     * @param int $trick
     * @return array
     */
    public function getComments(CommentRepository $commentRepository, int $page, string $slug,int $trick):array
    {
        //Comments pagination
        return $commentRepository->findCommentsPaginated($page,10,$slug,$trick);
    }

    /**
     * This function create a form
     *
     * @return FormView
     */
    public function createFormComment(): FormView
    {
        $comment = new Comment();

        $form = $this->createForm(CommentsType::class, $comment);

        return $form->createView();

    }

    /**
     * This function create comments
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param TrickRepository $trickRepository
     * @param CommentRepository $commentRepository
     * @return Response
     */
    #[isGranted('ROLE_USER')]
    #[Route('/comment/create',name: 'app_comments_createcomment',methods: ['POST','GET'])]
    public function createComment(Request $request, EntityManagerInterface $manager,TrickRepository $trickRepository,CommentRepository $commentRepository): Response
    {
        $comment = new Comment();

        $trickName = $request->query->get('trick');

        $trick = $trickRepository->findOneBy(['name'=>$trickName]);
        if (!isset($trick)){
            $this->addFlash('danger', 'Cette figure n\'existe pas !');
            return $this->redirectToRoute('app_home');
        }
        $comment->setTrick($trick);

        $user = $this->getUser();
        $comment->setUserId($user);

        $trickId = $trick->getId();

        $page = $request->query->getInt('page',1);

        $comments = $this->getComments($commentRepository,$page,$trickName,$trickId);

        $form = $this->createForm(CommentsType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $comment = $form->getData();

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été ajouté !');
        }


        return $this->render('tricks/showTrick.html.twig',[
            'trick'=>$trick,
            'comments'=>$comments,
            'form'=>$form
        ]);
    }

}

