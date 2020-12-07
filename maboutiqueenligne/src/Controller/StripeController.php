<?php

namespace App\Controller;

use App\Classe\Cart;
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
     * @Route("/commande/create-session", name="stripe_create_session")
     */
    public function index(Cart $cart): Response
    {
        // Create an array for later. We will give it to Stripe
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://localhost:8741';

        foreach ($cart->getFull() as $product) {
            // Put every product of the foreach loop inside this array
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product['product']->getPrice(),
                    'product_data' => [
                      'name' => $product['product']->getName(),
                      'images' => [$YOUR_DOMAIN."/uploads/".$product['product']->getIllustration()],
                    ],
                  ],
                  'quantity' => $product['quantity'],
            ];
        }

        // Initialize Stripe
        Stripe::setApiKey('sk_test_51HvkcHA2BEM55rFzSgXuDJ2SCkx4eVeNU1hWLLmclWNhWAINvavw8V7JVhbhJrOrSw4fb3xitSjMvYa6xDvxGnzp00g3agMi6F');

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                $products_for_stripe
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/success.html',
            'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
        ]);

        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
