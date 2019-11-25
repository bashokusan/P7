<?php

namespace App\Controller;

use App\Manager\UserManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\ProductUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class UserController extends AbstractFOSRestController
{

    /**
     * @var UserManager
     */
    private $manager;

    public function __construct(UserManager $manager){
        $this->manager = $manager;
    }

    /**
     * @Get(
     *      path = "api/users",
     *      name = "app_users_list",
     * )
     * @View(serializerGroups={"list"})
     * @SWG\Response(
     *     response=200,
     *     description="Return the list of a client's users",
     *     @Model(type=ProductUser::class)
     * )
     * @SWG\Tag(name="Utilisateurs")
     */
    public function showAllUsers(){

        return $this->manager->getShowAll();
    }

    /**
     * @Get(
     *      path = "api/users/{id}",
     *      name = "app_users_show",
     *      requirements = {"id"="\d+"}
     * )
     * @View(serializerGroups={"detail"})
     * @SWG\Response(
     *     response=200,
     *     description="Return information of a user",
     *     @Model(type=ProductUser::class)
     * )
     * @SWG\Tag(name="Utilisateurs")
     * @param ProductUser $user
     * @return ProductUser
     * @return mixed
     */
    public function showUniqueUser(ProductUser $user){
        return $this->manager->getShowUnique($user);
    }

    /**
     * @Post(
     *    path = "api/users",
     *    name = "app_user_create"
     * )
     * @View(serializerGroups={"list"})
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param ProductUser $user
     * @param ValidatorInterface $validator
     * @return \FOS\RestBundle\View\View
     * @SWG\Response(
     *     response=201,
     *     description="Add a new user",
     *     @Model(type=ProductUser::class)
     * )
     * @SWG\Tag(name="Utilisateurs")
     */
    public function createAction(ProductUser $user, ValidatorInterface $validator)
    {
        $user->setClient($this->getUser());

        $validationErrors = $validator->validate($user, null, ['registration']);

        if(count($validationErrors) > 0){
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $this->manager->createUser($user);

        return $this->view(
            $user,
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_users_show', ['id' => $user->getId(), UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Put(
     *    path = "api/users/{id}",
     *    name = "app_user_update",
     *    requirements = {"id"="\d+"}
     * )
     * @View(serializerGroups={"detail"})
     * @ParamConverter("newUser", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="Update a user",
     *     @Model(type=ProductUser::class)
     * )
     * @SWG\Tag(name="Utilisateurs")
     * @param ProductUser $productUser
     * @param ProductUser $newUser
     * @param ConstraintViolationListInterface $validationErrors
     * @return \FOS\RestBundle\View\View
     * @Security("is_granted('ROLE_USER') and user == productUser.getClient()", message="Vous ne pouvez pas modifier cet utilisateur")
     */
    public function updateAction(ProductUser $productUser, ProductUser $newUser, ConstraintViolationListInterface $validationErrors)
    {
        if(count($validationErrors) > 0){
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        if (!empty($newUser->getName())){
            $productUser->setName($newUser->getName());
        };

        if (!empty($newUser->getEmail())){
            $productUser->setEmail($newUser->getEmail());
        };

        if (!empty($newUser->getPhone())){
            $productUser->setPhone($newUser->getPhone());
        };

        if (!empty($newUser->getAddress())){
            $productUser->setAddress($newUser->getAddress());
        };

        $this->manager->updateUser();

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
     *     description="Delete a user",
     *     @Model(type=ProductUser::class)
     * )
     * @SWG\Tag(name="Utilisateurs")
     * @param ProductUser $productUser
     * @Security("is_granted('ROLE_USER') and user == productUser.getClient()", message="Vous ne pouvez pas supprimer cet utilisateur")
     */
    public function deleteAction(ProductUser $productUser)
    {
        $this->manager->deleteUser($productUser);

        return;
    }
}
