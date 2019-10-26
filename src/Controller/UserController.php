<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
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
    *      path = "/users",
    *      name = "app_users_list",
    * )
    * @View()
    *
    */
    public function getShowAll(){
        return $this->cache->get('showAll', function(ItemInterface $item){
            $item->expiresAfter(3600);
            return $this->showAll();
        });
    }

    private function showAll()
    {
        $users= $this->getDoctrine()->getRepository(ProductUser::class)->findAll();
        return $users;
    }

    /**
    * @Get(
    *      path = "/users/{id}",
    *      name = "app_users_show",
    *      requirements = {"id"="\d+"}
    * )
    * @View()
    *
    */
    public function showAction(ProductUser $user)
    {
        return $user;
    }

    /**
     * @Post(
     *    path = "/users",
     *    name = "app_user_create"
     * )
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param ProductUser $user
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     * @throws ResourceViolationException
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

        return $this->view(
            $user,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_users_show', ['id' => $user->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Put(
     *    path = "/users/{id}",
     *    name = "app_user_update"
     * )
     * @ParamConverter("newUser", converter="fos_rest.request_body")
     */
    public function updateAction(ProductUser $user, ProductUser $newUser, ConstraintViolationListInterface $validationErrors)
    {
        if(count($validationErrors) > 0){
            //return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
            $message = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
            foreach ($validationErrors as $violation) {
                $message .= sprintf("Field %s: %s ", $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new ResourceViolationException($message);

        }

        $user->setName($newUser->getName());
        $user->setEmail($newUser->getEmail());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->view(
            $user,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_users_show', ['id' => $user->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Delete(
     *    path = "/users/{id}",
     *    name = "app_user_delete",
     *    requirements = {"id"="\d+"}
     * )
     * @View(StatusCode = 204)
     */
    public function deleteAction(ProductUser $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return;
    }
}
