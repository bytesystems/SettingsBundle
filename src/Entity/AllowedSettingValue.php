<?php

namespace Bytesystems\SettingsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="bytesystems_setting_allowed_value")
 * @ORM\Entity()
 */
class AllowedSettingValue
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Bytesystems\SettingsBundle\Setting", inversedBy="allowedSettingValues")
     * @ORM\JoinColumn(nullable=false,name="setting_key", referencedColumnName="setting_key")
     */
    private $setting;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemValue;

    public function getId(): ?int
    {
        return $this->id;
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
