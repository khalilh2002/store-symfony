<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    private $categoryRepository;
    private $entityManger;
    function __construct(
        CategoryRepository $categoryRepository,
        ManagerRegistry $doctrine
    ){
        $this->categoryRepository = $categoryRepository;
        $this->entityManger = $doctrine->getManager();
    }

    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/category', name: 'category_list')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/store/category', name: 'category_store')]
    public function store(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class,$category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            $this->entityManger->persist($category);
            $this->entityManger->flush();
            $this->addFlash(
                "success",
                "category saved"
            );
            return $this->redirectToRoute('category_list');
        }

        return $this->render('category/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/category/details/{id}', name: 'category_show')]
    public function CategoryShow(Category $category): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $category,
        ]);
    }


    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/category/edit/{id}', name: 'category_edit')]
    public function CategoryEdit(Category $category , Request $request): Response
    {
        $form = $this->createForm(CategoryType::class,$category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            
            $this->entityManger->persist($category);
            $this->entityManger->flush();
            $this->addFlash(
                "success",
                "category Updated"
            );
            return $this->redirectToRoute('category_list');
        }

        return $this->render('category/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/category/delete/{id}', name: 'category_delete')]
    public function CategoryDelete(Category $category): Response
    {
        
        $this->entityManger->remove($category);
        $this->entityManger->flush();
        $this->addFlash(
            "success",
            "category Deleted"
        );
        
        return $this->redirectToRoute('category_list');

    }
}
