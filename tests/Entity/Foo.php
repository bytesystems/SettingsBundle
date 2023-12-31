<?php

namespace Bytesystems\SettingsBundle\Tests\Entity;

use Bytesystems\SettingsBundle\Entity\SettingOwnerInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Foo
 * @package Bytesystems\SettingsBundle\Tests\Entity
 */
#[ORM\Table]
#[ORM\Entity]
class Foo implements SettingOwnerInterface
{
    /**
     *
     * @var integer
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    public function getUniqueIdentifierForSettings(): string
    {
        return sprintf("foo_%d", $this->id);
    }
}