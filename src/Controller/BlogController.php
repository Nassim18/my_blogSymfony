<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{   public static $ripo;

    /**
     * @Route("/blog", name="blog")
     * @return Response
     */
    public function index(PostRepository $ripo): Response
    {
        BlogController::$ripo=$ripo;
        $posts = $ripo->findAll();


        return $this->render('blog/index.html.twig', [

             'message' => 'Page d\'accueil',
              'posts'   => $posts
        ]);
    }

    /**
     * @Route("/post/{id}", name="post")
     */
    public function post(Post $post): Response
    {
        return $this->render('blog/post.html.twig', [
            'post' => $post
        ]);
    }
}
