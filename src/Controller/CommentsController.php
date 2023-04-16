<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CommentsType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @return Response
     */
    #[Route('/comment/create',name: 'app_comments_createcomment',methods: ['POST','GET'])]
    public function createComment(Request $request, EntityManagerInterface $manager,TrickRepository $trickRepository,UserRepository $userRepository): Response
    {

        $comment = new Comment();

        $form = $this->createForm(CommentsType::class, $comment);

        $form->handleRequest($request);

        $comment = $form->getData();

        //get userid courant
        $userId = $request->query->get('userId');
        $user = $userRepository->find($userId);
        $comment->setUserId($user);

        $slug = $request->query->get('trick');
        $trick= $trickRepository->findOneBy(['name'=>$slug]);
        $comment->setTrick($trick);

        if (!empty($userId) && !empty($trick)){
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a bien été ajouté !');

            return $this->redirect('/tricks/details/'.$slug);
        }else{
            $this->addFlash('danger', 'Votre commentaire n\'a pas pu être ajouté !');

            return $this->redirect('/tricks/details/'.$slug);
        }


    }

}

