<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/categorie", name="categorie")
 */

class CategorieController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $categories = $this->entityManager->getRepository(Categorie::class)->findAll();

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("show/{id}", name="show")
     */
    public function show(Categorie $categorie): Response
    {
        $articles = $categorie->getArticles();

        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function add(Request $request): Response
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->entityManager->persist($categorie);
            $this->entityManager->flush();

            return $this->redirectToRoute('categorieshow',  ['id' => $categorie->getId()]);
        }

        dump($form->createView());

        return $this->render('categorie/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Categorie $categorie
     * @param Request $request
     * @return Response
     */
    public function edit(Categorie $categorie, Request $request): Response
    {

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->entityManager->persist($categorie);
            $this->entityManager->flush();

            return $this->redirectToRoute('categorieshow',  ['id' => $categorie->getId()]);
        }

        return $this->render('categorie/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Categorie $categorie): Response
    {

        $this->entityManager->remove($categorie);
        $this->entityManager->flush();

        return $this->redirectToRoute('categorieindex');

    }
}
