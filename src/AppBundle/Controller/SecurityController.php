<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\Commentaire;
use AppBundle\Entity\Utilisateur;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $lastUsername = $authenticationUtils->getLastUsername();

        $error = $authenticationUtils->getLastAuthenticationError();
        if($error) {
            switch ($error->getCode()) {
                case 0:
                    $this->addFlash('danger', "Identifiants incorrects");
            }
        }
        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @param Request $request
     * @Route("/password/recover", name="recover_password")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function recoverPasswordAction(Request $request) {

        $email = $request->get('_email_recovery');
        $user = $this->getDoctrine()->getRepository('AppBundle:Utilisateur')->findOneBy(['email' => $email]);
        if($user){
            $utils = $this->get('app.utils');
            $passwordToken = $utils->str_random();
            $user->setPasswordToken($passwordToken);
            $this->getDoctrine()->getEntityManager()->flush();
            $globals = $this->get('twig')->getGlobals();
            $utils->sendMail("Changement de mot de passe", null, null, $user->getEmail(), $this->renderView('mails/recover.html.twig', ['user' => $user, 'password_token' => $passwordToken]), 'text/html');
            $this->addFlash("info", "Un lien concernant la réinitialisation de votre mot de passe vous a été envoyé à votre adresse mail.");
            return $this->redirectToRoute('login');
        }
        $this->addFlash("error", "Cette adresse mail n'est associée à aucun compte !");
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request){

        if($request->getMethod() == "POST") {
            $email = $request->get('_email');
            $username = $request->get('_username');
            $password = $request->get('_password')[0];

            $userRepository = $this->getDoctrine()->getRepository('AppBundle:Utilisateur');


            $user = $userRepository->findOneBy(['username' => $username]);

            if($user){
                $this->addFlash('error', "L'utilisateur '" . $user->getUsername() . "' existe déjà !");
                return $this->redirectToRoute('login');
            }

            if($password != $request->get('_password')[1]) {
                $this->addFlash('error', "Les mots de passe ne correspondent pas !");
                return $this->redirectToRoute('login');
            }

            $usermail = $userRepository->findOneBy(['email' => $email]);
            if($usermail){
                $this->addFlash('error', "Un utilisateur avec l'email '" . $usermail->getEmail() . "' existe déjà !");
                return $this->redirectToRoute('login');
            }

            $newUser = new Utilisateur();
            $newUser->setCreatedAt(new \DateTime('now'))
                ->setEmail($email)
                ->setUsername($username)
                ->setPassword($this->get('security.password_encoder')->encodePassword($newUser, $password))
                ->addRole($this->getDoctrine()->getRepository('AppBundle:Role')->findOneBy(['role' => 'ROLE_USER']));
            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            $em->flush();

            return $this->redirectToRoute('email_send_verification', ['id' => $userRepository->findOneBy(['email' => $newUser->getEmail()])->getId()]);

        }

    }
}
