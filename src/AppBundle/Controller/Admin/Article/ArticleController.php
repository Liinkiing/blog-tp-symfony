<?php

namespace AppBundle\Controller\Admin\Article;

use AppBundle\Entity\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ArticleController
 * @package AppBundle\Controller\Admin\Article
 * @Route("/admin/article")
 * @Security("has_role('ROLE_ADMIN')")
 */
class ArticleController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="admin_article_index")
     */
    public function indexAction()
    {

        /** @var Article[] $articles */
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();

        return $this->render('admin/article/index.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @param Request $request
     * @Route("/add", name="admin_article_add")
     */
    public function addAction(Request $request) {
        if($request->getMethod() == "GET"){
            return $this->render('/admin/article/add.html.twig');
        } else {
            $article = new Article();
            $article->setTitle($request->get("title"))
                ->setContent($request->get("content"))
                ->setThumbnail($request->get('thumbnail'))
                ->setSlug(Article::slugify($article->getTitle()))
                ->setAuthor($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash("success", "L'article a bien été ajouté !");

            return $this->redirectToRoute("admin_article_index");

        }
    }

    /**
     * @param Request $request
     * @Route("/edit/{id}", name="admin_article_edit")
     */
    public function editAction (Request $request, Article $article){
        if($request->getMethod()=="GET"){
            return $this->render("/admin/article/edit.html.twig", [
                "article"=>$article
            ]);
        } else{
            $article->setTitle($request->get("title"))
                ->setContent($request->get("content"))
                ->setThumbnail($request->get('thumbnail'))
                ->setSlug(Article::slugify($article->getTitle()))
                ->setAuthor($this->getUser())
                ->setEditedAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', "L'article a bien été edité !");
            return $this->redirectToRoute("admin_article_index");
        }
    }
    /**
     * @param Request $request
     * @Route("/delete/{id}", name="admin_article_delete")
     */
    public function deleteAction(Request $request, $id){
        $article = $this->getDoctrine()->getRepository("AppBundle:Article")->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', "L'article a bien été supprimé !");
        return $this->redirectToRoute("admin_article_index");
    }
}
