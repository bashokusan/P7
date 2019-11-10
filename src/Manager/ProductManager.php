<?php


namespace App\Manager;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ProductManager
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var ProductRepository
     */
    private $repository;

    public function __construct(CacheInterface $cache, ProductRepository $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }

    public function getShowAll(){

        return $this->cache->get('showAll', function(ItemInterface $item){
            $item->expiresAfter(3600);

            return $this->showAll();
        });
    }

    private function showAll()
    {
        $product = $this->repository->findAll();

        return $product;
    }

    public function getShowUnique(Product $product){
        return $this->cache->get('showAction', function(ItemInterface $item) use ($product) {
            $item->expiresAfter(3600);

            return $this->showAction($product);
        });
    }

    private function showAction(Product $product)
    {
        return $product;
    }

    public function deleteCache()
    {
        $this->cache->delete('showAll');
        $this->cache->delete('showAction');
    }
}