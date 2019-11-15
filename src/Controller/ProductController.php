<?php

namespace App\Controller;

use App\Manager\ProductManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\Product;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class ProductController extends AbstractFOSRestController
{
    private $manager;

    public function __construct(ProductManager $manager){
        $this->manager = $manager;
    }

    /**
     * @Get(
     *      path = "api/items",
     *      name = "app_items_list",
     * )
     * @View(serializerGroups={"list"})
     * @SWG\Response(
     *     response=200,
     *     description="Return the list of BileMo products",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     */
    public function showAllItems(){

        return $this->manager->getShowAll();
    }

    /**
     * @Get(
     *      path = "api/items/{id}",
     *      name = "app_items_show",
     *      requirements = {"id"="\d+"}
     * )
     * @View(serializerGroups={"detail"})
     * @SWG\Response(
     *     response=200,
     *     description="Return information of a product",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @param Product $product
     * @return Product
     */
    public function getShowUniqueItem(Product $product){
        return $this->manager->getShowUnique($product);
    }
}
