<?php 

namespace App\Services;

use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class BlogPagination {

    private $postRepository;
    private $commentRepository;
    private $paginator;

    public function __construct(PostRepository $postRepository, CommentRepository $commentRepository, PaginatorInterface $paginator){
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->paginator = $paginator;
    }



    // Posts Pagination
    public function postsPagination($request){
        $postsQuery = $this->postRepository->findByPagination();
        $page = $request->query->getInt('page', 1);
        $limit = 6;

        //Return Pagination
        return $this->paginator->paginate($postsQuery, $page, $limit);
    }



    // Comments Pagination
    public function commentsPagination($request, $postId){
        $commentsQuery = $this->commentRepository->findByPagination($postId);
        $page = $request->query->getInt('page', 1);
        $limit = 2;

        return $this->paginator->paginate($commentsQuery, $page, $limit);
    }




}