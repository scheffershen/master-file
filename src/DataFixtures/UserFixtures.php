<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\UserManagement\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * php bin/console doctrine:database:create --env=test
 * php bin/console doctrine:schema:update --force --env=test
 * php bin/console doctrine:fixtures:load --env=test --group TEST
 *
 * @codeCoverageIgnore
 */
final class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const ADMIN = 'admin';
    public const SUPER_CONSULTANT = 'super-consultant';
    public const CONSULTANT = 'consultant';
    public const UTILISATEUR = 'utilisateur';
    public const ADMIN_PASSWORD = 'admin2020!';
    
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUser($manager);
    }

    public function loadUser(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$firstName, $lastName, $username, $password, $phone, $email, $roles, $reference]) {
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setUsername($username);
            $user->setPassword($this->passwordEncoder->encodePassword($user,$password));
            $user->setFixe($phone);
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setIsEnable(true);
            $user->setMailAlerte(true);
            $user->setPvUser(false);
            $manager->persist($user);
            $this->addReference($reference, $user);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['DEV', 'PROD', 'TEST'];
    }

    private function getUserData(): array
    {
        return [
            ['firstname', 'lastname', 'admin', 'admin2020!', '0(0)123456789', 'info@localhost', ['ROLE_SUPER_ADMIN'], self::ADMIN_USER_REFERENCE],
        ];
    }
}
