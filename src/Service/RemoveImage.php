<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RemoveImage extends AbstractController
{
    /**
     * This function removes the image in 'images' directory
     *
     * @param array $images
     * @return void
     */
    public function removeImages(array $images):void
    {
        if (!empty($images)){
            foreach ($images as $image){
                //Delete the images in 'images' directory
                $imageToBeDeleted = $this->getParameter('images_directory').'/'.$image;
                if (file_exists($imageToBeDeleted)){
                    unlink($imageToBeDeleted);
                }
            }
        }
    }

}
