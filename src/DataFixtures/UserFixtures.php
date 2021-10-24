<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface  $passEnc;
    public function __construct(UserPasswordEncoderInterface $passEnc)
    {
        $this->passEnc = $passEnc;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setBio("oh my gott");
        $user->setName("Jonh");
        $user->setSurname("Lech");
        $user->setEmail("adsa@gmail.com");
        $user->setGender("male");
        $user->setDateOfBirth(new DateTime("2002-12-12"));
        $user->setTag("@liechu");
        $user->setPassword($this->passEnc->encodePassword($user, "StrongPassword"));
        $user->setRoles([]);
        $user->setVerified(false);
        $user->setProfilePicUrl("https://res.cloudinary.com/faceprism/image/upload/v1626432519/profile_pics/default_bbdyw0.png");
        $manager->persist($user);
        $manager->flush();
    }
}
