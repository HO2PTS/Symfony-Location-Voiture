<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Commande;
use App\Entity\Voitures;
use App\Form\MembreType;
use App\Form\VoituresType;
use App\Form\CommandeAdminType;
use App\Form\RegistrationFormType;
use App\Repository\MembreRepository;
use App\Repository\CommandeRepository;
use App\Repository\VoituresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    
    #[Route('/admin/vehicule/edit/{id}', name:'admin_edit_vehicule')]
    #[Route('/admin/vehicules', name: "admin_vehicules")]
    public function adminVehicule(Request $globals, VoituresRepository $repo, EntityManagerInterface $manager, Voitures $vehicule = null)
    {
        $colonnes =$manager->getClassMetadata(Voitures::class)->getFieldNames();

        
        $vehicules = $repo->findAll();
        

        if($vehicule ==null):
            $vehicule = new Voitures;
        endif;


        $form = $this->createForm(VoituresType::class, $vehicule );

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $vehicule->setDateEnregistrement(new \DateTime);
            $manager->persist($vehicule);
            $manager->flush();
            $this->addFlash('success', "Le véhicule a bien été enregistré !");
            
            return $this->redirectToRoute('admin_vehicules');
        }

        return $this->renderForm("admin/admin_voitures.html.twig", [
            "formVoitures" => $form,
            "editMode" => $vehicule->getId() !== null,
            'vehicules' => $vehicules,
            'colonnes' => $colonnes
        ]);

    }

    #[Route('/admin/vehicule/delete/{id}', name:'admin_delete_voitures')]

    public function deleteVoiture(Voitures $vehicule, EntityManagerInterface $manager)
    {

        $manager->remove($vehicule);
        $manager->flush();
        $this->addFlash('success', "Le véhicule a bien été supprimé");
        return $this->redirectToRoute('admin_vehicule') ;
    }

    
    #[Route('/admin/commande/edit/{id}', name:'admin_edit_commande')]
    #[Route('/admin/commande', name: 'admin_commandes')]
    public function adminCommandes( Request $globals, CommandeRepository $repo, EntityManagerInterface $manager, MembreRepository $mrepo ,VoituresRepository $vrepo, Commande $commande = null)
    {
        $colonnes =$manager->getClassMetadata(Commande::class)->getFieldNames();

        
        $commandes = $repo->findAll();
        $membres = $mrepo->findAll();
        $vehicules = $vrepo->findAll();

        if($commande == null) {

            $commande = new Commande;
            

        }
        $form = $this->createForm(CommandeAdminType::class, $commande );

        $form->handleRequest($globals);



        if($form->isSubmitted() && $form->isValid())
        {
            $commande->setDateEnregistrement(new \DateTime);
            $manager->persist($commande);
            $manager->flush();
            $this->addFlash('success', "Le véhicule a bien été enregistré !");
            
            return $this->redirectToRoute('admin_commandes');
        }

        return $this->renderForm("admin/admin_commandes.html.twig", [
            "form" => $form,
            "editMode" => $commande->getId() !== null,
            'vehicules' => $vehicules,
            'commandes' => $commandes,
            'membres' => $membres,
            'colonnes' => $colonnes
        ]);
    }
    #[Route('/admin/vehicule/show/{id}', name: 'admin_show_vehicule')]
    public function showVehicule($id, VoituresRepository $repo, Request $globals, EntityManagerInterface $manager)  //$id correspond au {id} (paramètres de route) dans l'URL 
    {
        $vehicules = $repo->find($id);

        return $this->renderForm('admin/admin_show_vehicule.html.twig', [
            'vehicules' => $vehicules
        ]);

    }

    #[Route('/admin/show/{id}', name: 'admin_show')]
    public function show($id, CommandeRepository $repo, Request $globals, EntityManagerInterface $manager)  //$id correspond au {id} (paramètres de route) dans l'URL 
    {
        $commande = $repo->find($id);

        return $this->renderForm('admin/admin_show.html.twig', [
            'item' => $commande
        ]);

    }
}