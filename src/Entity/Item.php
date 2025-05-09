<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use function PHPUnit\Framework\isNull;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(nullable: true)]
    private ?int $precio = null;

    #[ORM\Column(nullable: true)]
    private ?float $peso = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'item')]
    private Collection $transaction;

    #[ORM\ManyToOne(inversedBy: 'items')]
    private ?Categoria $categoria = null;

    public function __construct()
    {
        $this->transaction = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getPrecio(): ?int
    {
        return $this->precio;
    }

    public function setPrecio(?int $precio): static
    {
        $this->precio = $precio;

        return $this;
    }

    public function getPeso(): ?float
    {
        return $this->peso;
    }

    public function setPeso(?float $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getMO(): int
    {
        return intdiv($this->precio, 1000);
    }

    public function getMP(): int
    {
        return intdiv($this->precio, 10) % 100;
    }

    public function getMC(): int
    {
        return $this->precio % 10;
    }

    public function getFormattedPrecio(): string
    {
        if ($this->getPrecio() != 0)
        {
            $mo = $this->getMO();
            $mc = $this->getMC();
            $mp = $this->getMP();

            $precio = [];

            if ($mo > 0) {
                $precio[] = "{$mo} MO";
            }
            if ($mp > 0) {
                $precio[] = "{$mp} MP";
            }
            if ($mc > 0) {
                $precio[] = "{$mc} MC";
            }

            return implode(", ", $precio);
        }
        else {
            return "No Aplica";
        }
    }


    /**
     * @return Collection<int, Transaction>
     */
    public function getTransaction(): Collection
    {
        return $this->transaction;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transaction->contains($transaction)) {
            $this->transaction->add($transaction);
            $transaction->setItem($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transaction->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getItem() === $this) {
                $transaction->setItem(null);
            }
        }

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

}
