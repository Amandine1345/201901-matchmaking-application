<?php

namespace App\Service;

use App\Entity\Society;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserCreate
 * Class for created an user by command
 * @package App\Service
 */
class UserCreate
{
    /**
     * SUPER ADMIN ROLE
     */
    const SUPER_ADMIN = ["ROLE_ADMIN"];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserCreate constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $email
     * @param string $password
     */
    public function create(string $email, string $password, string $societyName)
    {
        $society = new Society();
        $society->setName($societyName);
        $this->em->persist($society);
        $this->em->flush();

        if ($society->getId()) {
            $user = new User();

            $user->setSociety($society);
            $user->setFirstname('Admin');
            $user->setLastname('Admin');
            $user->setRoles(self::SUPER_ADMIN);
            $user->setEmail($email);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setActivated(1);

            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
