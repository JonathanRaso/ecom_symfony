<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    // For Stripe docs used here --> https://stripe.com/docs/checkout/integration-builder

    /**
     * @Route("/commande/create-session/{reference}", name="stripe_create_session")
     */
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {
        // Create an array for later. We will give it to Stripe
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://localhost:8741';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }

        //dd($order->getOrderDetails()->getValues());

        foreach ($order->getOrderDetails()->getValues() as $product) {
            // Put every product of the foreach loop inside this array
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                      'name' => $product->getProduct(),
                      'images' => [$YOUR_DOMAIN."/uploads/".$product_object->getIllustration()],
                    ],
                  ],
                  'quantity' => $product->getQuantity(),
            ];
        }

        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                  'name' => $order->getCarrierName(),
                  'images' => [$YOUR_DOMAIN],
                ],
              ],
              'quantity' => 1,
        ];

        // Initialize Stripe
        Stripe::setApiKey('sk_test_51HvkcHA2BEM55rFzSgXuDJ2SCkx4eVeNU1hWLLmclWNhWAINvavw8V7JVhbhJrOrSw4fb3xitSjMvYa6xDvxGnzp00g3agMi6F');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $products_for_stripe
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        // Save checkout session id from stripe inside this order
        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();

        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
