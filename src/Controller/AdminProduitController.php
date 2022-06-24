<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/produit')]
class AdminProduitController extends AbstractController
{
    #[Route('/', name: 'app_admin_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('admin_produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $produit->setEnregistrementAt(new \DateTimeImmutable('now'));


             // Upload image
             $imageFile = $form->get('image')->getData();
             if($imageFile){
                 
                 // 1 - Création d'un nom unique pour image
                 $nomImage = date("YmdHis"). "-" . uniqid() . "." . $imageFile->getClientOriginalExtension();  
 
                 // 2 - Déplacer le fichier image dans le dossier public
                 $imageFile->move(
                     $this->getParameter('imageProduit'),
                     $nomImage
                 );
 
                 // 3 - Enregistrer dans l'objet $produit à la propriété image du fichier
                 $produit->setImage($nomImage);
             }
 
            $produitRepository->add($produit, true);

            return $this->redirectToRoute('app_admin_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('admin_produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $produit->setEnregistrementAt(new \DateTimeImmutable('now'));


             // Upload image
             $imageFile = $form->get('image')->getData();
             if($imageFile){
                 
                 // 1 - Création d'un nom unique pour image
                 $nomImage = date("YmdHis"). "-" . uniqid() . "." . $imageFile->getClientOriginalExtension();  
 
                 // 2 - Déplacer le fichier image dans le dossier public
                 $imageFile->move(
                     $this->getParameter('imageProduit'),
                     $nomImage
                 );
 
                 // 3 - Enregistrer dans l'objet $produit à la propriété image du fichier
                 $produit->setImage($nomImage);
             }
 

            $produitRepository->add($produit, true);

            return $this->redirectToRoute('app_admin_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin_produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_admin_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
