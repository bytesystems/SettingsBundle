<?php


namespace Bytesystems\SettingsBundle\Entity;


interface SettingOwnerInterface
{
    public function getUniqueIdentifierForSettings(): string;
}