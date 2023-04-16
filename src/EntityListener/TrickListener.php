<?php

namespace App\EntityListener;

use App\Entity\Trick;

class TrickListener
{
    public function preUpdate(Trick $trick):Trick
    {
        return $trick->setUpdatedAt();
    }
}

