<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="comment")
     */
    public function index(): Response
    {
        
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }


    // Liike Comments
    /**
     * @Route("/commentlike/{id}/{post}", defaults={"id"="", "post"=""}, name="like_comment")
     */
    public function likeComment(Comment $comment, ManagerRegistry $managerRegistry, Request $request, $post) {
        $comment->setLikes($comment->getLikes() + 1);

        $em = $managerRegistry->getManager();
        $em->persist($comment);
        $em->flush();

        return $this->redirect($this->generateUrl('show_post', ["id" => $post]));
    }

    /**
     * @Route("/comment/edit/{id}", name="comment_edit")
     */
    public function editComment(Comment $comment, Request $request, ManagerRegistry $managerRegistry ) {
        // $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted()){
            $em = $managerRegistry->getManager();
            $em->persist($comment);
            $em->flush();
            
            //Get Post Id
            $postId = $comment->getPost()->getId();
            return $this->redirect($this->generateUrl('show_post', [
                "id" => $postId
            ]));
        }

        return $this->render('comment/edit.html.twig', [
            "commentForm" => $commentForm->createView()
        ]);
    }

    /**
     * @Route("/comment/delete/{id}", name="comment_delete")
     */
    public function deleteComment(Comment $comment, ManagerRegistry $managerRegistry) {
        $postId = $comment->getPost()->getId();

        $em = $managerRegistry->getManager();
        $em->remove($comment);
        $em->flush();
        

        return $this->redirect($this->generateUrl('show_post', [
            "id" => $postId
        ]));
    }





}
