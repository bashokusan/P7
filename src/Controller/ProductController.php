<?php

namespace App\Controller;

use App\Manager\ProductManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Product;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    /**
     * @Post(
     *    path = "api/items/c",
     *    name = "app_item_create"
     * )
     * @ParamConverter("product", converter="fos_rest.request_body")
     * @param Product $product
     * @param ValidatorInterface $validator
     * @return \FOS\RestBundle\View\View
     * @SWG\Response(
     *     response=201,
     *     description="Add a new product (admin only)",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @Security("is_granted('ROLE_ADMIN')", message="Vous ne pouvez pas ajouter un produit")
     */
    public function createAction(Product $product, ValidatorInterface $validator)
    {
        $validationErrors = $validator->validate($product, null, ['add']);

        if(count($validationErrors) > 0){
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        $this->manager->deleteCache();

        return $this->view(
            $product,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_items_show', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Put(
     *    path = "api/items/c/{id}",
     *    name = "app_item_update",
     *    requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newProduct", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="Update a product (admin only)",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @param Product $product
     * @param Product $newProduct
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     * @Security("is_granted('ROLE_ADMIN')", message="Vous ne pouvez pas modifier ce produit")
     */
    public function updateAction(Product $product, Product $newProduct, ConstraintViolationListInterface $validationErrors)
    {
        if(count($validationErrors) > 0){
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        if (!empty($newProduct->getName())){
            $product->setName($newProduct->getName());
        };

        if (!empty($newProduct->getReference())){
            $product->setReference($newProduct->setReference());
        };

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->manager->deleteCache();

        return $this->view(
            $product,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_items_show', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Delete(
     *    path = "api/items/c/{id}",
     *    name = "app_item_delete",
     *    requirements = {"id"="\d+"}
     * )
     * @View(StatusCode = 204)
     * @SWG\Response(
     *     response=204,
     *     description="Delete a product (admin only)",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @param Product $product
     * @Security("is_granted('ROLE_ADMIN')", message="Vous ne pouvez pas supprimer ce produit")
     */
    public function deleteAction(Product $product)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->manager->deleteCache();

        return;
    }
}
