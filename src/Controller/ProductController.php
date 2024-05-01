<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{

    private $productRepository;
    private $entityManger;

    function __construct(
        ProductRepository $productRepository,
        ManagerRegistry $doctrine
    ){
        $this->productRepository = $productRepository;
        $this->entityManger = $doctrine->getManager();
    }
    
    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/product', name: 'product_list')]
    public function index(): Response
    {
        $products = $this->productRepository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/store/product', name: 'product_store')]
    public function store(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class,$product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();

                $image->move($this->getParameter('image_directory'),$image_name);

                $product->setImage($image_name);
            }
            $this->entityManger->persist($product);
            $this->entityManger->flush();
            $this->addFlash(
                "success",
                "product saved"
            );
            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/product/details/{id}', name: 'product_show')]
    public function ProductShow(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }


    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/product/edit/{id}', name: 'product_edit')]
    public function ProductEdit(Product $product , Request $request): Response
    {
        $form = $this->createForm(ProductType::class,$product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($request->files->get('product')['image']) {
                $image = $request->files->get('product')['image'];
                $image_name = time().'_'.$image->getClientOriginalName();

                $image->move($this->getParameter('image_directory'),$image_name);

                $product->setImage($image_name);
            }
            $this->entityManger->persist($product);
            $this->entityManger->flush();
            $this->addFlash(
                "success",
                "product Updated"
            );
            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN' ,statusCode: 404 ,message:"page not found")]
    #[Route('/product/delete/{id}', name: 'product_delete')]
    public function ProductDelete(Product $product): Response
    {
        $filesystem = new Filesystem();
        $image_path = "./uploads/".$product->getImage();
        if ($filesystem->exists($image_path)) {
            $filesystem->remove($image_path);
        }
        $this->entityManger->remove($product);
        $this->entityManger->flush();
        $this->addFlash(
            "success",
            "product Deleted"
        );
        
        return $this->redirectToRoute('product_list');

    }

}
