<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
class UserController extends AbstractController
{
    private $userRepository;

    public function __construct(
        UserRepository $userRepository_
    ){
        $this->userRepository = $userRepository_;
    }


    #[Route('/admin/user', name: 'users_list')]
    public function index(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
}
