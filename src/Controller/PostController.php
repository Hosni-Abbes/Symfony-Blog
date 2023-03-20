<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Services\BlogPagination;
use App\Services\FileUpload;
use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PostController extends AbstractController
{
    /**
     * @Route("/", name="app_post")
     */
    public function index(PostRepository $postRepository,BlogPagination $blogPagination, Request $request, Security $security, FileUpload $fileUpload, ManagerRegistry $doctrine)
    {
        $post = new Post;
        $user = $security->getUser();

        //Create Form
        $form = $this->createForm(PostType::class, $post);

        // Handle request
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $form->get('image')->getData();
            if($file){
                $fileUpload->tryUpload($file, $post);
            }
            // Add date
            $date = new DateTimeImmutable();
            $post->setPostdate($date);

            //Set the post user 
            $post->setUser($user);
            // Persist
            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('app_post'));
        }

        // $posts = $postRepository->findAll();
        $posts = $blogPagination->postsPagination($request);


        return $this->render('post/index.html.twig',[
            'posts' => $posts,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/show/{id}", name="show_post")
     */
    public function show(Post $post, Request $request,BlogPagination $blogPagination, Security $security, ManagerRegistry $doctrine, CommentRepository $commentRepository)
    {
        $comment = new Comment();
        $user = $security->getUser();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if($commentForm->isSubmitted() && $commentForm->isValid()) {
            $date = new DateTimeImmutable();
            $comment->setCommentDate($date);
            $comment->setPost($post);
            $comment->setUser($user);

            $post->setCommentscount($post->getCommentscount() + 1);

            $em = $doctrine->getManager();
            $em->persist($comment, $post);
            $em->flush();

            return $this->redirect($this->generateUrl('show_post', ['id' => $post->getId()]));
        }

        // Get Post Comments
        // $comments = $commentRepository->findBy(['post' => $post->getId()]);
        $comments = $blogPagination->commentsPagination($request, $post->getId());


        return $this->render('post/show.index.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
            'comments' => $comments
        ]);
    }


    /**
     * @Route("/edit/{id}", name="edit_post")
     */
    public function edit(Post $post, Request $request, Security $security, FileUpload $fileUpload, ManagerRegistry $managerRegistry) {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if($file) {
                //Delete file
                $fileUpload->tryDelete($post);
                //Upload file
                $fileUpload->tryUpload($file, $post);
            }
            $em = $managerRegistry->getManager();
            $em->persist($post);
            $em->flush();
            
            return $this->redirect($this->generateUrl('app_post'));
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post
        ]);
        
    }


    
    /**
     * @Route("/delete/{id}", name="delete_post")
     */
    public function delete(Post $post, CommentRepository $commentRepository, FileUpload $fileUpload, ManagerRegistry $managerRegistry) {
        if ($post->getImage()){
            // Delete File
            $fileUpload->tryDelete($post);
        }
        
        //get Comments Related to this post
        $relatedComments = $commentRepository->findBy(["post" => $post]);


        // Remove Post and its Comments from DB
        $em = $managerRegistry->getManager();
        foreach ($relatedComments as $comment) {
            $em->remove($comment);
        }
        $em->remove($post);
        $em->flush();
        
        // Redirect to Home page
        return $this->redirect($this->generateUrl("app_post"));
    }


    // Update Likes when click btn
    /**
     * @Route("/like/{id}/{like}", defaults={"id"="", "like"=""}, name="like_post")
     */
    public function like(Post $post, ManagerRegistry $managerRegistry, Request $request, $like) {
        $likes = $post->getLikescount();
        $post->setLikescount($likes+1);

        $em = $managerRegistry->getManager();
        $em->persist($post);
        $em->flush();
        
        // 
        $uri = explode('/', $request->getRequestUri());
        if (end($uri) == "like") {
            return $this->redirect($this->generateUrl('show_post', ["id" => $post->getId()]));
        }
        return $this->redirect($this->generateUrl('app_post'));
    }




    
}
