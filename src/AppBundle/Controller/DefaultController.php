<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Utilisateur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->findLastNth(4);
        return $this->render('default/index.html.twig', ['articles' => $articles]);
    }

    /**
     * @Route(name="archive")
     */
    public function getArchiveAction() {
        $articles = $this->getDoctrine()->getRepository('AppBundle:Article')->groupByYearAndMonth();
        return $this->render('archive/list.html.twig', ['articles' => $articles]);
    }
}
