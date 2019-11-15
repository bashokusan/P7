<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

  /**
  * @ORM\Entity(repositoryClass="App\Repository\ProductUserRepository")
  * @UniqueEntity(fields={"email"}, message="Cet utilisateur existe déjà", groups={"registration"})
  *
  * @Hateoas\Relation(
  *    "self",
  *    href = @Hateoas\Route(
  *        "app_users_show",
  *        parameters = {"id" = "expr(object.getId())"},
  *        absolute = true
  *    ),
   *     exclusion = @Hateoas\Exclusion(groups={"list"})
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
     * @Groups({"list", "detail"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"registration"}, message="Ce champ est obligatoire")
     *  @Groups({"list", "detail"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"registration"}, message="Ce champ est obligatoire")
     * @Groups({"list", "detail"})
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="productUsers")
     * @Groups({"list"})
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail"})
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"detail"})
     */
    private $address;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
