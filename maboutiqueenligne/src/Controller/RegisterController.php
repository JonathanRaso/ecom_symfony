<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    // The entity manager will be accessible to the entire class and you will be able to run queries, 
    // Create and persist entities. WIth this, we don't need to get doctrine in every function of the class
    // Because we get it everywhere in the class now
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;

    }

    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request): Response
    {
        // Create a new instance of User class, then create a new form linked to this user
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        // Our form will be able to 'listen' the request, and get user infos from this request
        $form->handleRequest($request);

        // We check if the form is submitted AND if it is valid (using TextType, EmailType, PasswordType for that)
        if ($form->isSubmitted() && $form->isValid()) {
            // If yes, we get user infos from our form and add it to our user object 
            $user = $form->getData();

            // 1) We need doctrine to add this new user into our DB (done with $entityManager)
            // 2) Freeze the data
            $this->entityManager->persist($user);
            // 3) Get the freeze data and save it into database
            $this->entityManager->flush();
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
