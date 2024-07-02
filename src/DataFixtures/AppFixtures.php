<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;           
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setEmail("user@mail.ru");
        $admin->setBalance(1000000.0);
        $password = $this->hasher->hashPassword($admin, 'password');
        $admin->setPassword($password);

        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setEmail("admin@mail.ru");
        $user->setBalance(100.0);
        $password = $this->hasher->hashPassword($user, 'password');
        $user->setPassword($password);
        
        $manager->persist($user);
        $manager->persist($admin);
        $manager->flush();
  
    }
}
