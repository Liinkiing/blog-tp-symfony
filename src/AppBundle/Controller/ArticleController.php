<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
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
     * @Route("/{day}/{month}/{year}/{slug}", name="article_show", requirements={"day": "\d+", "month": "\d+", "year": "\d+"})
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction(Request $request, $day, $month, $year, $slug) {
        $date = \DateTime::createFromFormat('d/m/Y', "$day/$month/$year");
        $article =  $this->getDoctrine()->getRepository('AppBundle:Article')->findOneBy(
            ['slug' => $slug]
        );
        if(!$article) throw $this->createNotFoundException("L'article est pas trouvé fdp");
        if($article->getCreatedAt()->format('d') == $day && $article->getCreatedAt()->format('m') == $month && $article->getCreatedAt()->format('Y') == $year) {
            return $this->render('article/show.html.twig', [
                'article' => $article
            ]);
        } else {
            throw $this->createNotFoundException("L'article n'a pas été trouvé :(");
        }

    }
}
