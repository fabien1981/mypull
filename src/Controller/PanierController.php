<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, ProductsRepository $product): Response
    {
        $panier = $session->get('panier', []);

        $data = [];
        $total = 0;

        foreach($panier as $key =>$quantity){
            $produit = $product->find($key);
            $data[] = [
                "product"=>$produit,
                "quantity"=>$quantity
            ];
            $total += $produit->getPrice() * $quantity;
        }

        return $this->render('panier/index.html.twig', [
            'data' => $data,
            'total' => $total,
        ]);
    }

    #[Route('/panier/add{id}', name: 'app_addPanier')]
    public function add($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier',[]);
        if(empty($panier[$id])){
            $panier[$id] = 1;
        }else{
            $panier[$id]++;    
        }
        $session->set('panier',$panier);
        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier/supp{id}', name: 'app_suppPanier')]
    public function supp($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier',[]);

        if(!empty($panier[$id])) {
            if ($panier[$id]>1){
            $panier[$id]--;
        }else{
           unset($panier[$id]);    
        }
    }
        $session->set('panier',$panier);
        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier/vider{id}', name: 'app_viderPanier')]
    public function vider($id, SessionInterface $session): Response
    {
        $panier = $session->get('panier',[]);

        if(!empty($panier[$id])) {
     
           unset($panier[$id]);    
        
    }
        $session->set('panier',$panier);
        return $this->redirectToRoute("app_panier");
    }

    #[Route('/panier/trash', name: 'app_trashPanier')]
    public function trash(SessionInterface $session): Response
    {
        $session->remove('panier');
        return $this->redirectToRoute("app_panier");
    }

}
