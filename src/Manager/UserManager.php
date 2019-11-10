<?php


namespace App\Manager;

use App\Entity\ProductUser;
use App\Repository\ProductUserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class UserManager
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var ProductUserRepository
     */
    private $repository;
    /**
     * @var Security
     */
    private $security;

    public function __construct(CacheInterface $cache, ProductUserRepository $repository, Security $security)
    {
        $this->cache = $cache;
        $this->repository = $repository;
        $this->security = $security;
    }

    public function getShowAll(){

        return $this->cache->get('showAll', function(ItemInterface $item){
            $item->expiresAfter(3600);

            return $this->showAll();
        });
    }

    private function showAll()
    {
        $product = $this->repository->findBy(['client' => $this->security->getUser()]);

        return $product;
    }

    public function getShowUnique(ProductUser $productUser){
        return $this->cache->get('showAction', function(ItemInterface $item) use ($productUser) {
            $item->expiresAfter(3600);

            return $this->showAction($productUser);
        });
    }

    private function showAction(ProductUser $productUser)
    {
        return $productUser;
    }

    public function deleteCache()
    {
        $this->cache->delete('showAll');
        $this->cache->delete('showAction');
    }
}