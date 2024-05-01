<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $PasswordHasher;

    public function __construct(UserPasswordHasherInterface $PasswordHasher) {
        $this->PasswordHasher = $PasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $plainPassword = "admin";
        $hashedPassword = $this->PasswordHasher->hashPassword($user,$plainPassword);
        $user->setPassword($hashedPassword);
        $user->setUsername('admin');
        $user->setRoles(['ROLE_ADMIN']);
        
        $manager->persist($user);

        $manager->flush();
    }
}
