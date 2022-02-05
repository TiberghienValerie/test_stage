<?php

namespace App\Controller;

use App\Entity\Article;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
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
     * HomeController constructor.
     * @param ArticleRepository $articleRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ArticleRepository $articleRepository, EntityManagerInterface $em)
    {
        $this->articleRepository = $articleRepository;
        $this->em = $em;
    }


    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {

        $articleEntities = $this->articleRepository->findAll();
        return $this->render('home/index.html.twig', [
            'articleEntities' => $articleEntities
        ]);
    }
}
