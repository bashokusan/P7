<?php

namespace App\Controller;

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
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractFOSRestController
{
    private $cache;

    public function __construct(CacheInterface $cache){
        $this->cache = $cache;
    }

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
     * @throws InvalidArgumentException
     */
    public function getShowAll(){

        return $this->cache->get('showAll', function(ItemInterface $item){
            $item->expiresAfter(3600);

            return $this->showAll();
        });
    }

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
     * @throws InvalidArgumentException
     */
    public function getShowUnique(Product $product){
        return $this->cache->get('showAction', function(ItemInterface $item) use ($product) {
            $item->expiresAfter(3600);

            return $this->showAction($product);
        });
    }

    public function showAction(Product $product)
    {
        return $product;
    }

    /**
     * @Post(
     *    path = "api/items",
     *    name = "app_item_create"
     * )
     * @ParamConverter("product", converter="fos_rest.request_body")
     * @param Product $product
     * @param ValidatorInterface $validator
     * @return \FOS\RestBundle\View\View
     * @SWG\Response(
     *     response=201,
     *     description="Ajout d'un nouveau produit",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @Security("is_granted('ROLE_ADMIN')", message="Vous ne pouvez pas ajouter un produit")
     * @throws InvalidArgumentException
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

        $this->cache->delete('showAll');
        $this->cache->delete('showAction');

        return $this->view(
            $product,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_items_show', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Put(
     *    path = "api/items/{id}",
     *    name = "app_item_update",
     *    requirements = {"id"="\d+"}
     * )
     * @ParamConverter("newProduct", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="Mise à jour les informations d'un produit",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @param Product $product
     * @param Product $newProduct
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     * @Security("is_granted('ROLE_ADMIN')", message="Vous ne pouvez pas modifier ce produit")
     * @throws InvalidArgumentException
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

        $this->cache->delete('showAll');
        $this->cache->delete('showAction');

        return $this->view(
            $product,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_items_show', ['id' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Delete(
     *    path = "api/items/{id}",
     *    name = "app_item_delete",
     *    requirements = {"id"="\d+"}
     * )
     * @View(StatusCode = 204)
     * @SWG\Response(
     *     response=204,
     *     description="Suppression un produit",
     *     @Model(type=Product::class)
     * )
     * @SWG\Tag(name="Produits")
     * @param Product $product
     * @Security("is_granted('ROLE_ADMIN')", message="Vous ne pouvez pas supprimer ce produit")
     * @throws InvalidArgumentException
     */
    public function deleteAction(Product $product)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->cache->delete('showAll');
        $this->cache->delete('showAction');

        return;
    }
}
