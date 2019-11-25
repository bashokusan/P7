<?php


namespace App\Manager;

use App\Entity\ProductUser;
use App\Repository\ProductUserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(CacheInterface $cache, ProductUserRepository $repository, Security $security, EntityManagerInterface $entityManager)
    {
        $this->cache = $cache;
        $this->repository = $repository;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function getShowAll(){

        return $this->cache->get('showAllUser', function(ItemInterface $item){
            $item->expiresAfter(3600);

            return $this->showAll();
        });
    }

    private function showAll()
    {
        $product = $this->repository->findBy(['client' => $this->security->getUser()]);

        return $product;
    }

    public function getShowUnique(ProductUser $productUser)
    {
        return $productUser;
    }

    public function createUser(ProductUser $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->deleteCache();
    }

    public function updateUser()
    {
        $this->entityManager->flush();

        $this->deleteCache();
    }

    public function deleteUser(ProductUser $user)
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->deleteCache();
    }

    private function deleteCache()
    {
        $this->cache->delete('showAllUser');
        $this->cache->delete('showActionUser');
    }
}