<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Comment should not be blank!")
     */
    private $commenttext;

    /**
     * @ORM\Column(type="date")
     */
    private $commentdate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="post")
     */
    private $post;

    /**
     * @ORM\Column(type="integer", length=11, nullable=true, options={"default" : 0})
     */
    private $likes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="")
     */
    private $user;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommenttext(): ?string
    {
        return $this->commenttext;
    }

    public function setCommenttext(?string $commenttext): self
    {
        $this->commenttext = $commenttext;

        return $this;
    }

    public function getCommentdate(): ?\DateTimeInterface
    {
        return $this->commentdate;
    }

    public function setCommentdate(\DateTimeInterface $commentdate): self
    {
        $this->commentdate = $commentdate;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(?int $likes): self
    {
        $this->likes = $likes;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
