<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * This function registers a user and sends an email to activate his account
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param SendMailService $mailService
     * @param JWTService $jwt
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/registration', name: 'app_registration', methods: ['GET','POST'])]
    public function registration(Request $request,EntityManagerInterface $manager,SendMailService $mailService,JWTService $jwt): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setProfilePhoto('user.webp');
        $form = $this->createForm(RegistrationType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $user->setPlainPassword($form->get('plainPassword')->getData());
            $user = $form->getData();
            $manager->persist($user);
            $manager->flush();

            // Generation of the jwt
            // Creation of the header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // Creation of the payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // Generation of the token
            $token = $jwt->generate($header, $payload, $this->getParameter('jwt_secret'));

            // Sends the email
            $mailService->send(
                'no-reply@snowtricks.fr',
                $user->getEmail(),
                'Activation de votre compte sur le site snowtricks',
                'email/emailActivation.html.twig',
                ['user'=>$user,'token'=>$token]

            );

            $this->addFlash('success', 'Votre compte a bien été créé !');
            $this->addFlash('warning','Veuillez le valider en cliquant sur le lien d\'activation que nous vous avons envoyé à l\'adresse '.$user->getEmail());

            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * This function resend an activation link or a modification password link
     *
     * @param JWTService $jwt
     * @param SendMailService $mailService
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/resend_link/{slug}', name: 'app_resend_link', methods: ['GET','POST'])]
    public function sendLink(JWTService $jwt, SendMailService $mailService, UserRepository $userRepository,Request $request):Response
    {
        $slug = $request->get('slug');
        $username = $request->get('user_name');
        $user = $userRepository->findOneBy(['username'=>$username]);
        $template = ($slug == 'activation' ? 'email/emailActivation.html.twig' : 'email/emailResetPassword.html.twig');
        $subject = ($slug == 'activation' ? 'Activation de votre compte sur le site snowtricks' : 'Modification de mot de passe sur le site snowtricks');
        $message = ($slug == 'activation' ? 'Le lien d\'activation à été envoyé avec succès !' : 'Le lien pour accéder à la modification du mot passe à été envoyé avec succès !');

        if (!isset($user)) {
            $this->addFlash('danger', 'L\'utilisateur n\'existe pas');
            return $this->redirectToRoute('app_login');
        }elseif ($slug == 'activation' && $user->getIsEnable()){
            $this->addFlash('warning','Votre compte est déjà activé !');
            return $this->redirectToRoute('app_login');
        }elseif ($slug != 'password' && $slug != 'activation'){
            $this->addFlash('warning','Cette route n\'existe pas !');
            return $this->redirectToRoute('app_login');
        }
        // Generation of the jwt
        // Creation of the header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        // Creation of the payload
        $payload = [
            'user_id' => $user->getId()
        ];
        // Generation of the token
        $token = $jwt->generate($header, $payload, $this->getParameter('jwt_secret'));
        // Sends the email
        $mailService->send(
            'no-reply@snowtricks.fr',
            $user->getEmail(),
            $subject,
            $template,
            ['user'=>$user,'token'=>$token]);
        $this->addFlash('success', $message);
        return $this->redirectToRoute('app_home');
    }


    /**
     *  This function verify the token
     *
     * @param string $token
     * @param JWTService $jwt
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/check/{slug}/{token}', name: 'check_token',methods: ['GET','POST'])]
    public function verifyToken(string $token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $manager, Request $request): Response
    {
        // Checking the token validity
        if (!$jwt->isValid($token) || $jwt->isExpired($token) || !$jwt->check($token, $this->getParameter('jwt_secret'))) {
            $this->addFlash('danger', 'Le token est invalide ou a expiré');
            return $this->redirectToRoute('app_home');
        }

        // Get Slug
        $slug = $request->get('slug');

        // Get payload
        $payload = $jwt->getPayload($token);

        // Get user with the user_id of payload
        $user = $userRepository->find($payload['user_id']);

        if (!isset($user)){
            $this->addFlash('danger', 'L\'utilisateur n\'existe pas !');
            return $this->redirectToRoute('app_home');
        }

        switch ($slug){
            case 'activation':
                $user->setIsEnable(true);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Votre compte est maintenant activé !');
                return $this->redirectToRoute('app_home');
            case 'password':
                return $this->render('user/resetPassword.html.twig', ['token' => $token]);
            case 'newPassword':
                $username = $request->get('_username');
                $password = $request->get('_password');
                return $this->redirectToRoute('app_reset_password', [
                    'username_token' => $user->getUsername(),
                    'username_form' => $username,
                    'password' => $password,
                    'token'=> $token
                ]);
            default:
                $this->addFlash('danger', 'Cette route n\'existe pas !');
                return $this->redirectToRoute('app_home');
        }
    }



    /**
     * This function modify the password
     *
     * @param string $username_token
     * @param string $username_form
     * @param string $password
     * @param string $token
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/reset_password/{username_token}/{username_form}/{password}/{token}',name: 'app_reset_password',methods: ['GET','POST'])]
    public function resetPassword(string $username_token, string $username_form, string $password, string $token,UserRepository $userRepository,EntityManagerInterface $manager):Response
    {
        $passwordIsValid = preg_match('/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?([^\w\s]|_)).{8,}$/',$password);
        if ($username_token != $username_form){
            $this->addFlash('danger', 'Le nom d\'utilisateur est invalide');
        }elseif (!$passwordIsValid) {
            $this->addFlash('danger', 'Votre mot de passe doit comporter au moins huit caractères, dont des lettres majuscules et minuscules, un chiffre et un symbole');
            return $this->render('user/resetPassword.html.twig', ['token' => $token]);
        }else{
            $user = $userRepository->findOneBy(['username'=>$username_token]);
            $user->setPlainPassword($password);
            $user->setUpdatedAt(new \DateTimeImmutable());
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès');
        }
        return $this->redirectToRoute('app_home');
    }


    /**
     * This function connect an user
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/login',name: 'app_login',methods: ['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils):Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if (!empty($error)){
            $error = "Identifiant ou mot de passe invalide !";
        }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }


    /**
     * This function add flash message
     *
     * @return Response
     */
    #[Route('/login_message',name: 'app_login_message',methods: ['GET'])]
    public function loginMessage(): Response
    {
        $this->addFlash('success', 'Bonjour '.$this->getUser()->getUsername().', vous êtes bien connecté !');
        return $this->redirectToRoute('app_home');
    }


    /**
     * This function disconnect a user
     *
     * @return void
     */
    #[Route('/logout',name: 'app_logout',methods: ['GET'])]
    public function logout():void{
        // It's symfony manage disconnection
    }


    /**
     * This function add flash message
     *
     * @return Response
     */
    #[Route('/logout_message',name: 'app_logout_message',methods: ['GET'])]
    public function logoutMessage(): Response
    {
        $this->addFlash('success', 'Vous êtes bien déconnecté !');
        return $this->redirectToRoute('app_home');
    }


}

