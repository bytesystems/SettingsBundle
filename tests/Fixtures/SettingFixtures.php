<?php

namespace Bytesystems\SettingsBundle\Tests\Fixtures;

use Bytesystems\SettingsBundle\Entity\AllowedSettingValue;
use Bytesystems\SettingsBundle\Entity\SettingDefinition;
use Bytesystems\SettingsBundle\Entity\SettingGroup;
use Bytesystems\SettingsBundle\Entity\SettingValue;
use Bytesystems\SettingsBundle\Tests\Entity\Foo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $group = new SettingGroup();
        $group->setKey('main');
        $group->setName('First group');
        $group->setDescription('main group');
        $group->setSortOrder(0);

        $manager->persist($group);
        $manager->flush();

        $setting1 = $this->createSetting($group, 1);
        $settingValue = new SettingValue();
        $settingValue->setSetting($setting1);
        $settingValue->setValue($group->getKey().'_setting_1_foo1_value');
        $settingValue->setOwner('foo_1');
        $setting1->addSettingValue($settingValue);

        $settingValue = new SettingValue();
        $settingValue->setSetting($setting1);
        $settingValue->setValue($group->getKey().'_setting_1_foo2_value');
        $settingValue->setOwner('foo_2');
        $setting1->addSettingValue($settingValue);


        $setting2 = $this->createSetting($group, 2);
        $setting3 = $this->createSetting($group, 3);
        $setting4 = $this->createSetting($group, 4);
        $setting5 = $this->createSetting($group, 5);

        $setting1->setDataType('decimal');
        foreach ($setting1->getSettingValues() as $value) {
            $value->setValue(102.43);
        }
        $setting2->setDataType('int');
        $setting2->setValueMin(0);
        $setting2->setValueMax(100);
        foreach ($setting2->getSettingValues() as $value) {
            $value->setValue(23);
        }

        $setting3->setIsConstrained(true);

        for ($i = 0; $i < 5; $i++) {
            $asv = new AllowedSettingValue();
            $asv->setItemValue('allowed_setting_value_'.$i);
            $setting3->addAllowedSettingValue($asv);
        }

        foreach ($setting3->getSettingValues() as $value) {
            $value->setValue('allowed_setting_value_3');
        }

        $setting4->setScope(SettingDefinition::SCOPE_GLOBAL);
        $setting4->setDataType('bool');

        foreach ($setting4->getSettingValues() as $value) {
            $value->setValue(true);
        }

        $setting5->setScope(SettingDefinition::SCOPE_USER);

        foreach ($setting5->getSettingValues() as $value) {
            $value->setOwner('foo_1');
        }

        $setting6 = new SettingDefinition();
        $setting6->setName($group->getKey().'_setting_6')
            ->setSortOrder(6)
            ->setKey($group->getKey().'_setting_6')
            ->setSettingGroup($group);

        $setting7 = new SettingDefinition();
        $setting7->setName($group->getKey().'_setting_7')
            ->setSortOrder(7)
            ->setKey($group->getKey().'_setting_7')
            ->setScope(SettingDefinition::SCOPE_GLOBAL)
            ->setSettingGroup($group);

        $setting8 = new SettingDefinition();
        $setting8->setName($group->getKey().'_setting_8')
            ->setSortOrder(8)
            ->setKey($group->getKey().'_setting_8')
            ->setScope(SettingDefinition::SCOPE_USER)
            ->setSettingGroup($group);

        $setting9 = new SettingDefinition();
        $setting9->setName($group->getKey().'_setting_9')
            ->setSortOrder(9)
            ->setKey($group->getKey().'_setting_9')
            ->setScope(SettingDefinition::SCOPE_DEFAULT)
            ->setSettingGroup($group);

        $manager->persist($setting1);
        $manager->persist($setting2);
        $manager->persist($setting3);
        $manager->persist($setting4);
        $manager->persist($setting5);
        $manager->persist($setting6);
        $manager->persist($setting7);
        $manager->persist($setting8);
        $manager->persist($setting9);


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [FooFixtures::class];
    }

    private function createSetting($group,$index) : SettingDefinition
    {
        $setting = new SettingDefinition();
        $setting
            ->setName($group->getKey().'_setting_'.$index)
            ->setSortOrder($index)
            ->setKey($group->getKey().'_setting_'.$index)
            ->setSettingGroup($group);

        $settingValue = new SettingValue();
        $settingValue->setSetting($setting);
        $settingValue->setValue($group->getKey().'_setting_'.$index.'_value');
        $setting->addSettingValue($settingValue);

        return $setting;

    }
}