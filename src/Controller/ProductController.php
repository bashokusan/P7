<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\Product;

class ProductController extends AbstractFOSRestController
{
    /**
    * @Get(
    *      path = "/items",
    *      name = "app_items_list",
    * )
    * @View()
    */
    public function showAll()
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findAll();
        return $product;
    }
}
