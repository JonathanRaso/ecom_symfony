<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/compte/modifier-mot-de-passe", name="account_password")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;

        // First, we get the user already connected with $this->getUser()
        // Then, we create a new form with the form ChangePasswordType, and we pass $user data to the new form
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        // To process form data, you’ll need to call the handleRequest()
        // Behind the scenes, this uses a Symfony\Component\Form\NativeRequestHandler object 
        // To read data off of the correct PHP superglobals (i.e. $_POST or $_GET) based on the HTTP method 
        // Configured on the form (POST is default).
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // We extract the old password sent by the user
            $old_pwd = $form->get('old_password')->getData();
            // And we compare this old password with the one inside our DB
            if ($encoder->isPasswordValid($user, $old_pwd)) {
                $new_pwd = $form->get('new_password')->getData();
                $password = $encoder->encodePassword($user, $new_pwd);

                $user->setPassword($password);
                // We don't need to persist the data with $this->entityManager->persist($user) here
                // Because persist is used when creating soemthing new. For updating, we can
                // use directly flush()
                $this->entityManager->flush();
                $notification = "Votre mot de passe a bien été mis à jour.";
            } else {
                $notification = "Votre mot de passe actuel n'est pas le bon.";
            }
        }

        return $this->render('account/password.html.twig', [
            // Here, we give the form to the view
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
