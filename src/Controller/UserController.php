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
     */
    #[Route('/registration', name: 'app_registration', methods: ['GET','POST'])]
    public function registration(Request $request,EntityManagerInterface $manager,SendMailService $mailService,JWTService $jwt): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(RegistrationType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
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
     *  This function verify the token
     *
     * @param $token
     * @param JWTService $jwt
     * @param UserRepository $usersRepository
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/check/{token}', name: 'check_token',methods: ['GET','POST'])]
    public function verifyToken($token, JWTService $jwt, UserRepository $usersRepository, EntityManagerInterface $manager): Response
    {
        // Checking the token validity
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('jwt_secret'))){
            // Get payload
            $payload = $jwt->getPayload($token);

            // Get user with the user_id of payload
            $user = $usersRepository->find($payload['user_id']);

            // Checks if user exists and if his account isn't enabled
            if($user && !$user->getIsEnable()){
                $user->setIsEnable(true);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Votre compte est maintenant activé !');
                return $this->redirectToRoute('app_home');
            }
        }
        // Ici un problème se pose dans le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_home');
    }

}

