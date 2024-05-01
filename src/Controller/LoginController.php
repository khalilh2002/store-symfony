<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginController extends AbstractController
{
    private $entityManager;
    private $twilioService;
    private $session;

    public function __construct(EntityManagerInterface $entityManager, TwilioService $twilioService)
    {
        $this->entityManager = $entityManager;
        $this->twilioService = $twilioService;
    }
    
    #[Route('/login', name: 'app_login')]
    public function index( Request $request,EntityManagerInterface $entityManager, SessionInterface $session ,UserPasswordHasherInterface $userPasswordHasher , TokenStorageInterface $tokenStorage): Response
    {
        $form = $this->createForm(LoginType::class ); // Create the form and bind it to the User object
        $form->handleRequest($request); // Handle the form submission


        
        if ($form->isSubmitted() && $form->isValid()) {
            echo"
                <script>
                    window.alert('your in')
                </script>
            ";
            
            $formData = $form->getData();
            $repository = $entityManager->getRepository(User::class);
            //var_dump($formData);
            
            
            $login = $repository->findOneBy([
                'username' => $formData['username'],
            ]);
            
            if ($login !== null && $userPasswordHasher->isPasswordValid($login , $formData['password'])) {
                if($login->GetVerifiyStatus()=="Pending"){
                    $WhatsappNo = $login->getPhoneNumber();
                    $otp = sprintf('%06d', mt_rand(0, 999999));
                    $this->twilioService->sendWhatsappOTP("$WhatsappNo", "$otp");
                    $session->set('username', $formData['username']);
                    $session->set('otp', $otp);
                    return $this->redirectToRoute('verify');
                } else {
                    $user = $login; // Retrieve authenticated user from database or other source
                    $token = new UsernamePasswordToken($user, 'main', $login->getRoles());
                    $tokenStorage->setToken($token);
                    $msg= "Your account has been verified, and you will be redirected to your dashboard page.";
                    
                    
                    return $this->redirectToRoute('home');
                }
            }  else {
                $msg= "Incorrect Username/Password";
            }
            var_dump($msg);
        }
        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'form'=> $form
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout( SessionInterface $session ): Response
    {
        // Invalidate the user's session
        $session->invalidate();

        // Redirect the user to the homepage or any other desired page
        return $this->redirectToRoute('home');
    }
}
