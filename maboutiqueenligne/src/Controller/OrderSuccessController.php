<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // If isPaid status of the order is 0 (not paid), we need to change it for 1 (paid)
        if (!$order->getIsPaid()) {
            // Remove cart's session
            $cart->remove();
            // Modify isPaid status from 0 to 1
            $order->setIsPaid(1);
            $this->entityManager->flush();
            // Send email to customer
        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}
