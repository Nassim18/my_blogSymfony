<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/posts/{id}", name="post")
     */
    public function post(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class,$comment);

            $comment -> setCreatedAt(new \DateTime());
            $comment -> setPost($post);

            $form -> handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $em -> persist($comment);
                $em -> flush();
            }

        return $this->render('blog/post.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }
}
