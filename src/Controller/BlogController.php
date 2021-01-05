<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Contact;
use App\Entity\Post;
use App\Entity\User;
use App\Form\ContactType;
use App\Form\PostType;
use App\Form\CommentType;
use App\Form\UpdateType;
use App\Repository\PostRepository;
use App\Security\MailerService;
use App\Security\MessageService;
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
     * @Route("/admin/{username}", name="admin_profile")
     * @param $user
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function renderAdminPage(User $user): Response
    {
        $connecteduser = $this->getUser();
        if($connecteduser->getUsername() != $user->getUsername() || $connecteduser == null){
            return $this->render('security/error.html.twig');
        }
        $posts = $this->getDoctrine()
            ->getRepository(Post::class)
            ->findAll();
        $latests = $this->getDoctrine()
            ->getRepository(Post::class)
            ->getLatest();
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findAll();
        $em = $this->getDoctrine()->getManager();
        $userConnected = $this->getUser()->getId();
        $users = $em->createQuery('SELECT u FROM App\Entity\User u WHERE u.id != ' . $userConnected)->getResult();
        //dd($em);
        return $this->render('profile/admin.html.twig',[
            'posts' => $posts,
            'latests' => $latests,
            'comments' => $comments,
            'user' => $user,
            'users' =>$users
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
            $image = $form->getData()->getImage();
            if(!$this->endsWith($image,'.jpeg') && !$this->endsWith($image,'.gif') && !$this->endsWith($image,'.jpg') &&  !$this->endsWith($image,'.png')){
                dd("Please enter a valid link for the image");
            }
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
        //dd($this->getUser()->getRoles()[0]);
        if(strcmp($userConnected , $userr->getUsername()) !== 0 && strcmp($this->getUser()->getRoles()[0], 'ROLE_ADMIN') !== 0){
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
            if($this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {
                return $this->redirectToRoute('admin_profile',['username'=>$user->getUsername()]);
            }else{
                return $this->redirectToRoute('profile',['username'=>$user->getUsername()]);
            }
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
        if(strcmp($userConnected , $userr->getUsername()) !== 0 && strcmp($this->getUser()->getRoles()[0], 'ROLE_ADMIN') !== 0){
            return $this->render('security/error.html.twig');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        if($this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {
            return $this->redirectToRoute('admin_profile',['username'=>$userConnected]);
        }else{
            return $this->redirectToRoute('profile',['username'=> $userConnected,'_fragment'=> 'v-pills-post-management']);
        }
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
        if(strcmp($this->getUser()->getUsername() , $comment->getAuthor()->getUsername()) !== 0 && strcmp($this->getUser()->getRoles()[0], 'ROLE_ADMIN') !== 0){
                return $this->render('security/error.html.twig');
        }
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $comment->getPost()]);
            $em->flush();
            if($this->getUser()->getRoles()[0] == 'ROLE_ADMIN') {
                return $this->redirectToRoute('admin_profile',['username'=>$this->getUser()->getUsername()]);
            }else{
                return $this->redirectToRoute('post_show', ['url_alias' => $post->geturl_alias()]);
            }
    }

    /**
     * @Route("/admin/delete-user/{id}", name="delete_user")
     * @param $id
     * @IsGranted("ROLE_ADMIN")
     * @return Response
     */
    public function renderDeleteUser($id): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $id]);
        //dd($userConnected,$userr);
        if(strcmp($this->getUser()->getRoles()[0], 'ROLE_ADMIN') !== 0){
            return $this->render('security/error.html.twig');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('admin_profile');
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
    function endsWith( $string, $end ) {
        $length = strlen( $end );
        if( !$length ) {
            return true;
        }
        return substr( $string, -$length ) === $end;
    }


    /**
     * @Route("/contact", name="contact")
     * @param Request $request
     * @param MailerService $mailerService
     * @return Response
     */
    public function contact(Request $request,MailerService $mailerService): Response
    {
        $contact = new Contact();
        $formContact = $this->createForm(ContactType::class, $contact);
        $formContact->handleRequest($request);

        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $data = $formContact->getData();
            //dd($data->getDescription());
            $name = $data->getName();
            $email = $data->getEmail();
            //$description = $data->getDescription();
            //$contactManager->sendContact($contact);
            $mailerService->send('New message from '.$name,$email,"blogsymfony.dev@gmail.com","contact/contactform.html.twig",[
                "name" => $data->getName(),
                "description" => $data->getDescription()
            ]);
            $mailerService->send('Your message has been successefully received ',$email,$email,"contact/contactform.html.twig",[
                "name" => "My-blogSymfony",
                "description" => "Thank you for contacting us ! :D your request will be treated as soon as possible by our team, have a good day."
            ]);
            $this->get('session')->getFlashBag()->add(
                'message',
                'Thank you for contacting us !'
            );
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/contact.html.twig', [
            'formContact' => $formContact->createView()
        ]);
    }





}
