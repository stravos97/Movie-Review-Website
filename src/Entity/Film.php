<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
/**
 * @ORM\Table(name="film")
 * @ORM\Entity(repositoryClass=FilmRepository::class)
 */
class Film
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $synopsis;

    /**
     * @var ArrayCollection
     * @ORM\Column(type="array", nullable=true)
     */
    private $reviews;

    /**
     * @ORM\ManyToOne(targetEntity="Director")
     * @ORM\JoinColumn(name="director_id", referencedColumnName="id")
     * @ORM\Column(type="string", length=255)
     */
    private $director;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $genre;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $publishedDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $imageName;
	
	/**
	 * Film constructor.
	 */
	public function __construct()
	{
		$this->reviews = new ArrayCollection();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }
    

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(string $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getPublishedDate(): ?\DateTimeImmutable
    {
        return $this->publishedDate;
    }

    public function setPublishedDate(?\DateTimeImmutable $publishedDate): self
    {
        $this->publishedDate = $publishedDate;

        return $this;
    }

    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    public function setImageFile(?string $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }
}
