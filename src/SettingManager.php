<?php


namespace Bytesystems\SettingsBundle;


use Bytesystems\SettingsBundle\Entity\AllowedSettingValue;
use Bytesystems\SettingsBundle\Entity\SettingDefinition;
use Bytesystems\SettingsBundle\Entity\SettingOwnerInterface;
use Bytesystems\SettingsBundle\Entity\SettingValue;
use Bytesystems\SettingsBundle\Model\Setting;
use Bytesystems\SettingsBundle\Repository\SettingValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class SettingManager
{
    const GLOBAL_KEY = 'global';
    const VALUE_WITHOUT_OWNER = 'setting_value_without_owner';

    /**
     * @var array
     */
    protected $settings;

    protected $settingCollection;

    /**
     * @var EntityManagerInterface
     */
    protected $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function set(string $key, $value, SettingOwnerInterface|string|null $owner = null): void
    {
        $ownerKey = $owner instanceof SettingOwnerInterface ? $owner->getUniqueIdentifierForSettings() : $owner;
        $this->loadAndValidateSetting($key, $ownerKey);

        $qb = $this->em->createQueryBuilder()
            ->select('s','_sv','_asv')
            ->from(SettingDefinition::class,'s')
            ->leftJoin('s.settingValues','_sv')
            ->leftJoin('s.allowedSettingValues','_asv');

        $qb->where($qb->expr()->eq('s.key',':key'))
            ->setParameter('key',$key);

        /* @var \Bytesystems\SettingsBundle\Entity\SettingDefinition */
        $setting = $qb->getQuery()->getOneOrNullResult();

        $values = $setting->getSettingValues()->filter(function ($sv) use ($ownerKey) {
            return $sv->getOwner() === $ownerKey;
        });

        if($setting->getIsConstrained())
        {
            $allowedValues = array_map(fn($asv) => $asv->getItemValue(), $setting->getAllowedSettingValues()->toArray());
            if(!array_search($value,$allowedValues))
            {
                throw(new \InvalidArgumentException(sprintf('Value %s not allowed in constrained setting: %s', $value, $key)));
            }

        }

        $filter = match ($this->settings[$key]['dataType']) {
            'int' => FILTER_VALIDATE_INT,
            'decimal', 'float', 'percent', 'currency' => FILTER_VALIDATE_FLOAT,
            'bool' => FILTER_VALIDATE_BOOL,
            default => ''
        };

        $validate = match ($this->settings[$key]['dataType']) {
            'int', 'decimal', 'float', 'percent', 'currency' => 'numeric',
            'bool' => 'bool',
            default => ''
        };

        if('numeric' === $validate) {
            if(!filter_var($value, $filter)) {
                throw(new \InvalidArgumentException(sprintf('Value %s not allowed for %s(%s)', $value, $key, $setting->getDataType())));
            }

            if(is_numeric($setting->getValueMin())) {
                $options = [
                    'options' => [
                        'min_range' => $setting->getValueMin(),
                    ]
                ];
                if (!filter_var($value, $filter,$options)) {
                    throw(new \InvalidArgumentException(sprintf('Value %s out of range for %s(%s), min: %s', $value, $key, $setting->getDataType(),$setting->getValueMin())));
                }
            }

            if(is_numeric($setting->getValueMax())) {
                $options = [
                    'options' => [
                        'max_range' => $setting->getValueMax(),
                    ]
                ];
                if (!filter_var($value, $filter,$options)) {
                    throw(new \InvalidArgumentException(sprintf('Value %s out of range for %s(%s), max: %s', $value, $key, $setting->getDataType(),$setting->getValueMax())));
                }
            }

        }

        if('bool' === $validate) {
            $orig = $value;
            $value = filter_var($value, $filter, FILTER_NULL_ON_FAILURE);
            if(null === $value) {
                throw(new \InvalidArgumentException(sprintf('Value %s not allowed for %s(%s)', $orig, $key, $setting->getDataType())));
            }
        }

        if(0 === count($values) )
        {
            $settingValue = new SettingValue();
            $settingValue->setOwner($ownerKey);
            $settingValue->setSetting($setting);
            $settingValue->setValue($value);
            $setting->addSettingValue($settingValue);
            $this->em->persist($settingValue);
        }
        else
        {
            $settingValue = $values->first();
            $settingValue->setValue($value);
        }

        $this->em->flush();
        $this->loadSettings(true);
    }

    public function get(string $key, SettingOwnerInterface|string|null $owner = null, $default = null)
    {
        $ownerKey = $owner instanceof SettingOwnerInterface ? $owner->getUniqueIdentifierForSettings() : $owner;
        $this->loadAndValidateSetting($key, $ownerKey);
        $scope = $this->settings[$key]['scope'] ?? SettingDefinition::SCOPE_GLOBAL;
        $value = null;

        switch ($scope) {
            case SettingDefinition::SCOPE_GLOBAL:
                $value = $this->settings[$key]['values'][self::VALUE_WITHOUT_OWNER] ?? $value;
//                $value = $this->settingValues[self::GLOBAL_KEY][$key] ?? null;
                break;
            case SettingDefinition::SCOPE_DEFAULT:
                // Try to get the global value, but don't break
                $value = $this->settings[$key]['values'][self::VALUE_WITHOUT_OWNER] ?? $value;
//                $value = $this->settingValues[self::GLOBAL_KEY][$key] ?? null;
                // break;
            case SettingDefinition::SCOPE_USER:
                if (null !== $ownerKey) {
                    $value = $this->settings[$key]['values'][$ownerKey] ?? $value;
                }
                break;
        }

        return match ($this->settings[$key]['dataType']) {
            'int' => (int)(null === $value ? $default : $value),
            'decimal', 'float', 'percent', 'currency' => (float)(null === $value ? $default : $value),
            'bool' => (boolean)(null === $value ? $default : $value),
            default => null === $value ? $default : $value,
        };

    }


    public function all(SettingOwnerInterface|string|null $owner = null): array
    {
        $this->loadSettings();
        $ownerKey = $owner instanceof SettingOwnerInterface ? $owner->getUniqueIdentifierForSettings() : $owner;
        $all = [];

        foreach ($this->settings as $key => $setting) {
            $scope = $setting['scope'] ?? SettingDefinition::SCOPE_GLOBAL;
            if($ownerKey && SettingDefinition::SCOPE_GLOBAL === $scope ) continue;
            if(!$ownerKey && SettingDefinition::SCOPE_USER === $scope) continue;
            $all[$key] = $this->get($key,$owner);
        }

        return $all;
    }

    public function config(): array
    {
        $this->loadSettings();

        return array_map(function($setting) {
            $new_setting = $setting;
            unset($new_setting['values']);
            return $new_setting;
        },$this->settings);
    }

    private function loadSettings($reload = false): void
    {
        if(!$reload && null !== $this->settings) return;

        $qb = $this->em->createQueryBuilder()
            ->select('s','_sv','_asv')
            ->from(SettingDefinition::class,'s')
            ->leftJoin('s.settingValues','_sv')
            ->leftJoin('s.allowedSettingValues','_asv');

        $settings = array_map(fn($item) => (array)$this->getModel($item),$qb->getQuery()->getResult());

        foreach ($settings as $setting) {
            $key = $setting['key'];
            unset($setting['key']);
            $this->settings[$key] = $setting;
        }

    }

    private function getModel(SettingDefinition $item): Setting
    {
        $setting = new Setting();

        $setting->key = $item->getKey();
        $setting->scope = $item->getScope();
        $setting->dataType = $item->getDataType();
        $setting->description = $item->getDescription();
        $setting->name = $item->getName();
        $setting->isConstrained = $item->getIsConstrained();
        $setting->valueMin = $item->getValueMin();
        $setting->valueMax = $item->getValueMax();

        $setting->allowedValues = [];
        foreach ($item->getAllowedSettingValues() as $allowedSettingValue) {
            $setting->allowedValues[] = $allowedSettingValue->getItemValue();
        }

        $setting->values = [];
        foreach ($item->getSettingValues() as $settingValue) {
            $key = null === $settingValue->getOwner() ? self::VALUE_WITHOUT_OWNER : $settingValue->getOwner();

            $setting->values[$key] = $settingValue->getValue();
        }

        return $setting;

    }

    public function loadAndValidateSetting(string $key, string|null $owner = null): void
    {
        $this->loadSettings();

        if (!array_key_exists($key, $this->settings)) {
            throw(new \InvalidArgumentException(sprintf('Setting does not exist: %s', $key)));
        }

        $scope = $this->settings[$key]['scope'] ?? SettingDefinition::SCOPE_GLOBAL;

        if ((SettingDefinition::SCOPE_GLOBAL === $scope && null !== $owner)) {
            throw(new \InvalidArgumentException(sprintf('No owner allowed for setting %s, the setting is only allowed in scope: %s', $key, $scope)));
        }

        if ((SettingDefinition::SCOPE_USER === $scope && null === $owner)) {
            throw(new \InvalidArgumentException(sprintf('Setting %s not allowed in scope: %s', $key, $scope)));
        }
    }

    private function loadSettingValues(SettingOwnerInterface|string|null $owner = null)
    {
        if(null !== $this->settingValues && null === $owner)  return;
        if(null !== $owner && array_key_exists($owner->getUniqueIdentifierForSettings(),$this->settingValues ?? [])) return;

        $qb = $this->em->createQueryBuilder()
            ->select(['sv.owner','sv.value','IDENTITY(sv.setting) as key'])
            ->from(SettingValue::class,'sv')
            ->where('sv.owner is null');


        if(null !== $owner)
        {
            $qb->orWhere($qb->expr()->eq('sv.owner',':owner'))->setParameter('owner',$owner->getUniqueIdentifierForSettings());
        }

        $values = $qb->getQuery()->getArrayResult();

        foreach ($values as $value) {
            $key = $value['key'];
            $scope = null === $value['owner'] ? self::GLOBAL_KEY : $value['owner'];
            $this->settingValues[$scope][$key] = $value['value'];
        }
    }

}