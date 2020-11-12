<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(): Response
    {
        $post = new Post();
        $post -> setTitre('Article 1')
        ->setContent(" içi c'est le contenu de notre article ")
        ->setUrl_alias(' \url\article ')
        ->setPublishedAt(new \datetime());


        return $this->render('blog/index.html.twig', [

             'message' => 'Page d\'accueil'
        ]);
    }

    /**
     * @Route("/post/{id}", name="post")
     */
    public function post(String $id): Response
    {
        return $this ->render('blog/post.html.twig',[
            'id' => $id
        ]);
    }
}
