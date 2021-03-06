<?php

namespace App\Entity;

use App\Repository\PostRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @Vich\Uploadable
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private ?string $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank
     */
    private ?string $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $owner;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="posts", cascade={"persist"})
     * @Assert\Count(max = 5)
     * @Assert\Valid
     */
    private ?Collection $Tags;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="favorites")
     */
    private $starred;

    /**

     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="Likes")
     */
    private $Liked;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="post")
     */
    private $comments;

    /**
     * @Vich\UploadableField(mapping="posts", fileNameProperty="photo")
     * 
     * @var File|null
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, inversedBy="posts")
     */
    private $products;



    public function __construct()
    {
        $this->Tags = new ArrayCollection();
        $this->starred = new ArrayCollection();
        $this->Liked = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->products = new ArrayCollection();

        $this->createdAt = new DateTimeImmutable('now');
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->Tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->Tags->contains($tag)) {
            $this->Tags[] = $tag;
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getStarred(): Collection
    {
        return $this->starred;
    }

    public function addToStarred(User $starred): self
    {
        if (!$this->starred->contains($starred)) {
            $this->starred[] = $starred;
            $starred->addToFavorite($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->Tags->removeElement($tag);
        return $this;
    }

    public function removeFromStarred(User $starred): self
    {
        if ($this->starred->removeElement($starred)) {
            $starred->removeFromFavorite($this);
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getLiked(): Collection
    {
        return $this->Liked;
    }

    public function addLiked(User $liked): self
    {
        if (!$this->Liked->contains($liked)) {
            $this->Liked[] = $liked;
            $liked->addLike($this);
        }

        return $this;
    }

    public function removeLiked(User $liked): self
    {
        if ($this->Liked->removeElement($liked)) {
            $liked->removeLike($this);
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function addStarred(User $starred): self
    {
        if (!$this->starred->contains($starred)) {
            $this->starred[] = $starred;
            $starred->addFavorite($this);
        }

        return $this;
    }

    public function removeStarred(User $starred): self
    {
        if ($this->starred->removeElement($starred)) {
            $starred->removeFavorite($this);
        }

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    /**
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
}
