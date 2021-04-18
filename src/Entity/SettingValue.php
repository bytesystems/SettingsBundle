<?php

namespace Bytesystems\SettingsBundle\Entity;

use Bytesystems\SettingsBundle\Repository\SettingValueRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(name="bytesystems_setting_value",uniqueConstraints={
 *        @UniqueConstraint(name="setting_owner_unique",columns={"setting_key", "owner"})
 * })
 * @ORM\Entity(repositoryClass=SettingValueRepository::class)
 */
class SettingValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string|null
     * @ORM\Column(name="owner", type="string", length=255, nullable=true)
     */
    protected $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Bytesystems\SettingsBundle\Entity\Setting")
     * @ORM\JoinColumn(nullable=false,name="setting_key", referencedColumnName="setting_key")
     */
    protected $setting;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(?string $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function getSetting(): ?Setting
    {
        return $this->setting;
    }

    public function setSetting(?Setting $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
