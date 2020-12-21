<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{   public static $ripo;

    /**
     * @Route("/", name="blog")
     * @param PostRepository $ripo
     * @return Response
     */
    public function index(PostRepository $ripo): Response
    {
        BlogController::$ripo=$ripo;
        $posts = $ripo->findAll();
        $latests = $this->getDoctrine()
                       ->getRepository(Post::class)
                       ->getLatest();

        return $this->render('blog/index3.html.twig', [

             'message' => 'Page d\'accueil',
              'posts'   => $posts,
             'latests' => $latests
        ]);
    }

    /**
     * @Route("/posts/{id}", name="blog_post")
     */
    public function post(Post $post, User $user,Request $request, EntityManagerInterface $em): Response
    {
        $comment = new Comment();
        //$form = $this->createForm(CommentType::class,$comment);
        $form = $this->createFormBuilder($comment)
            ->add("content", TextareaType::class, [
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add("submit", SubmitType::class, [
                "attr" => [
                   "class" => "btn btn-primary"
                ]
            ])
            ->getForm();

            $comment -> setCreatedAt(new \DateTime());
            $comment -> setPost($post);
            $comment -> setAuthor($user);

            $form -> handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $em -> persist($comment);
                $em -> flush();
            }

        return $this->render('blog/post.html.twig', [
            'post' => $post,
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_show")
     * @param $id
     * @return Response
     */
    public function show($id,Request $request, EntityManagerInterface $em): Response
    {
        $post = $this->getDoctrine()
                    ->getRepository(Post::class)
                    ->findOneBy(['id' => $id]);
        $latests = $this->getDoctrine()
                        ->getRepository(Post::class)
                        ->getLatest();

        $comment = new Comment();
        //$form = $this->createForm(CommentType::class,$comment);
        $form = $this->createForm(CommentType::class,$comment);

        $comment -> setCreatedAt(new \DateTime());
        $comment -> setPost($post);
        $user = $this->getUser();
        $comment -> setAuthor($user);

        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em -> persist($comment);
            $em -> flush();
        }

        return $this->render('blog/show2.html.twig',[
               'post' => $post,
            'latests' => $latests,
            'form' => $form->createView()
        ]);

        }
    /**
     * @Route("/blog/{username}", name="user_posts")
     * @param $user
     * @return Response
     */
    public function renderPosts(User $user): Response
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findBy(['user' => $user],['publishedAt' => 'DESC']);
        $latests = $this->getDoctrine()
            ->getRepository(Post::class)
            ->getLatest();
        return $this->render('blog/user_posts.html.twig',[
            'posts' => $posts,
            'latests' => $latests,
            'user' => $user
        ]);

    }

//    /**
//     * @Route("/blog/{id}", name="post_comments")
//     * @param $id
//     * @return Response
//     */
/*    public function renderComments($id): Response
    {
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['id' => $id],['createdAt' => 'DESC']);

        return $this->render('blog/show2.html.twig',[
            'comments' => $comments,
            'id' => $id
        ]);

    }
*/

}
