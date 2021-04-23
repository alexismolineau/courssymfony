<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentaire;
use App\Entity\User;
use App\Form\ArticleType;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/article", name="article")
 */
class ArticleController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {

        $articles = $this->getDoctrine()->getRepository(Article::class)->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     */
    public function show(Article $article, Request $request): Response
    {
        $commentaires = $article->getCommentaires();

        foreach($commentaires as &$comments){
            $repository = $this->entityManager->getRepository(User::class);
            $user = $repository->findOneBy(['id' => $comments->getUtilisateur()->getId()]);
            $comments->setUtilisateur($user);
        }
        unset($comments);

        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getUser();
            $commentaire->setUtilisateur($user);
            $commentaire->setArticle($article);

            $this->entityManager->persist($commentaire);
            $this->entityManager->flush();

            return $this->redirectToRoute('articleshow', ['id' => $article->getId()]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentaires' => $commentaires,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/add", name="ajout_article")
     * @param Request $request
     * @return Response
     */
    public function add(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            if($article->getImgSrc()){
                $file = $form->get('imgSrc')->getData();
                $fileName = uniqid('image', true) . '.' . $file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('img_folder'), $fileName
                    );
                }
                catch(FileException $e){
                    return new Response($e->getMessage());
                }

                $article->setImgSrc($fileName);
            }

            $article->setDateAjout(new \DateTime());



            $entityManager = $this->entityManager;
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('articleindex');
        }


        return $this->render('article/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Article $article
     * @param Request $request
     * @return Response
     */
    public function edit(Article $article, Request $request): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);



        if($form->isSubmitted() && $form->isValid()){

            if($article->getImgSrc() !== null){
                $file = $form->get('imgSrc')->getData();
                $fileName = uniqid('image', true) . '.' . $file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('img_folder'), $fileName
                    );
                }
                catch(FileException $e){
                    return new Response($e->getMessage());
                }

                $article->setImgSrc($fileName);
            }
            else {
                $uow = $this->getDoctrine()->getManager()->getUnitOfWork();
                $oldArticle = $uow->getOriginalEntityData($article);
                $article->setImgSrc($oldArticle['imgSrc']);
            }

            $article->setDateAjout(new \DateTime());


            $entityManager = $this->entityManager;
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('articleindex');
        }


        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article
        ]);
    }


    /**
     * @Route("/delete/{id}", name="delete")
     * @param Article $article
     * @return Response
     */
    public function delete(Article $article): Response
    {

        $entityManager = $this->entityManager;
        $entityManager->remove($article);
        $entityManager->flush();


        return $this->redirectToRoute('articleindex');

    }

}
