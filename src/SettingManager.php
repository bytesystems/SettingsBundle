<?php


namespace Bytesystems\SettingsBundle;


use Bytesystems\SettingsBundle\Entity\Setting;
use Bytesystems\SettingsBundle\Entity\SettingOwnerInterface;
use Bytesystems\SettingsBundle\Entity\SettingValue;
use Bytesystems\SettingsBundle\Repository\SettingValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class SettingManager
{
    const GLOBAL_KEY = 'global';

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $settingValues;

    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var SettingValueRepository
     */
    private $settingValueRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->settingValueRepository = $this->em->getRepository(SettingValue::class);
    }

    public function setWithoutFlush(string $key, $value, ?SettingOwnerInterface $owner = null)
    {

    }

    public function set(string $key, $value, ?SettingOwnerInterface $owner = null)
    {
        $this->loadSettingDefinition();

        if(!array_key_exists($key,$this->settings))
        {
            throw(new \InvalidArgumentException(sprintf('Setting does not exist: %s',$key)));
        }

        $scope = $this->settings[$key]['scope'] ?? Setting::SCOPE_GLOBAL;

        if((Setting::SCOPE_GLOBAL === $scope && null !== $owner))
        {
            throw(new \InvalidArgumentException(sprintf('No owner allowed for setting %s, the setting is only allowed in scope: %s',$key,$scope)));
        }

        if((Setting::SCOPE_USER === $scope && null === $owner))
        {
            throw(new \InvalidArgumentException(sprintf('Setting %s not allowed in scope: %s',$key,$scope)));
        }

        $ownerKey = null === $owner ? self::GLOBAL_KEY : $owner->getUniqueIdentifierForSettings();
        $this->settingValues[$ownerKey][$key] = $value;
        $setting = $this->settingValueRepository->findValueEntry($key,self::GLOBAL_KEY === $ownerKey ? null : $ownerKey);

        if( null === $setting)
        {
            $setting = new SettingValue();
            $setting->setSetting($this->em->getReference(Setting::class,$key));
            $setting->setOwner(self::GLOBAL_KEY === $ownerKey ? null : $ownerKey);
            $this->em->persist($setting);
        }
        $setting->setValue($value);

        $this->em->flush();
    }

    public function get(string $key, ?SettingOwnerInterface $owner = null, $default = null)
    {
        $this->loadSettingDefinition();
        $this->loadSettingValues($owner);

        if(!array_key_exists($key,$this->settings))
        {
            throw(new \InvalidArgumentException(sprintf('Setting does not exist: %s',$key)));
        }

        $scope = $this->settings[$key]['scope'] ?? Setting::SCOPE_GLOBAL;

        if((Setting::SCOPE_GLOBAL === $scope && null !== $owner))
        {
            throw(new \InvalidArgumentException(sprintf('No owner allowed for setting %s, the setting is only allowed in scope: %s',$key,$scope)));
        }

        if((Setting::SCOPE_USER === $scope && null === $owner))
        {
            throw(new \InvalidArgumentException(sprintf('Setting %s not allowed in scope: %s',$key,$scope)));
        }

        $value = null;

        switch ($scope) {
            case Setting::SCOPE_GLOBAL:
                $value = $this->settingValues[self::GLOBAL_KEY][$key] ?? null;
                break;
            case Setting::SCOPE_DEFAULT:
                // Try to get the global value, but don't break
                $value = $this->settingValues[self::GLOBAL_KEY][$key] ?? null;
                // break;
            case Setting::SCOPE_USER:
                if (null !== $owner) {
                    $value = $this->settingValues[$owner->getUniqueIdentifierForSettings()][$key] ?? $value;
                }
                break;
        }
        return null === $value ? $default : $value;
    }

    private function loadSettingDefinition()
    {
        if(null !== $this->settings) return;

        $qb = $this->em->createQueryBuilder()
            ->select('s')
            ->from(Setting::class,'s');

        $settings = $qb->getQuery()->getArrayResult();

        foreach ($settings as $setting) {
            $key = $setting['key'];
            unset($setting['key']);
            $this->settings[$key] = $setting;
        }
    }

    private function loadSettingValues(?SettingOwnerInterface $owner = null)
    {
        if(null !== $this->settingValues && null === $owner) return;
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