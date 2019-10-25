<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Hateoas\Configuration\Annotation as Hateoas;

  /**
  * @ORM\Entity(repositoryClass="App\Repository\ProductUserRepository")
  * @UniqueEntity(fields={"email"}, message="Cet utilisateur existe déjà")
  *
  * @ExclusionPolicy("all")
  *
  * @Hateoas\Relation(
  *    "self",
  *    href = @Hateoas\Route(
  *        "app_users_show",
  *        parameters = {"id" = "expr(object.getId())"},
  *        absolute = true
  *    )
  * )
  *
  * @Hateoas\Relation(
  *    "update",
  *    href = @Hateoas\Route(
  *        "app_user_update",
  *        parameters = {"id" = "expr(object.getId())"},
  *        absolute = true
  *    )
  * )
  *
  * @Hateoas\Relation(
  *    "delete",
  *    href = @Hateoas\Route(
  *        "app_user_delete",
  *        parameters = {"id" = "expr(object.getId())"},
  *        absolute = true
  *    )
  * )
  *
  * @Hateoas\Relation(
  *    "client",
  *    embedded = @Hateoas\Embedded("expr(object.getclient())")
  * )
  */
class ProductUser
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Expose
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     *
     * @Expose
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     *
     * @Expose
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="productUsers")
     */
    private $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
