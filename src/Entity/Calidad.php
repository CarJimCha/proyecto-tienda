<?php

namespace App\Entity;

use App\Repository\CalidadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalidadRepository::class)]
class Calidad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $multiplicador_precio = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $multiplicador_precio_combate = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'calidad')]
    private Collection $transactions;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->transactions = new ArrayCollection();
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

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getMultiplicadorPrecio(): ?string
    {
        return $this->multiplicador_precio;
    }

    public function setMultiplicadorPrecio(string $multiplicador_precio): static
    {
        $this->multiplicador_precio = $multiplicador_precio;

        return $this;
    }

    public function getMultiplicadorPrecioCombate(): ?string
    {
        return $this->multiplicador_precio_combate;
    }

    public function setMultiplicadorPrecioCombate(string $multiplicador_precio_combate): static
    {
        $this->multiplicador_precio_combate = $multiplicador_precio_combate;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nombre;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setCalidad($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCalidad() === $this) {
                $transaction->setCalidad(null);
            }
        }

        return $this;
    }
}
