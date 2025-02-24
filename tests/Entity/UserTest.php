<?php
// tests/Entity/UserTest.php
namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $user = new User();

        $email = 'test@test.com';
        $password = 'password123';
        $roles = ['ROLE_USER'];

        $user->setEmail($email);
        $user->setPassword($password);
        $user->setRoles($roles);

        // Vérification des getters
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($roles, $user->getRoles());
    }
}
