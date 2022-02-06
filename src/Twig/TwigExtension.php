<?php


namespace App\Twig;


use App\Repository\ArticleRepository;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @var ArticleRepository
     */
    private $articleRepository;

     /**
     * @var Environment
     */
    private $twigEnvironnement;

    /**
     * TwigExtension constructor.
     * @param ArticleRepository $articleRepository
     * @param Environment $twigEnvironnement
     */
    public function __construct(ArticleRepository $articleRepository, Environment $twigEnvironnement)
    {
        $this->articleRepository = $articleRepository;
        $this->twigEnvironnement = $twigEnvironnement;
    }


    public function getFilters()
    {
        return [

        ];
    }

    public function getFunctions() {
        return [
            new TwigFunction('generate_article_mini', [$this, 'generateArticleMini']),
            new TwigFunction('generate_article_simple', [$this, 'generateArticleSimple']),

        ];
    }

    public function generateArticleMini()
    {
        $articlesEntities = $this->articleRepository->findArticles();    
        return $this->twigEnvironnement->render('partial/article-mini.html.twig', [
            'articlesEntities' => $articlesEntities
        ]);
    }

    public function generateArticleSimple($articleEntity)
    {

        return $this->twigEnvironnement->render('partial/article-simple.html.twig', [
            'article' => $articleEntity
        ]);
    }

   









}