<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
use App\Form\CategoriesType;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class ProductController extends AbstractController
{
    #[Route('/product/add', name: 'app_productadd')]
    public function ajout(Request $request, EntityManagerInterface $entity): Response
    {
        $slugger = new AsciiSlugger();
        $product = new Products();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du slug
            $product->setSlug($slugger->slug($form->get('name')->getData()));

            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Répertoire défini dans les paramètres
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                }
            }

            $entity->persist($product);
            $entity->flush();

            $this->addFlash("success", "Produit ajouté avec succès !");
            return $this->redirectToRoute("app_productadd");
        }

        // Formulaire pour catégories
        $category = new Categories();
        $formCategorie = $this->createForm(CategoriesType::class, $category);
        $formCategorie->handleRequest($request);

        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {
            $category->setSlug($slugger->slug($formCategorie->get('name')->getData()));
            $entity->persist($category);
            $entity->flush();
            $this->addFlash("success", "Catégorie ajoutée avec succès !");
            return $this->redirectToRoute('app_productadd');
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
            'formCategorie' => $formCategorie->createView(),
        ]);
    }

    #[Route('/product/modif/{id}', name: 'app_productmodif')]
    public function modif($id, Request $request, EntityManagerInterface $entity): Response
    {
        $slugger = new AsciiSlugger();
    
        // Récupération du produit à modifier
        $product = $entity->getRepository(Products::class)->find($id);
    
        // Vérifie si le produit existe
        if (!$product) {
            $this->addFlash("error", "Produit introuvable");
            return $this->redirectToRoute("app_productadd");
        }
    
        // Création du formulaire
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du slug
            $product->setSlug($slugger->slug($form->get('name')->getData()));
    
            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Répertoire défini dans les paramètres
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', "Une erreur est survenue lors de l'upload de l'image.");
                }
            }
    
            // Persistance des modifications
            $entity->persist($product);
            $entity->flush();
    
            $this->addFlash("message", "Produit modifié avec succès");
            return $this->redirectToRoute("app_productadd");
        }
    
        /**
         * Formulaire pour catégories
         */
        $category = $entity->getRepository(Categories::class)->find($id);
        $formCategorie = $this->createForm(CategoriesType::class, $category);
        $formCategorie->handleRequest($request);
    
        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {
            $category->setSlug($slugger->slug($formCategorie->get('name')->getData()));
            $entity->persist($category);
            $entity->flush();
    
            $this->addFlash("message", "Catégorie modifiée avec succès");
            return $this->redirectToRoute('app_productadd');
        }
    
        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
            'formCategorie' => $formCategorie->createView(),
        ]);
    }
    

    #[Route('/product/suppProduit/{id}', name: 'app_productsupp')]
    public function supp($id, EntityManagerInterface $entity): Response
    {
        $product = $entity->getRepository(Products::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Produit introuvable");
        }

        $entity->remove($product);
        $entity->flush();
        $this->addFlash('success', 'Produit supprimé avec succès.');
        return $this->redirectToRoute('app_productadd');
    }

    #[Route('/product/suppCategories/{id}', name: 'app_Categorysupp')]
    public function suppCat($id, Request $request, EntityManagerInterface $entity): Response
    {
        $category = $entity->getRepository(Categories::class)->find($id);
        $entity->remove($category);
        $entity->flush();
        return $this->redirectToRoute('app_product');
    }

    #[Route('/product/{slug}', name: 'app_product')]
    public function index($slug,Request $request, EntityManagerInterface $entity): Response
    {
        $category = $entity->getRepository(Categories::class)->findBy(["slug"=>$slug]);
        $product = $entity->getRepository(Products::class)->findBy(["category"=>$category]);
       
        return $this->render('product/index.html.twig', [
            'products'=>$product,
        ]);
    }
}
