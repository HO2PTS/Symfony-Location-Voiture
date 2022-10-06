<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\MembreType;
use App\Form\RegistrationFormType;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Membre();
        
        $form = $this->createForm(MembreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setDateEnregistrement(new \DateTime); // prout
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/admin/membre/edit/{id}', name:'admin_edit_membre')]
    #[Route('/admin/membres', name: "admin_membres")]
    public function adminMembre(Request $globals, MembreRepository $repo, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $manager, Membre $membre = null)
    {
        $colonnes =$manager->getClassMetadata(Membre::class)->getFieldNames();

        
        // dd($colonnes);
        $membres = $repo->findAll();
        

        if($membre ==null):
            $membre = new Membre;
        endif;


        $form= $this->createForm(RegistrationFormType::class, $membre);

        $form->handleRequest($globals);


        if($form->isSubmitted() && $form->isValid())
        {
            $membre->setDateEnregistrement(new \DateTime);
            $membre->setPassword(
                $userPasswordHasher->hashPassword(
                    $membre,
                    $form->get('plainPassword')->getData()
                )
            );
            $manager->persist($membre);
            $manager->flush();
            $this->addFlash('success', "Votre compte a bien été créé");

            return $this->redirectToRoute('admin_membres');
        }

        return $this->renderForm("admin/admin_membre.html.twig", [
            "formMembre" => $form,
            "editMode" => $membre->getId() !== null,
            'membres' => $membres,
            'colonnes' => $colonnes
        ]);

    }
    #[Route("/admin/membre/delete/{id}", name:"admin_delete_membre")]

    public function deleteArticle(Membre $membre, EntityManagerInterface $manager)
    {
        $manager->remove($membre);
        $manager->flush();
        $this->addFlash('success', "L'utilisateur a bien été supprimé'");
        return $this->redirectToRoute('admin_membres');
    }
    #[Route('/admin/membre/show/{id}', name: 'admin_show_member')]
    public function show($id, MembreRepository $repo, Request $globals, EntityManagerInterface $manager)  //$id correspond au {id} (paramètres de route) dans l'URL 
    {
        $membres = $repo->find($id);

        return $this->renderForm('admin/admin_show_member.html.twig', [
            'member' => $membres
        ]);

    }
}
