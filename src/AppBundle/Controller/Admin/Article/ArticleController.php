<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ArticleController extends Controller
{
    public function indexAction($name)
    {

        /** @var Article[] $articles */
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findAll();

        return $this->render('admin/article/index.html.twig', [
            'articles' => $articles
        ]);
    }
}
