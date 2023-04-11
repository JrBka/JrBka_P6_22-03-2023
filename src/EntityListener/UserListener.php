<?php

namespace App\EntityListener;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Flex\Response;

class UserListener{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }

    public function prePersist(User $user):void
    {
        $this->encodePassword($user);
    }

    public function preUpdate(User $user):void
    {
        $this->encodePassword($user);
    }

    /**
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