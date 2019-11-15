<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 * @UniqueEntity(fields={"name"}, message="Cet produit existe déjà", groups={"add"})
 *
 *
 * @Hateoas\Relation(
 *    "self",
 *    href = @Hateoas\Route(
 *        "app_items_show",
 *        parameters = {"id" = "expr(object.getId())"},
 *        absolute = true
 *    ),
 *    exclusion = @Hateoas\Exclusion(groups={"list"})
 * )
 */
class Product
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
     * @Assert\NotBlank(groups={"add"}, message="Ce champ est obligatoire")
     * @Groups({"list", "detail"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"add"}, message="Ce champ est obligatoire")
     * @Groups({"list", "detail"})
     */
    private $reference;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail"})
     */
    private $price;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"detail"})
     */
    private $description;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
