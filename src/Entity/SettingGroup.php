<?php

namespace Bytesystems\SettingsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'bytesystems_setting_group')]
#[ORM\Entity]
class SettingGroup
{
    #[ORM\Id]
    #[ORM\Column(name: 'group_key', type: Types::STRING, length: 40)]
    protected ?string $key = null;

    #[ORM\Column(type: Types::STRING, length: 60)]
    protected ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<\Bytesystems\SettingsBundle\Entity\SettingDefinition>
     */
    #[ORM\OneToMany(mappedBy: 'settingGroup', targetEntity: 'SettingDefinition', orphanRemoval: true)]
    protected \Doctrine\Common\Collections\Collection $settings;

    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $sortOrder = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $role = null;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(mixed $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }


    /**
     * @return Collection
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function addSetting(SettingDefinition $setting): self
    {
        if (!$this->settings->contains($setting)) {
            $this->settings[] = $setting;
            $setting->setSettingGroup($this);
        }

        return $this;
    }

    public function removeSetting(SettingDefinition $setting): self
    {
        if ($this->settings->contains($setting)) {
            $this->settings->removeElement($setting);
            // set the owning side to null (unless already changed)
            if ($setting->getSettingGroup() === $this) {
                $setting->setSettingGroup(null);
            }
        }

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }
}
