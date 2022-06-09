<?php

namespace Bytesystems\SettingsBundle\Tests\Fixtures;

use Bytesystems\SettingsBundle\Tests\Entity\Foo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FooFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $foo = new Foo();
            $manager->persist($foo);
            $this->addReference('FOO_OBJ_'.$i, $foo);
        }
        $manager->flush();
    }
}