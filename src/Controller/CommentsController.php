<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentsType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentsController extends AbstractController
{
    public function getComments(CommentRepository $commentRepository, int $page, string $slug):array
    {
        $comments = $commentRepository->findCommentsPaginated($page,10,$slug);
        return $comments;
    }

    #[Route('/comment/create',name: 'app_comments_createcomment',methods: ['POST','GET'])]
    public function createComment(Request $request, EntityManagerInterface $manager,TrickRepository $trickRepository)
    {

        $comment = new Comment();

        $form = $this->createForm(CommentsType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $comment = $form->getData();

            $userId = $request->query->get('userId');
            $comment->setUserId($userId);

            $slug = $request->query->get('trick');
            $trick= $trickRepository->findOneBy(['name'=>$slug]);
            $comment->setTrick($trick);

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été ajouté');

            return $this->redirectToRoute('app_tricks_getonetrick',['slug'=>$slug]);

        }


        return $form->createView();

    }



}
