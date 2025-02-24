<?php

namespace App\Tests\Entity;

use App\Entity\File;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $file = new File();

        $name = 'document.pdf';
        $createdAt = new \DateTimeImmutable();
        $user = new User();

        $file->setName($name);
        $file->setCreatedAt($createdAt);
        $file->setUser($user);

        $this->assertEquals($name, $file->getName());
        $this->assertEquals($createdAt, $file->getCreatedAt());
        $this->assertEquals($user, $file->getUser());
    }
}
