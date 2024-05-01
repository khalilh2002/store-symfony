<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private $productRepository , $categoryRepository;
    private $entityManger;

    function __construct(
        ProductRepository $productRepository_,
        ManagerRegistry $doctrine_,
        CategoryRepository $categoryRepository_
    ){
        $this->productRepository = $productRepository_;
        $this->entityManger = $doctrine_->getManager();
        $this->categoryRepository = $categoryRepository_;
    }

    #[Route('/', name: 'home')]
    public function index( ): Response
    {
        $product = $this->productRepository->findOneBy([], ['id' => 'DESC']);
        $products = $this->productRepository->findAll();

        $categories = $this->categoryRepository->findAll();

        return $this->render('home/index.html.twig', [
            'items' => $product,
            'products'=>$products,
            'categories'=>$categories,
        ]);
    }
    #[Route('/product/{category}', name: 'product_category')]
    public function ProductShow(Category $category): Response
    {
        $product = $this->productRepository->findOneBy([], ['id' => 'DESC']);

        $categories = $this->categoryRepository->findAll();

        return $this->render('home/index.html.twig', [
            'items' => $product,
            'products'=>$category->getProducts(),
            'categories'=>$categories,
        ]);
    }
}
