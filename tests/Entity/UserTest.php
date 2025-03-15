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
        $roles = ['ROLE_USER'];  // Rôle standard

        // Simulation d'un mot de passe haché (ex: bcrypt)
        $hashedPassword = password_hash('test_password', PASSWORD_BCRYPT);

        $user->setEmail($email);
        $user->setPassword($hashedPassword);
        $user->setRoles($roles);

        // Vérification des getters
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($hashedPassword, $user->getPassword()); // Comparaison avec le hash
        $this->assertContains('ROLE_USER', $user->getRoles());  // Vérifier si ROLE_USER est dans le tableau de rôles
    }
}
