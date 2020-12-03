<?php

namespace App\Controller;

use App\Entity\Product;
use App\Classe\Search;
use App\Form\SearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/nos-produits", name="products")
     */
    public function index(Request $request): Response
    {
        // Create a Search instance, and then we create a form with this new instance (Search) and the SearchType
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // We create this method (findWithSearch()) for fetching products according to the user's research
            // A repository let us fetch data from our DB and custom our sql requests if needed.
            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search);
        } else {
            // Here, we get our product repository with entityManager method 'getRepository(name_of_the_repository)'
            // And we get all products with findAll();
            $products = $this->entityManager->getRepository(Product::class)->findAll();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        // Here, we get our product repository with entityManager method 'getRepository(name_of_the_repository)'
        // And we get the right product with the slug and the method findOneBySlug($slug);
        $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug);

        if (!$product) {
            return $this->redirectToRoute('products');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }
}
