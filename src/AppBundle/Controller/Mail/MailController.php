<?php

namespace AppBundle\Controller\Mail;

use AppBundle\Entity\Utilisateur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MailController extends Controller
{

    /**
     * @param Request $request
     * @param Utilisateur $user
     * @Route("/mail/send/verification/{id})", name="email_send_verification", requirements={"id": "\d+"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function sendVerificationEmailAction(Request $request, Utilisateur $user){
        if(!$user) {
            return $this->createNotFoundException("Utilisateur non trouvé");
        }
        $user->setToken($this->str_random());
        $this->getDoctrine()->getManager()->flush();
        $this->get('app.utils')->sendMail("Vérifiez votre adresse email", ['no-reply@theblog.fr'], "TheBlog78", $user->getEmail(),
            $this->renderView('mails/validation.html.twig', ['user' => $user]), "text/html");
            $this->addFlash('info', "Un email de vérification vous a été envoyé. Confirmez votre inscription via cet email");
        return $this->redirectToRoute("login");
    }

    /**
     * @param Request $request
     * @Route("/mail/verification/{id}/{token}", name="email_verify")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function verifyEmailAction(Request $request, Utilisateur $user, $token){
        if(!$user) {
            return $this->createNotFoundException("Utilisateur non trouvé");
        }
        if($user->getToken() === $token) {
            $user->setToken(null)
                ->setConfirmedAt(new \DateTime('now'));
            $this->addFlash("success", "Votre profil a bien été confirmé ! Vous pouvez désormais vous connecter");
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("login");
        } else {
            if($user->getConfirmedAt() != null) {
                $this->addFlash("info", "Votre compte a déjà été confirmé. Connectez-vous dès maintenant !");
                return $this->redirectToRoute("login");
            } else {
                $this->addFlash("danger", "Impossible de vérifier l'authenticité de votre demande");
                return $this->redirectToRoute("login");
            }
        }
    }

    public function str_random($length = 30) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);

    }

}
