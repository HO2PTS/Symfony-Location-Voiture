<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Voitures;
use App\Form\VoituresType;
use App\Form\CommandePostType;
use App\Repository\CommandeRepository;
use App\Repository\VoituresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AgenceController extends AbstractController
{
    #[Route('/', name: 'app_agence')]
    public function index( VoituresRepository $repo): Response
    {
    
        $vehicules = $repo->findAll();

        return $this->render('agence/index.html.twig', [
            'controller_name' => 'AgenceController',
            'vehicules' => $vehicules,
        ]);
    }
    #[Route('/show/{id}', name: "show")]
    public function show($id, VoituresRepository $repo, Request $globals, EntityManagerInterface $manager, Voitures $vehicule)  
    {
        $vehicules = $repo->find($id);

        $commande = new Commande;
        $form = $this->createForm(CommandePostType::class, $commande);

        $form->handleRequest($globals);
        if($form->isSubmitted() && $form->isValid())
        {
            $table = $globals->request->get("commande_post");
            $tableOrigin = $table["date_heure_depart"]['date'];
            $origin = $tableOrigin["year"] . "-" . $tableOrigin["month"] . "-" . $tableOrigin["day"];
            $origin = date_create($origin);
            $tableTarget = $table["date_heure_fin"]['date'];
            $target = $tableTarget["year"] . "-" . $tableTarget["month"] . "-" . $tableTarget["day"];
            $target = date_create($target);
            $commande->setDateEnregistrement(new \DateTime);
            $interval = date_diff($origin, $target);;
            $prix = $vehicule->getPrixJournalier();
            $interval = ($interval->d) + ($interval->m) *30 + ($interval->y) *364 ;
            $prix = $prix * $interval;
            $commande->setPrixTotal($prix);
            $commande->setIdVehicule($vehicule);
            $commande->setIdMembre($this->getUser());
            $manager->persist($commande);
            $manager->flush();
            $this->addFlash("success", "Opération réalisé avec succès"); 

            return $this->redirectToRoute('app_agence', [
                'id' => $commande->getId(),
            ]);
        } 

        // find() permet de récupérer un article en fonction de son id

        return $this->renderForm('agence/show.html.twig', [
            'item' => $vehicules,
            'form' => $form
        ]);
    }
    #[Route('/profil/{id}', name: "app_profil")]
    public function profil(CommandeRepository $repo)
    {
        $commandes = $repo->findAll();
        

     
        return $this->render('agence/profil.html.twig', [
            'commandes'=> $commandes
        ]);
    }
}
