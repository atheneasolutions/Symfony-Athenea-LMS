<?php

namespace Athenea\LMS\Document;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

#[Document(collection: "athenea_lms_users")]
class User
{
    #[Id]
    private string $id;

    #[Field]
    private string $cip;

    #[Field]
    private ?string $dni;

    #[Field]
    private ?string $dn;

    #[Field]
    private string $codiUserConnectat;

    #[Field]
    private string $nomUserConnectat;

    #[Field]
    private ?string $dadesAuxiliars;

    #[Field]
    private string $uniqueCode;

    #[Field]
    private string $idioma;

    #[Field]
    private bool $used = false;

    #[Field(type: 'date_immutable')]
    private ?DateTimeInterface $expiresAt = null;

    public function __construct(
        string $cip,
        string $codiUserConnectat,
        string $nomUserConnectat,
        string $uniqueCode,
        string $idioma = "ca",
        ?string $dni = null,
        ?string $dn = null,
        ?string $dadesAuxiliars = null,
        bool $used = false,
        ?DateTimeInterface $expiresAt = null,

    ) {
        $this->cip = $cip;
        $this->codiUserConnectat = $codiUserConnectat;
        $this->nomUserConnectat = $nomUserConnectat;
        $this->uniqueCode = $uniqueCode;
        $this->dni = $dni;
        $this->dn = $dn;
        $this->dadesAuxiliars = $dadesAuxiliars;
        $this->idioma = $idioma;
        $this->used = $used;
        $this->setExpiresAt($expiresAt);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCip(): string
    {
        return $this->cip;
    }

    public function setCip(string $cip): self
    {
        $this->cip = $cip;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(?string $dni): self
    {
        $this->dni = $dni;

        return $this;
    }

    public function getDn(): ?string
    {
        return $this->dn;
    }

    public function setDn(?string $dn): self
    {
        $this->dn = $dn;

        return $this;
    }

    public function getCodiUserConnectat(): string
    {
        return $this->codiUserConnectat;
    }

    public function setCodiUserConnectat(string $codiUserConnectat): self
    {
        $this->codiUserConnectat = $codiUserConnectat;

        return $this;
    }

    public function getNomUserConnectat(): string
    {
        return $this->nomUserConnectat;
    }

    public function setNomUserConnectat(string $nomUserConnectat): self
    {
        $this->nomUserConnectat = $nomUserConnectat;

        return $this;
    }

    public function getDadesAuxiliars(): ?string
    {
        return $this->dadesAuxiliars;
    }

    public function setDadesAuxiliars(?string $dadesAuxiliars): self
    {
        $this->dadesAuxiliars = $dadesAuxiliars;

        return $this;
    }

    /**
     * Get the value of uniqueCode
     */
    public function getUniqueCode(): string
    {
        return $this->uniqueCode;
    }

    /**
     * Set the value of uniqueCode
     */
    public function setUniqueCode(string $uniqueCode): self
    {
        $this->uniqueCode = $uniqueCode;

        return $this;
    }

    /**
     * Get the value of idioma
     */
    public function getIdioma(): string
    {
        return $this->idioma;
    }

    /**
     * Set the value of idioma
     */
    public function setIdioma(string $idioma): self
    {
        $this->idioma = $idioma;

        return $this;
    }

    /**
     * Get the value of used
     */
    public function isUsed(): bool
    {
        return $this->used;
    }

    /**
     * Set the value of used
     */
    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get the value of expiresAt
     */
    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt ? DateTimeImmutable::createFromInterface($this->expiresAt) : null;
    }

    /**
     * Set the value of expiresAt
     */
    public function setExpiresAt(?DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt ? DateTimeImmutable::createFromInterface($expiresAt) : null;
        return $this;
    }
}
