<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Form\RechercheType;
use App\Repository\ArticleRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

    /**
     * @Route("/admin/article/read/{id}", name="admin_article_read")
     */
    public function read(string $id): Response
    {
        $articleEntity = $this->articleRepository->find($id);

        return $this->render('admin_article/read.html.twig', [
            'articleEntity' => $articleEntity
        ]);
    }


    /**
     * @Route("/admin/article/delete/{id}", name="admin_article_delete")
     */
    public function delete(string $id, Request $request): Response
    {
        $articleEntity = $this->articleRepository->find($id);
        $ancienPhoto =  $articleEntity->getCover();
        if (file_exists($this->getParameter('article_directory'). '/'.$ancienPhoto)) {
            try {
                  unlink($this->getParameter('article_directory'). '/'.$ancienPhoto);
            } catch(\Exception $exception) {
            // unable to upload the photo, give up
            }
        }
        $this->em->remove($articleEntity);
        $this->em->flush();
        return $this->redirectToRoute('admin_article');
    }


    /**
     * @Route("/admin/article/update/{id}", name="admin_article_update")
     */
    public function update(string $id, Request $request, SluggerInterface $slugger): Response
    {
        $articleEntity = $this->articleRepository->find($id);

        $form = $this->createForm(ArticleType::class, $articleEntity);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('cover')->getData();
            if($photo !== null) {

                $ancienPhoto = $articleEntity->getCover();
                if (file_exists($this->getParameter('article_directory') . '/' . $ancienPhoto)) {
                    try {
                        unlink($this->getParameter('article_directory') . '/' . $ancienPhoto);
                    } catch (FileException $e) {
                        // unable to upload the photo, give up
                    }
                }

                $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '.' . $photo->guessExtension();
                if (!file_exists($this->getParameter('article_directory') . '/' . $newFilename)) {
                    try {
                        $photo->move($this->getParameter('article_directory'), $newFilename);
                    } catch (FileException $e) {
                        // unable to upload the photo, give up
                    }
                }
                $articleEntity->setCover($newFilename);
            }

            $this->em->persist($articleEntity);
            $this->em->flush();
            return $this->redirectToRoute('admin_article_list');
        }

        return $this->render('admin_article/update.html.twig', [
            'form' => $form->CreateView(),
            'cover' => $articleEntity->getCover()
        ]);
    }

    /**  
     * @Route("/admin/article/add", name="admin_article_add")
     */
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        $articleEntity = new Article();
        $form = $this->createForm(ArticleType::class, $articleEntity);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('cover')->getData();
            $originalFilename = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'.'.$photo->guessExtension();
            if (!file_exists($this->getParameter('article_directory').'/'.$newFilename)) {
                try {
                    $photo->move($this->getParameter('article_directory'), $newFilename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
            }
            $articleEntity->setCover($newFilename);
            $articleEntity->setDateCreated(new \DateTime());
            $this->em->persist($articleEntity);
            $this->em->flush();
            return $this->redirectToRoute('admin_article_list');
        }

        return $this->render('admin_article/create.html.twig', [
            'form' => $form->CreateView()
        ]);
    }


}
