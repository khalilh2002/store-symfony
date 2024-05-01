<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Service\TwilioService;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $twilioService;

    public function __construct(EntityManagerInterface $entityManager, TwilioService $twilioService)
    {
        $this->entityManager = $entityManager;
        $this->twilioService = $twilioService;
    }
    
    #[Route('/register', name: 'app_register')]
    public function register(Request $request,SessionInterface $session, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
                $otp = sprintf('%06d', mt_rand(0, 999999));
                $this->twilioService->sendWhatsappOTP($user->getPhoneNumber(), $otp);
                $session->set('username', $user->getUsername());
                $session->set('otp', $otp);
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $user->setVerifyStatus('Pending');
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute('verify');
            //}
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }






    #[Route('/verify', name: 'verify')]
    public function verify(Request $request, SessionInterface $session): Response
    {
        $msg = "";
        if ($session->get('otp') !== null && $session->get('username') !== null) {
            $otpFromForm = $request->request->get('otp');
            $sessionOtp = $session->get('otp');
            if (empty($otpFromForm)) {
                $msg = "";
            } else {
                if ($otpFromForm == $sessionOtp) {
                    $sessionUsername = $session->get('username');
                    $userRepository = $this->entityManager->getRepository(User::class);
                    $user = $userRepository->findOneBy(['username' => $sessionUsername]);
                    if ($user) {
                        $user->setVerifyStatus('Verified');
                        $this->entityManager->persist($user);
                        $this->entityManager->flush();
                        $msg = 'Account verified successfully.';
                        return $this->redirectToRoute('home');
                    } else {
                        return $this->redirectToRoute('login');
                    }
                } else {
                    $msg = 'Verification code is incorrect.';
                }
            }
        } else {
            return $this->redirectToRoute('login');
        }    
        return $this->render('registration/verify.html.twig', ['message' => $msg]);
    }



    // #[Route('/login', name: 'app_login')]
    // public function login(Request $request,EntityManagerInterface $entityManager, SessionInterface $session): Response
    // {
       
    //     $msg= "";
    //     $user = new User;
    //     $form = $this->createForm(LoginType::class,$user);
    //     $form->handleRequest($request);
       
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         echo"
    //             <script>
    //                 window.alert('your in')
    //             </script>
    //         ";
            
    //         $formData = $form->getData();
    //         $repository = $entityManager->getRepository(User::class);
    //         $login = $repository->findOneBy([
    //             'username' => $formData['username'],
    //             'password' => $formData['password'],
    //         ]);
    //         if ($login !== null) {
    //             if($login->GetVerifiyStatus()=="Pending"){
    //                 $WhatsappNo = $login->getPhoneNumber();
    //                 $otp = sprintf('%06d', mt_rand(0, 999999));
    //                 $this->twilioService->sendWhatsappOTP("+212689666089", "5555");
    //                 $session->set('username', $formData['username']);
    //                 $session->set('otp', $otp);
    //                 return $this->redirectToRoute('verify');
    //             } else {
    //                 $msg= "Your account has been verified, and you will be redirected to your dashboard page.";
    //                 return $this->redirectToRoute('home');
    //             }
    //         }  else {
    //             $msg= "Incorrect Username/Password";
    //         }
    //     }
    //     return $this->render('login/index.html.twig', [
    //         'form' => $form,'msg' => $msg,
    //     ]);
    // }


}
