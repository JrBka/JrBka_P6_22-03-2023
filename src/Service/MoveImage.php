<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MoveImage extends AbstractController
{
    /**
     * This function saves the image in 'images' directory
     *
     * @param mixed $file
     * @param string $newFilename
     * @return void
     */
    public function moveImages(mixed $file, string $newFilename):void
    {
        try {
            $file->move(
                $this->getParameter('images_directory'),
                $newFilename
            );
        } catch (FileException $e) {
            echo $e->getMessage();
        }
    }
}
