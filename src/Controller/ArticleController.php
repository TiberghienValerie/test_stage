<?php

namespace App\Controller;

use App\Entity\Article;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{

     /**
     * @var ArticleRepository
     */
    private $ArticleRepository;


     /**
     * @var EntityManagerInterface
     */
    private $em;

     /**
     * ArticleController constructor.
     * @param ArticleRepository $articleRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ArticleRepository $articleRepository, EntityManagerInterface $em)
    {
        $this->articleRepository = $articleRepository;
        $this->em = $em;
    }

    /**
     * @Route("/article/{id}-{slug}", name="article")
     */
    public function index(string $id, string $slug): Response
    {
        $articleEntity = $this->articleRepository->find($id);

        return $this->render('article/index.html.twig', [
            'articleEntity' => $articleEntity
        ]);
    }
}
