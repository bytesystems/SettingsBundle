<?php

namespace Bytesystems\SettingsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'bytesystems_setting_allowed_value')]
#[ORM\Entity]
class AllowedSettingValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'SettingDefinition', inversedBy: 'allowedSettingValues')]
    #[ORM\JoinColumn(nullable: false, name: 'setting_key', referencedColumnName: 'setting_key')]
    private ?\Bytesystems\SettingsBundle\Entity\SettingDefinition $setting = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    private ?string $itemValue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSetting(): ?SettingDefinition
    {
        return $this->setting;
    }

    public function setSetting(?SettingDefinition $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getItemValue(): ?string
    {
        return $this->itemValue;
    }

    public function setItemValue(string $itemValue): self
    {
        $this->itemValue = $itemValue;

        return $this;
    }
}
