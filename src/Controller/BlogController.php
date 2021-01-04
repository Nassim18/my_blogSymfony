<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Form\CommentType;
use App\Form\UpdateType;
use App\Repository\PostRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;


class BlogController extends AbstractController
{
    public static $ripo;

    /**
     * @Route("/", name="blog")
     * @param PostRepository $ripo
     * @return Response
     */
    public function index(PostRepository $ripo): Response
    {
        BlogController::$ripo=$ripo;
        $posts = $ripo->findAll();
        $post = $this->getDoctrine()->getRepository(Post::class)->findAll();

        $latests = $this->getDoctrine()
                       ->getRepository(Post::class)
                       ->getLatest();
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(["post" => $post]);

        return $this->render('blog/index.html.twig', [

             'message' => 'Page d\'accueil',
              'posts'   => $posts,
             'comments' => $comments,
             'latests' => $latests
        ]);
    }

    /**
     * @Route("/posts", name="posts_show")
     * @return Response
     */
    public function blogPosts(): Response
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();
        $latests = $this->getDoctrine()
            ->getRepository(Post::class)
            ->getLatest();

        return $this->render('post/showAllPosts.html.twig',[
            'posts' => $posts,
            'latests' => $latests
        ]);

    }


    /**
     * @Route("/post/{url_alias}", name="post_show")
     * @param $url_alias
     * @return Response
     */
    public function renderPost($url_alias, Request $request, EntityManagerInterface $em): Response
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findOneBy(['url_alias' => $url_alias]);
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
            unset($form);
            unset($comment);
            $comment= new Comment();
            $form = $this->createForm(CommentType::class,$comment);
        }

        return $this->render('post/showPost.html.twig',[
               'post' => $post,
            'latests' => $latests,
            'user' => $user,
            'form' => $form->createView()
        ]);

        }
    /**
     * @Route("/{username}/posts", name="user_posts")
     * @param $user
     * @return Response
     */
    public function userPosts(User $user): Response
    {
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findBy(['user' => $user],['publishedAt' => 'DESC']);
        $latests = $this->getDoctrine()
            ->getRepository(Post::class)
            ->getLatest();
        return $this->render('post/userPosts.html.twig',[
            'posts' => $posts,
            'latests' => $latests,
            'user' => $user
        ]);

    }

    /**
     * @Route("/profile/{username}", name="user_profile")
     * @param $user
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function renderProfile(User $user): Response
    {
        $connecteduser = $this->getUser();
        if($connecteduser->getUsername() != $user->getUsername() || $connecteduser == null){
            return $this->render('security/error.html.twig');
        }
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findBy(['user' => $user],['publishedAt' => 'DESC']);
        $latests = $this->getDoctrine()
            ->getRepository(Post::class)
            ->getLatest();
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(["author" => $user]);
        return $this->render('profile/profile.html.twig',[
            'posts' => $posts,
            'latests' => $latests,
            'comments' => $comments,
            'user' => $user
        ]);

    }

    /**
     * @Route("/posts/add-post", name="add_post")
     * @param Request $request
     * @return Response
     */
    public function renderNew(Request $request): Response
    {
        $slugify = new Slugify();
        $post = new Post();
        //$form = $this->createForm(PostType::class, $post);
        $form = $this->createForm(PostType::class,$post, array(
        'validation_groups' => array('add')
    ));
        $user = $this->getUser();
        $post->setPublishedAt(new \DateTime('now'));
        $post->setUser($user);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $post->seturl_alias($slugify->slugify($post->getTitre()));
            $em = $this->getDoctrine()->getManager();
            //$file = new File($post->getImage().'test.jpg');
            //$filename = 'test.jpg';
            //$file->move($this->getParameter('images_directory'), $filename);
            //$post->setImage($filename);
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('profile',['username'=>$user->getUsername()]);
        }

        return $this->render('post/addPost.html.twig',[
            'newPostForm' => $form->createView()

        ]);

    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/posts/update-post/{id}", name="update_post")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function renderUpdate(Request $request, $id): Response
    {
        $slugify = new Slugify();
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $id]);
        $userConnected = $this->getUser()->getUsername();
        $userr = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=> $post->getUser()->getId()]);
        if(strcmp($userConnected , $userr->getUsername()) !== 0){
            return $this->render('security/error.html.twig');
        }

        $form = $this->createForm(UpdateType::class,$post);
        $user = $this->getUser();

        $form -> handleRequest($request);

        if($form->isSubmitted())
        {
            $em = $this->getDoctrine()->getManager();
            $post->seturl_alias($slugify->slugify($post->getTitre()));
            $post->setPublishedAt(new \DateTime('now'));
            $post->setUser($user);
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('profile',['username'=>$user->getUsername()]);
        }

        return $this->render('post/updatePost.html.twig',[
            'updatePostForm' => $form->createView()

        ]);

    }

    /**
     * @Route("/posts/delete-post/{id}", name="delete_post")
     * @param $id
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function renderDelete($id): Response
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $id]);
        $userConnected = $this->getUser()->getUsername();
        $userr = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id'=> $post->getUser()->getId()]);
        //dd($userConnected,$userr);
        if(strcmp($userConnected , $userr->getUsername()) !== 0){
            return $this->render('security/error.html.twig');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        return $this->redirectToRoute('profile',['username'=> $userConnected,'_fragment'=> 'v-pills-post-management']);
    }

    /**
     * @Route("/posts/delete-comment/{id}", name="delete_comment")
     * @param $id
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function deleteComment($id): Response
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->findOneBy(['id' => $id]);
        if(strcmp($this->getUser()->getUsername() , $comment->getAuthor()->getUsername()) !== 0){
                return $this->render('security/error.html.twig');
        }
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $comment->getPost()]);
            $em->flush();
            return $this->redirectToRoute('post_show', ['url_alias' => $post->geturl_alias()]);

    }

    /**
     * @Route("/post/{id}", name="showsPost")
     * @param $id
     * @return Response
     */
    public function showsPost($id, Request $request, EntityManagerInterface $em): Response
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

        return $this->render('blog/showPost.html.twig',[
            'post' => $post,
            'latests' => $latests,
            'form' => $form->createView()
        ]);

    }





}
