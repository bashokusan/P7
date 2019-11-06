<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use App\Exception\ResourceViolationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use FOS\RestBundle\Request\ParamFetcher;
use App\Entity\ProductUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class UserController extends AbstractFOSRestController
{

    private $cache;

    public function __construct(CacheInterface $cache){
        $this->cache = $cache;
    }

    /**
     * @Get(
     *      path = "api/users",
     *      name = "app_users_list",
     * )
     * @View()
     * @SWG\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs du client connecté",
     *     @Model(type=ProductUser::class)
     * )
     */
    public function getShowAll(){

        return $this->cache->get('showAll', function(ItemInterface $item){
            $item->expiresAfter(3600);

            return $this->showAll();
        });
    }

    private function showAll()
    {
        $users= $this->getDoctrine()->getRepository(ProductUser::class)->findBy(['client' => $this->getUser()]);

        return $users;
    }

    /**
     * @Get(
     *      path = "api/users/{id}",
     *      name = "app_users_show",
     *      requirements = {"id"="\d+"}
     * )
     * @View()
     * @SWG\Response(
     *     response=200,
     *     description="Retourne les informations de l'utilisateur du client connecté",
     *     @Model(type=ProductUser::class)
     * )
     * @param ProductUser $user
     * @return ProductUser
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getShowUnique(ProductUser $user){
        return $this->cache->get('showAction', function(ItemInterface $item) use ($user) {
            $item->expiresAfter(3600);

            return $this->showAction($user);
        });
    }

    private function showAction(ProductUser $user)
    {
        return $user;
    }

    /**
     * @Post(
     *    path = "api/users",
     *    name = "app_user_create"
     * )
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param ProductUser $user
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     * @throws ResourceViolationException
     * @SWG\Response(
     *     response=201,
     *     description="Ajout d'un nouvel utilisateur",
     *     @Model(type=ProductUser::class)
     * )
     * @throws InvalidArgumentException
     */
    public function createAction(ProductUser $user, ConstraintViolationListInterface $validationErrors)
    {
        //$user->setClient($this->getUser());

        if(count($validationErrors) > 0){
            //return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($validationErrors as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new ResourceViolationException($message);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->cache->delete('showAll');

        return $this->view(
            $user,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_users_show', ['id' => $user->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Put(
     *    path = "api/users/{id}",
     *    name = "app_user_update"
     * )
     * @ParamConverter("newUser", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="Mise à jour les informations d'un utilisateur",
     *     @Model(type=ProductUser::class)
     * )
     * @param ProductUser $productUser
     * @param ProductUser $newUser
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     * @throws ResourceViolationException
     * @throws InvalidArgumentException
     * @Security("is_granted('ROLE_USER') and user == productUser.getClient()", message="Vous ne pouvez pas modifier cet utilisateur")
     */
    public function updateAction(ProductUser $productUser, ProductUser $newUser, ConstraintViolationListInterface $validationErrors)
    {
        if(count($validationErrors) > 0){
            //return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($validationErrors as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new ResourceViolationException($message);

        }

        $productUser->setName($newUser->getName());
        $productUser->setEmail($newUser->getEmail());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->cache->delete('showAll');
        $this->cache->delete('showAction');

        return $this->view(
            $productUser,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_users_show', ['id' => $productUser->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Delete(
     *    path = "api/users/{id}",
     *    name = "app_user_delete",
     *    requirements = {"id"="\d+"}
     * )
     * @View(StatusCode = 204)
     * @SWG\Response(
     *     response=204,
     *     description="Suppression un utilisateur",
     *     @Model(type=ProductUser::class)
     * )
     * @param ProductUser $productUser
     * @throws InvalidArgumentException
     * @Security("is_granted('ROLE_USER') and user == productUser.getClient()", message="Vous ne pouvez pas supprimer cet utilisateur")
     */
    public function deleteAction(ProductUser $productUser)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($productUser);
        $em->flush();

        $this->cache->delete('showAll');
        $this->cache->delete('showAction');

        return;
    }
}
