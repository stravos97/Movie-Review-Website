<?php

namespace App\Entity;

use App\Repository\DirectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Table(name="director")
 * @ORM\Entity(repositoryClass=DirectorRepository::class)
 */
class Director
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $lastName;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Film", mappedBy="director")
     * @ORM\Column(type="array", nullable=true)
     */
    private $films;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $imageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;
	
	/**
	 * Director constructor.
	 */
	public function __construct()
	{
		$this->films = new ArrayCollection();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    public function setImageFile(string $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
