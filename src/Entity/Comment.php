<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Review::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movieID;

    /**
     * @ORM\Column(type="text")
     */
    private $commentBody;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMovieID(): ?Review
    {
        return $this->movieID;
    }

    public function setMovieID(?Review $movieID): self
    {
        $this->movieID = $movieID;

        return $this;
    }

    public function getCommentBody(): ?string
    {
        return $this->commentBody;
    }

    public function setCommentBody(string $commentBody): self
    {
        $this->commentBody = $commentBody;

        return $this;
    }

}
