<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Services\FileUpload;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\CustomLoginAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    private $userPasswordHasher;
    private $userAuthenticator;
    private $authenticator;
    private $fileUpload;
    private $em;
    private $security;
    
    /**
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param UserAuthenticatorInterface $userAuthenticator
     * @param CustomLoginAuthenticator $authenticator
     * @param FileUpload $fileUpload
     * @param EntityManagerInterface $em
     */
    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        CustomLoginAuthenticator $authenticator,
        FileUpload $fileUpload,
        EntityManagerInterface $em,
        Security $security)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userAuthenticator = $userAuthenticator;
        $this->authenticator = $authenticator;
        $this->fileUpload = $fileUpload;
        $this->em = $em;
        $this->security = $security;
    }


    /**
     * @Route("/admin-register", name="admin_register")
     */
    public function registerAdmin(Request $request){
        $admin = new User();
        $registerForm = $this->createForm(RegisterType::class, $admin);
        $registerForm->handleRequest($request);
        
        if($registerForm->isSubmitted() && $registerForm->isValid()){
            //hash password
            $admin->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $admin,
                    $registerForm->get('password')->getData()
                )
            );
            //if admin add avatar set the img
            $avatar = $registerForm->get('avatar')->getData();
            if($avatar) {
                $this->fileUpload->tryUpload($avatar, $admin);
            }
            //set Admin Role
            $admin->setRoles(["admin", "user"]);
            //save admin data
            $this->em->persist($admin);
            $this->em->flush();

            //authenticate and redirect to home page
            return $this->userAuthenticator->authenticateUser(
                $admin,
                $this->authenticator,
                $request
            );
        }
        // Redirect to home page if user is logged-in
        if($this->security->isGranted(('IS_AUTHENTICATED_FULLY'))){
            return $this->redirect($this->generateUrl('app_post'));
        }
        //render view
        return $this->render('registration/index.html.twig', [
            "registerForm" => $registerForm->createView()
        ]);
    }









    /**
     * @Route("/register", name="register")
     */
    public function registerUser( Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, CustomLoginAuthenticator $authenticator, FileUpload $fileUpload, EntityManagerInterface $em ): Response
    {
        $user = new User();

        $registerForm = $this->createForm(RegisterType::class, $user);

        $registerForm->handleRequest($request);
        // dd($registerForm->isValid());
        if($registerForm->isSubmitted() && $registerForm->isValid()){
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $registerForm->get('password')->getData()
                    )
            );
            $file = $registerForm->get('avatar')->getData();
            if($file) {
                $fileUpload->tryUpload($file, $user);
            }
            $user->setRoles(['user']);
            $em->persist($user);
            $em->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
        // Redirect to home page if user is logged-in        
        if($this->security->isGranted(('IS_AUTHENTICATED_FULLY'))){
            return $this->redirect($this->generateUrl('app_post'));
        }
        return $this->render('registration/index.html.twig', [
            'registerForm' => $registerForm->createView()
        ]);
    }
}
