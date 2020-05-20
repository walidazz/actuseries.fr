<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, EntityManagerInterface $em,  UserPasswordEncoderInterface $encoder, MailerService $mailerService)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $user->setTokenConfirmation($this->generateToken());
            $em->persist($user);

            $em->flush();
            $token = $user->getTokenConfirmation();
            $username = $user->getUsername();
            $email = $user->getEmail();

            $mailerService->sendToken($token, $email, $username, 'validateAccount.html.twig');

            $this->addFlash('success', 'vous allez recevoir un email de confirmation pour activer votre compte et pouvoir vous connecté');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/confirmAccount/{token}/{username}", name="confirmAccount")
     */
    public function confirmAccount($token, $username): Response
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => $username]);
        if ($token === $user->getTokenConfirmation()) {
            $user->setTokenConfirmation(null);
            $user->setEnable(true);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte est désormais actif ! ');
            return $this->redirectToRoute('app_login');
        } else {
            return $this->render('register/token-expire.html.twig');
        }
    }



    /**
     * Permet de genener un token
     *@return string
     */
    private function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }





    /**
     * @Route("/forgottenPassword", name="forgottenPassword")
     */
    public function forgottenPassword(MailerService $mailerService, Request $request, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class)
            ->add('save', SubmitType::class, ['label' => 'Reinitialiser le mot de passe '])
            ->getForm();
        $form->handleRequest($request);
        $token = $this->generateToken();
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setTokenConfirmation($this->generateToken());
            $em->persist($user);
            $em->flush();
            $mailerService->sendToken($token, $user->getEmail(), $user->getPseudo(), 'resetPassword.html.twig');
            $this->addFlash("success", "un mail vient d'être envoyé sur votre adresse mail pour changer votre mot de passe");
            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/forgottenPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/changePassword/{username}/{token}", name="changePassword")
     */
    public function changePassword($username, $token, Request $request, EntityManagerInterface $em)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['email' => $username]);
        if ($token === $user->getTokenConfirmation()) {
            $form = $this->createFormBuilder($user)
                ->add('password', PasswordType::class,  ['attr' =>  ['placeholder' => 'Entrez un mot de passe', 'required' => true]])
                ->add('passwordConfirm', PasswordType::class,  ['attr' =>  ['placeholder' => 'Confirmez votre mot de passe', 'required' => true]])
                ->add('save', SubmitType::class, ['label' => 'Reinitialiser le mot de passe '])
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($user);
                $em->flush();
                $this->addFlash("success", "Le mot de passe a été changé avec succès !");
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render('user/changePassword.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
