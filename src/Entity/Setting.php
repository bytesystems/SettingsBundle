<?php

namespace Bytesystems\SettingsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

/**
 * @ORM\Table(name="bytesystems_setting_definition")
 * @ORM\Entity()
 */
class Setting
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_USER = 'user';
    const SCOPE_DEFAULT = 'default';

    /**
     * @ORM\Id()
     * @ORM\Column(name="setting_key",type="string",length=40)
     */
    protected $key;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $scope;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isConstrained;

    /**
     * @ORM\Column(type="string", length=40)
     */
    protected $dataType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $valueMin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $valueMax;

    /**
     * @ORM\OneToMany(targetEntity="Bytesystems\SettingsBundle\Entity\AllowedSettingValue", mappedBy="setting", orphanRemoval=true)
     */
    protected $allowedSettingValues;

    /**
     * @ORM\ManyToOne(targetEntity="Bytesystems\SettingsBundle\Entity\SettingGroup", inversedBy="settings")
     * @ORM\JoinColumn(nullable=false,name="group_key", referencedColumnName="group_key")
     */
    protected $settingGroup;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sortOrder;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $parentSetting;

    public function __construct()
    {
        $this->allowedSettingValues = new ArrayCollection();
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(string $key): self {
        $this->key = $key;
        return $this;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setScope($scope): self
    {
        $scopes = [self::SCOPE_APP, self::SCOPE_USER,self::SCOPE_MIXED];
        if (!in_array($scope, $scopes)) {
            throw new \InvalidArgumentException(sprintf("Invalid scope, allowed values: %s",join(", ",$scopes)));
        }

        $this->scope = $scope;
        return $this;
    }



    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsConstrained(): ?bool
    {
        return $this->isConstrained;
    }

    public function setIsConstrained(bool $isConstrained): self
    {
        $this->isConstrained = $isConstrained;
        return $this;
    }

    public function getDataType(): ?string
    {
        return $this->dataType;
    }

    public function setDataType(string $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }

    public function getValueMin()
    {
        return $this->valueMin;
    }

    public function setValueMin($valueMin): self
    {
        $this->valueMin = $valueMin;

        return $this;
    }

    public function getValueMax()
    {
        return $this->valueMax;
    }

    public function setValueMax($valueMax): self
    {
        $this->valueMax = $valueMax;

        return $this;
    }

    /**
     * @return Collection|AllowedSettingValue[]
     */
    public function getAllowedSettingValues(): Collection
    {
        return $this->allowedSettingValues;
    }

    public function addAllowedSettingValue(AllowedSettingValue $allowedSettingValue): self
    {
        if (!$this->allowedSettingValues->contains($allowedSettingValue)) {
            $this->allowedSettingValues[] = $allowedSettingValue;
            $allowedSettingValue->setSetting($this);
        }

        return $this;
    }

    public function removeAllowedSettingValue(AllowedSettingValue $allowedSettingValue): self
    {
        if ($this->allowedSettingValues->contains($allowedSettingValue)) {
            $this->allowedSettingValues->removeElement($allowedSettingValue);
            // set the owning side to null (unless already changed)
            if ($allowedSettingValue->getSetting() === $this) {
                $allowedSettingValue->setSetting(null);
            }
        }

        return $this;
    }

    public function getSettingGroup(): ?SettingGroup
    {
        return $this->settingGroup;
    }

    public function setSettingGroup(?SettingGroup $settingGroup): self
    {
        $this->settingGroup = $settingGroup;

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

    public function getParentSetting(): ?string
    {
        return $this->parentSetting;
    }

    public function setParentSetting(?string $parentSetting): self
    {
        $this->parentSetting = $parentSetting;

        return $this;
    }
}
