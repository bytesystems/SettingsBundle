<?php

namespace Bytesystems\SettingsBundle\Model;

use Bytesystems\SettingsBundle\Entity\SettingDefinition;

class Setting
{
    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_USER = 'user';
    public const SCOPE_DEFAULT = 'default';

    public $key;
    public $scope;
    public $name;
    public $description;
    public $isConstrained;
    public $dataType;
    public $valueMin;
    public $valueMax;

    public $allowedValues;
    public $values;
}