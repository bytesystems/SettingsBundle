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
     * @ORM\OneToMany(targetEntity="Bytesystems\SettingsBundle\Entity\Setting", mappedBy="settingGroup", orphanRemoval=true)
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

    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @return Collection|Setting[]
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function addSetting(Setting $setting): self
    {
        if (!$this->settings->contains($setting)) {
            $this->settings[] = $setting;
            $setting->setSettingGroup($this);
        }

        return $this;
    }

    public function removeSetting(Setting $setting): self
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
