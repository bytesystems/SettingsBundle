<?php

namespace Bytesystems\SettingsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[Orm\Table(
    name: "bytesystems_setting_value",
    uniqueConstraints: [
        new ORM\UniqueConstraint(
            name: "setting_owner_unique",
            columns: ["setting_key", "owner"]
        )
    ]
)]
class SettingValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'owner', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $owner = null;

    #[ORM\ManyToOne(targetEntity: 'SettingDefinition', inversedBy: 'settingValues')]
    #[ORM\JoinColumn(nullable: false, name: 'setting_key', referencedColumnName: 'setting_key')]
    protected ?\Bytesystems\SettingsBundle\Entity\SettingDefinition $setting = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $value = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $textValue = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    protected $jsonValue = [];

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

    public function getSetting(): ?SettingDefinition
    {
        return $this->setting;
    }

    public function setSetting(?SettingDefinition $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getValue(): string|array|null
    {
        if("json" === $this->setting->getDataType()) return $this->jsonValue;
        if("text" === $this->setting->getDataType()) return $this->textValue;
        return $this->value;
    }

    public function setValue(string $value): self
    {
        if("json" === $this->setting->getDataType()) {
            $this->jsonValue = $value;
            return $this;
        }
        if("text" === $this->setting->getDataType()) {
            $this->textValue = $value;
            return $this;
        }
        $this->value = $value;
        return $this;
    }

    public function setJsonValue(string $value): self
    {
        $this->jsonValue = $value;

        return $this;
    }


    public function setTextValue(string $value): self
    {
        $this->textValue = $value;

        return $this;
    }
}
