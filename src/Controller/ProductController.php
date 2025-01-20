<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    
    #[Route('/product', name: 'app_product')]
    public function index(Request $request, EntityManagerInterface $entity): Response
    {
        $product = new Products();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entity->persist($product);
            $entity->flush();
            $this->addFlash("message", "Produit ajouté avec succés");
            return $this->redirectToRoute("app_product");
        }
        return $this->render('product/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
