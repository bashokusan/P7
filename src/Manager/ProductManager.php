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

        return $this->cache->get('showAllProduct', function(ItemInterface $item){
            $item->expiresAfter(3600);

            return $this->showAll();
        });
    }

    private function showAll()
    {
        $product = $this->repository->findAll();

        return $product;
    }

    public function getShowUnique(Product $product)
    {
        return $product;
    }

    public function deleteCache()
    {
        $this->cache->delete('showAllProduct');
        $this->cache->delete('showActionProduct');
    }
}