<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\DetailsCommande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class Panier
{

    private $requestStack;
    //private $session;
    private $repoProduit;
    private $manager;


    public function __construct(RequestStack $requestStack,  ProduitRepository $repoProduit, EntityManagerInterface $manager)
    {
        $this->requestStack = $requestStack;
       // $this->session = $session;
        $this->repoProduit = $repoProduit;
        $this->manager = $manager;
    }

    public function creation()
    {
        $panier = [
            'id' => [],
            'prix' => [],
            'quantity' => [],
            'titre' => []
        ];
        return $panier;
    }

    public function add($id, $prix, $quantity, $titre)
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier');

        if (empty($panier)) {
            $panier = $this->creation();
            $session->set('panier', $panier);
        }

        /*
        fonction prédéfinie PHP
        array_search()
        -> permet de rechercher une valeur dans un tableau
        =>retourner la POSITION dans le tableau

        2arguements :
            - valeur recherché
            - tableau
        */
        $position_search = array_search($id, $panier['id']);

        if ($position_search !== false) {
            $panier['quantity'][$position_search] += $quantity;
        } else {
            $panier['id'][] = $id;
            $panier['prix'][] = $prix;
            $panier['quantity'][] = $quantity;
            $panier['titre'][] = $titre;
        }

        $session->set('panier', $panier);

        //dd($panier);

    }



    public function vider()
    {
        $session = $this->requestStack->getSession();
        //$this->session->remove('panier'); //panier de la session redeviendra null
        $panier = $this->creation();
        $session->set('panier', $panier);
    }



    public function remove($id)
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier');

        $position = array_search($id, $panier['id']);

        /*
            array_splice permett d'éffacer une portion (un ou des élemants) dans un tableau

            3 argumaents :
                1 - tableau
                2 - la position
                3 - le npmbre d'élemant à supprimer
        */

        array_splice($panier['id'], $position, 1);
        array_splice($panier['titre'], $position, 1);
        array_splice($panier['prix'], $position, 1);
        array_splice($panier['quantity'], $position, 1);

        // deuxiem manier de faire
        /* $tableau = ["id" , "titre" , "prix" , "quantity"];

        for($i = 0; $i < count($tableau); $i++){
            array_splice($panier[$tableau[$i]], $position, 1);
        } */

        $session->set('panier', $panier);
    }


    public function montant()
    {
        $session = $this->requestStack->getSession();
        $montant = 0;

        $panier = $session->get('panier');

        for ($i = 0; $i < count($panier['id']); $i++) {
            $montant += $panier['prix'][$i] * $panier['quantity'][$i];
        }

        $montant = round($montant, 2);

        return $montant;
    }




    public function verification()
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier');

        $size = count($panier['id']);

        for ($i = 0; $i < $size; $i++) {
            $produit = $this->repoProduit->find($panier['id'][$i]);

            if($produit->getStock()){
                if($produit->getStock() < $panier['quantity'][$i]){
                    $panier['quantity'][$i] = $produit->getStock();
                }
            }
            else
            {
                $panier['quantity'][$i] = 0;
            }
        }

        $session->set('panier', $panier);
    }




    public function paiement($user)
    {
        $session = $this->requestStack->getSession();
        $this->verification();

        $panier = $session->get('panier');

        $size = count($panier['id']);

        $acces = false;

        for ($i = 0; $i < $size; $i++) {
            if ($panier['quantity'][$i]) {
                $acces = true;
            }
        }

        if ($acces) {
            $commande = new Commande();
            $commande->setUser($user);
            $commande->setMontant($this->montant());
            $commande->setEnregistrementAt(new \DateTimeImmutable('now'));
            $commande->setEtat(0);

            $this->manager->persist($commande);
            $this->manager->flush();

            for ($i = 0; $i < $size; $i++) {
                if ($panier['quantity'][$i]) {
                    $produit = $this->repoProduit->find($panier['id'][$i]);
                    $detail = new DetailsCommande;
                    $detail->setCommande($commande);
                    $detail->setProduit($produit);
                    $detail->setQuantity($panier['quantity'][$i]);
                    $detail->setPrix($panier['prix'][$i]);

                    $this->manager->persist($detail);
                    $this->manager->flush();

                    $stockBdd = $produit->getStock();

                    $newStock = $stockBdd - $panier['quantity'][$i];

                    $produit->setStock($newStock);

                    $this->manager->persist($produit);
                    $this->manager->flush();

                    $this->remove($panier['id'][$i]);

                }
            }
        }
    }
}
