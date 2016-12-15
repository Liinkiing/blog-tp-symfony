<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Commentaire;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends Controller
{
    /**
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/articles/", name="article_list")
     */
    public function listAction(Request $request)
    {

        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();

        return $this->render('article/list.html.twig', [
            'articles' => $articles
        ]);

    }

    /**
     * @param Request $request
     * @param $month
     * @param $year
     * @Route("/archive/{month}/{year}", name="article_list_filter", requirements={"month": "\d+", "year": "\d+"})
     */
    public function listFilteredAction(Request $request, $month, $year)
    {
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findByDate($month, $year);
        return $this->render('article/list_filter.html.twig', ['articles' => $articles, 'filteredDate' => \DateTime::createFromFormat('m/Y', "$month/$year")]);
    }

    /**
     * @param Request $request
     * @param Article $article
     * @Route("/comment/{slug}/new", name="comment_new")
     * @Method({"POST"})
     */
    public function newCommentAction(Request $request, Article $article)
    {
        if (!$article) throw $this->createNotFoundException();
        $comment = new Commentaire();
        $comment->setArticle($article)
            ->setAuthor($this->getUser())
            ->setMessage($request->get('comment'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
        $this->addFlash("success", "Le commentaire a bien été rajouté");
        return $this->redirectToRoute("article_show", [
            'day' => $article->getCreatedAt()->format('d'),
            'month' => $article->getCreatedAt()->format('m'),
            'year' => $article->getCreatedAt()->format('Y'),
            'slug' => $article->getSlug()
        ]);
    }

    /**
     * @param Request $request
     * @Route("/{day}/{month}/{year}/{slug}", name="article_show", requirements={"day": "\d+", "month": "\d+", "year": "\d+"})
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showByDateAction(Request $request, $day, $month, $year, $slug)
    {
        $date = \DateTime::createFromFormat('d/m/Y', "$day/$month/$year");
        $article = $this->getDoctrine()->getRepository('AppBundle:Article')->findOneBy(
            ['slug' => $slug]
        );
        if (!$article) throw $this->createNotFoundException("L'article est introuvable");
        if ($article->getCreatedAt()->format('d') == $day && $article->getCreatedAt()->format('m') == $month && $article->getCreatedAt()->format('Y') == $year) {
            return $this->render('article/show.html.twig', [
                'article' => $article
            ]);
        } else {
            throw $this->createNotFoundException("L'article est introuvable");
        }

    }
}
