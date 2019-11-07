<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\Product;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class ProductController extends AbstractFOSRestController
{
    /**
    * @Get(
    *      path = "api/items",
    *      name = "app_items_list",
    * )
    * @View()
    * @SWG\Response(
    *     response=200,
    *     description="Retourne la liste des produits",
    *     @Model(type=Product::class)
    * )
    * @SWG\Tag(name="Produits")
    */
    public function showAll()
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $product;
    }

    /**
    * @Get(
    *      path = "api/items/{id}",
    *      name = "app_items_show",
    *      requirements = {"id"="\d+"}
    * )
    * @View()
    * @SWG\Response(
    *     response=200,
    *     description="Retourne les informations d'un produit",
    *     @Model(type=Product::class)
    * )
    * @SWG\Tag(name="Produits")
    * @param Product $product
    * @return Product
    */
    public function showAction(Product $product)
    {
        return $product;
    }
}
