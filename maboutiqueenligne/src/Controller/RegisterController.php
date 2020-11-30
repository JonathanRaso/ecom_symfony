<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /**
     * @Route("/inscription", name="register")
     */
    public function index(): Response
    {
        // Create a new instance of User class, then create a new form linked to this user
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
