<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    private $orderRepository;
    private $entityManger;

    function __construct(
        OrderRepository $orderRepository,
        ManagerRegistry $doctrine
    ){
        $this->orderRepository = $orderRepository;
        $this->entityManger = $doctrine->getManager();
    }
    
    #[Route('/orders', name: 'orders_list')]
    public function index(): Response
    {
        $orders = $this->orderRepository->findAll();
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/user/orders', name: 'user_order_list')]
    public function userOrders(): Response
    {
        if (! $this->getUser()) {
            return $this->redirectToRoute('app_login');

        }
        return $this->render('order/user.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/store/order/{product}', name: 'order_store')]
    public function store(Product $product): Response
    {
        if (! $this->getUser()) {
            return $this->redirectToRoute('app_login');

        }
        $orderExist = $this->orderRepository->findBy([
            "user"=>$this->getUser(),
            "pname"=>$product->getName(),
        ]);

        if ($orderExist) {
            $this->addFlash(
                "warning",
                "you already have an order of this product",
            );
            return $this->redirectToRoute('user_order_list');

        }

        $order = new Order();
        $order->setPname($product->getName());
        $order->setPrice($product->getPrice());
        $order->setStatus('Processing..');
        $order->setUser($this->getUser());

        
        

        $this->entityManger->persist($order);
        $this->entityManger->flush();
        $this->addFlash(
            "success",
            "order saved"
        );
        return $this->redirectToRoute('user_order_list');
    

       
    }
    #[Route('/update/{order}/{status}', name: 'order_status_update')]
    public function updateOrderStatus(Order $order , $status): Response
    {
        $order->setStatus($status);
        $this->entityManger->persist($order);
        $this->entityManger->flush();
        $this->addFlash(
            "success",
            "order updated"
        );
        return $this->redirectToRoute('orders_list');

    }

    #[Route('/delete/{order}', name: 'order_delete')]
    public function deleteOrderStatus(Order $order ): Response
    {
        $this->entityManger->remove($order);
        $this->entityManger->flush();
        $this->addFlash(
            "success",
            "order was deleted"
        );
        return $this->redirectToRoute('user_order_list');

    }

    #[Route('/admin/delete/{order}', name: 'admin_order_delete')]
    public function adminDeleteOrderStatus(Order $order ): Response
    {
        $this->entityManger->remove($order);
        $this->entityManger->flush();
        $this->addFlash(
            "success",
            "order was deleted"
        );
        return $this->redirectToRoute('orders_list');

    }

}
