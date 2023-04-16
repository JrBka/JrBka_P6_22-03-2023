<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserListener{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }

    /**
     * This function hashes the password before it is persisted
     *
     * @param User $user
     * @return void
     */
    public function prePersist(User $user):void
    {
        $this->encodePassword($user);
    }

    /**
     * This function hashes the password before it is updated
     *
     * @param User $user
     * @return void
     */
    public function preUpdate(User $user):void
    {
        $this->encodePassword($user);
    }

    /**
     * This function hashes the password
     *
     * @param User $user
     * @return void
     */
    public function encodePassword(User $user):void
    {
        if (empty($user->getPlainPassword())){
            return;
        }else{
            $user->setPassword(
                $this->hasher->hashPassword(
                    $user,
                    $user->getPlainPassword()
                )
            );
        }
    }

}

