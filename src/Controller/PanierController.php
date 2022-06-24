<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Service\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, Panier $panier): Response
    {
        if($session->get('panier')){
            $panier->verification();
            $panierSession = $session->get('panier');
            $montant = $panier->montant();

            return $this->render('panier/index.html.twig', [
                'panier' => $panierSession,
                'montant' => $montant
    
            ]);
        }else{
            return $this->render('panier/index.html.twig');

        }   
    }




    #[Route('/ajouter', name: 'panier_ajouter')]
    public function panier_ajouter(Request $request, ProduitRepository $repoProduit, Panier $panier)
    {
        //Dans la class Request, se trouve superglobal
        //la propriété request consern $_POST
        //$request->request = $_POST
        //pour accéder à des positions de ce tableau, on utilise la méthode ->get() dans laquelle on y met le nom de la positions

        $idProduit = $request->request->get('produit');
        $quantity = $request->request->get('quantity');

        $produit = $repoProduit->find($idProduit);

        // dd($produit);

        $panier->add($idProduit, $produit->getPrix(), $quantity, $produit->getTitre());

        return $this->redirectToRoute('app_panier');
    }




    #[Route('/vider', name: 'panier_vider')]
    public function panier_vider(Panier $panier)
    {
        $panier->vider();

        return $this->redirectToRoute('app_panier');
    }



    #[Route('/retirer/{id}', name: 'panier_retirer')]
    public function panier_retirer($id, Panier $panier)
    {
        $panier->remove($id);

        return $this->redirectToRoute('app_panier');
    }


    #[Route('/payer', name: 'panier_payer')]
    public function panier_payer(Panier $panier)
    {

        $user = $this->getUser();

        $panier->paiement($user);
        $this->addFlash("success", "Merci, votre cemmande sera traiter dans les plus brefs délais");

        return $this->redirectToRoute('app_panier');
    }
}
