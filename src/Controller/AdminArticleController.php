<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\RechercheType;
use App\Repository\ArticleRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminArticleController extends AbstractController
{

     /**
     * @var ArticleRepository
     */
    private $articleRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AdminArticleController constructor.
     * @param ArticleRepository $articleRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ArticleRepository $articleRepository, EntityManagerInterface $em)
    {
        $this->articleRepository = $articleRepository;
        $this->em = $em;
    }



    /**
     * @Route("/admin/article/list", name="admin_article_list")
     */
    public function index(PaginatorInterface $paginator,  Request $request): Response
    {
        $qb = $this->articleRepository->findArticle();


        $form = $this->createForm(RechercheType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $qb->where('a.title LIKE :name')
                ->setParameter('name', '%'.$data['objet'].'%');
        }
        $pagination = $paginator->paginate(
            $qb, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('admin_article/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView()
        ]);

       
    }
}
