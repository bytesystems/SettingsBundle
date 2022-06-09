<?php

namespace Bytesystems\SettingsBundle\Model;

use Bytesystems\SettingsBundle\Entity\SettingDefinition;

class Setting
{
    const SCOPE_GLOBAL = 'global';
    const SCOPE_USER = 'user';
    const SCOPE_DEFAULT = 'default';

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