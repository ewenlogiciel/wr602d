<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $usageLimit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?float $special_price = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $special_price_from = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $special_price_to = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'plan', orphanRemoval: true)]
    private Collection $users;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Tool>
     */
    #[ORM\ManyToMany(targetEntity: Tool::class, mappedBy: 'plan')]
    private Collection $tools;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePriceId = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->tools = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(?int $usageLimit): static
    {
        $this->usageLimit = $usageLimit;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSpecialPrice(): ?float
    {
        return $this->special_price;
    }

    public function setSpecialPrice(?float $special_price): static
    {
        $this->special_price = $special_price;

        return $this;
    }

    public function getSpecialPriceFrom(): ?\DateTime
    {
        return $this->special_price_from;
    }

    public function setSpecialPriceFrom(?\DateTime $special_price_from): static
    {
        $this->special_price_from = $special_price_from;

        return $this;
    }

    public function getSpecialPriceTo(): ?\DateTime
    {
        return $this->special_price_to;
    }

    public function setSpecialPriceTo(?\DateTime $special_price_to): static
    {
        $this->special_price_to = $special_price_to;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setPlan($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getPlan() === $this) {
                $user->setPlan(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        // C'est ici qu'on définit la date automatiquement
        $this->created_at = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Tool>
     */
    public function getTools(): Collection
    {
        return $this->tools;
    }

    public function addTool(Tool $tool): static
    {
        if (!$this->tools->contains($tool)) {
            $this->tools->add($tool);
            $tool->addPlan($this);
        }

        return $this;
    }

    public function removeTool(Tool $tool): static
    {
        if ($this->tools->removeElement($tool)) {
            $tool->removePlan($this);
        }

        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(?string $stripePriceId): static
    {
        $this->stripePriceId = $stripePriceId;

        return $this;
    }
}
