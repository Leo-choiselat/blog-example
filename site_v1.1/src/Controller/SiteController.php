<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SiteController extends AbstractController
{
    /**
     * @Route("/site", name="site")
     */
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll(); // Aussi possible d'utiliser find(One)ByTitle ou n'importe quel autre nom
        
        return $this->render('site/index.html.twig', [
            'controller_name' => 'SiteController',
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home() {
        return $this->render('site/home.html.twig', [
            'title' => 'Bienvenue !'
        ]);
    }

    /**
     * @Route("/site/new", name="site_create")
     * @Route("/site/{id}/edit", name="site_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager) {
        // On pourrait confondre la route avec /site/{id} donc on la place avant !

        if(!$article) {
            $article = new Article();
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if(!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('site_show', ['id' => $article->getId()]);
        }                

        // dump($article);

        return $this->render('site/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/site/{id}", name="site_show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager) {
        // grace au param converter, il voir une route avec un id donc il va chercher un article avec cet id. Pas forcément besoin d'instancier les classes.
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \Datetime())
                    ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('site_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('site/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }

}
