<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit')]
    public function index(ProduitRepository $repoProduct): Response
    {
        $produits = $repoProduct->findAll();
        return $this->render('produit/index.html.twig', [
            'produits' => $produits
        ]);
    }

    

    #[Route('/fiche_produit/{id}', name: 'fiche_produit')]
    public function show(Produit $produit): Response
    {

        return $this->render('produit/fiche_produit.html.twig', [
            'produit' => $produit
        ]);
    }

}
