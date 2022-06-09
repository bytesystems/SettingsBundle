<?php

namespace Bytesystems\SettingsBundle\Tests\Service;

use Bytesystems\SettingsBundle\Entity\SettingDefinition;
use Bytesystems\SettingsBundle\SettingManager;
use Bytesystems\SettingsBundle\Tests\Entity\Foo;
use Bytesystems\SettingsBundle\Tests\Fixtures\FooFixtures;
use Bytesystems\SettingsBundle\Tests\Fixtures\SettingFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class SettingsBundleTest extends KernelTestCase
{


    /* @var EntityManagerInterface */
    private $entityManager;

    /* @var SettingManager */
    private $sm;

    protected function setUp():void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $this->databaseTool = self::$kernel->getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->sm = self::$kernel->getContainer()
            ->get('bytesystems_settings.setting_manager');
    }

    public function testGetSettingFromDatabase()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $qb = $this->entityManager->createQueryBuilder();
        /* @var Foo */
        $foo = $qb->select('f')
            ->from(Foo::class,'f')
            ->andWhere($qb->expr()->eq('f.id',$qb->expr()->literal(1)))->getQuery()->getOneOrNullResult();

        $this->assertEquals($foo->getUniqueIdentifierForSettings(),'foo_1');


    }

    public function testManager() {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $config = $this->sm->config();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('main_setting_1',$config);
        $this->assertArrayNotHasKey('values',$config['main_setting_1']);

        $all = $this->sm->all();
        $this->assertIsArray($all);
        $this->assertArrayHasKey('main_setting_1',$all);
        $this->assertEquals(102.43,$all['main_setting_1']);
        $this->assertArrayNotHasKey('main_setting_5',$all);

        $all = $this->sm->all('foo_1');
        $this->assertIsArray($all);
        $this->assertArrayHasKey('main_setting_1',$all);
        $this->assertEquals(102.43,$all['main_setting_1']);
        $this->assertArrayNotHasKey('main_setting_7',$all);

    }

    public function testGetDefaultSetting() {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $qb = $this->entityManager->createQueryBuilder();
        /* @var Foo */
        $foo = $qb->select('f')
            ->from(Foo::class,'f')
            ->andWhere($qb->expr()->eq('f.id',$qb->expr()->literal(2)))->getQuery()->getOneOrNullResult();

        $this->assertEquals(102.43, $this->sm->get('main_setting_1'));
        $this->assertEquals(102.43, $this->sm->get('main_setting_1','foo_1'));
        $this->assertEquals(102.43, $this->sm->get('main_setting_1',$foo));
        $this->assertEquals(102.43, $this->sm->get('main_setting_1','foo_3'));
        $this->assertEquals(102.43, $this->sm->get('main_setting_1','foo_300'));

        $this->assertEquals("default value", $this->sm->get('main_setting_9','foo_300',"default value"));

        $this->expectException(\InvalidArgumentException::class);
        $this->sm->get('main_setting_100','foo_3');

    }

    public function testGetGlobalSetting() {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $this->assertEquals(true, $this->sm->get('main_setting_4'));
        $this->expectException(\InvalidArgumentException::class);
        $this->sm->get('main_setting_4','foo_1');
    }

    public function testGetUserSetting() {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $this->assertEquals('main_setting_5_value', $this->sm->get('main_setting_5','foo_1'));
        $this->expectException(\InvalidArgumentException::class);
        $this->sm->get('main_setting_5');

    }

    public function testGetWithoutValue() {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );


        $this->assertEquals('default_value', $this->sm->get('main_setting_6',null,'default_value'));
        $this->assertEquals('default_value', $this->sm->get('main_setting_6','foo_1','default_value'));
        $this->assertEquals('default_value', $this->sm->get('main_setting_7',null,'default_value'));
        $this->assertEquals('default_value', $this->sm->get('main_setting_8','foo_1','default_value'));

        $this->expectException(\InvalidArgumentException::class);
        $this->assertEquals('default_value', $this->sm->get('main_setting_8',null,'default_value'));

        $this->expectException(\InvalidArgumentException::class);
        $this->assertEquals('default_value', $this->sm->get('main_setting_7','foo_1','default_value'));

    }

    public function testSetValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $this->sm->set('main_setting_9','main_setting_9_value');
        $this->assertEquals('main_setting_9_value',$this->sm->get('main_setting_9'));

        $this->sm->set('main_setting_9','main_setting_9_foo_5_value','foo_5');
        $this->assertEquals('main_setting_9_value',$this->sm->get('main_setting_9'));
        $this->assertEquals('main_setting_9_foo_5_value',$this->sm->get('main_setting_9','foo_5'));

        $this->sm->set('main_setting_9','main_setting_9_foo_5_new_value','foo_5');
        $this->assertEquals('main_setting_9_foo_5_new_value',$this->sm->get('main_setting_9','foo_5'));

        $this->expectException(\InvalidArgumentException::class);
        $this->sm->set('main_setting_10','main_setting_10_value');
    }

    public function testSetConstrainedValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value main_setting_3_value not allowed in constrained setting: main_setting_3');
        $this->sm->set('main_setting_3','main_setting_3_value');
    }

    public function testSetIntValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );
        $this->sm->set('main_setting_2','10');
        $this->assertEquals('10',$this->sm->get('main_setting_2'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value 101.23 not allowed for main_setting_2(int)');
        try {
            $this->sm->set('main_setting_2','101.23');
        } finally
        {
            $this->assertEquals('10',$this->sm->get('main_setting_2'));
        }
    }

    public function testSetDecimalValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );


        $this->sm->set('main_setting_1','101.23');
        $this->assertEquals('101.23',$this->sm->get('main_setting_1'));
        $this->sm->set('main_setting_1','5487');
        $this->assertEquals('5487',$this->sm->get('main_setting_1'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value 101.23.23 not allowed for main_setting_1(decimal)');
        try {
            $this->sm->set('main_setting_1','101.23.23');
        } finally {
            $this->assertEquals('5487',$this->sm->get('main_setting_1'));
        }

    }

    public function testSetBoolValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $this->sm->set('main_setting_4',true);
        $this->assertEquals(true,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"true");
        $this->assertEquals(true,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',1);
        $this->assertEquals(true,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"1");
        $this->assertEquals(true,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"yes");
        $this->assertEquals(true,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"on");
        $this->assertEquals(true,$this->sm->get('main_setting_4'));

        $this->sm->set('main_setting_4',"false");
        $this->assertEquals(false,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"0");
        $this->assertEquals(false,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',0);
        $this->assertEquals(false,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"no");
        $this->assertEquals(false,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"");
        $this->assertEquals(false,$this->sm->get('main_setting_4'));
        $this->sm->set('main_setting_4',"off");
        $this->assertEquals(false,$this->sm->get('main_setting_4'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value 101.23.23 not allowed for main_setting_4(bool)');
        try {
            $this->sm->set('main_setting_4','101.23.23');
        } finally {
            $this->assertEquals(false,$this->sm->get('main_setting_4'));
        }

    }

    public function testSetMinValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $this->sm->set('main_setting_2','50');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value -10 out of range for main_setting_2(int), min: 0');
        try {
            $this->sm->set('main_setting_2','-10');
        } finally {
            $this->assertEquals(50,$this->sm->get('main_setting_2'));
        }
    }

    public function testSetMaxValue()
    {
        $this->databaseTool->loadFixtures(
            [
                FooFixtures::class,
                SettingFixtures::class
            ]
        );

        $this->sm->set('main_setting_2','100');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value 101 out of range for main_setting_2(int), max: 100');
        try {
            $this->sm->set('main_setting_2','101');
        } finally {
            $this->assertEquals(100,$this->sm->get('main_setting_2'));
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
