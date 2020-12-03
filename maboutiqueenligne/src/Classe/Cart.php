<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private $session;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function add($id)
    {
        // >We get data from our cart and put it inside $cart
        $cart = $this->session->get('cart', []);

        // If, inside the cart, a specific product ($id) exists
        // We add +1 quantity of this product inside our cart
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        // We set cart details inside the session
        $this->session->set('cart', $cart);
    }

    public function get()
    {
        return $this->session->get('cart');
    }

    public function remove()
    {
        return $this->session->remove('cart');

    }

    public function delete($id)
    {
        $cart = $this->session->get('cart', []);

        // unset will delete the product with the right id inside our cart
        unset($cart[$id]);

        // We set the updated cart in our session and we return it
        return $this->session->set('cart', $cart);
    }

    public function decrease($id)
    {
        // >We get data from our cart and put it inside $cart
        $cart = $this->session->get('cart', []);

        // We check if the quantity inside our cart is greater than 1
        // If yes, we reduce the quantity by one
        // If no, we remove the product from the cart
        if ($cart[$id] > 1 ) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        return $this->session->set('cart', $cart);
    }

    public function getFull()
    {
        // We create a variable ($cartComplete, an empty array) in order to
        // Put informations about the products added to the cart
        $cartComplete = [];

        // Before going inside the foreach, we check if we have products inside our cart
        // Otherwise, we will have an error
        if ($this->get()) {
            //  With this loop, we add the right product(with all its infos)
            // And we add it inside our $cartComplete array
            foreach ($this->get() as $id => $quantity) {
                $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);
                if (!$product_object) {
                    $this->delete($id);
                    // With continue, we skip this product and we continue the loop with every other products
                    // We can ignore id linked to a product that doesn't exist and go through the loop with the other products
                    // Useful if a user add a new product with the route cart/add/{id}. The fake product will not be added inside our cart
                    continue;
                }
                $cartComplete[] = [
                    'product' => $product_object, 
                    'quantity' => $quantity
                ];
            }
        }

        return $cartComplete;
    }
}