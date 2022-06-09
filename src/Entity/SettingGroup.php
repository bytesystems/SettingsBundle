<?php

namespace Bytesystems\SettingsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="bytesystems_setting_group")
 * @ORM\Entity()
 */
class SettingGroup
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="group_key",type="string",length=40)
     */
    protected $key;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="SettingDefinition", mappedBy="settingGroup", orphanRemoval=true)
     */
    protected $settings;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sortOrder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $role;

    public function __construct()
    {
        $this->settings = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
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
     * @return Collection|SettingDefinition[]
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
