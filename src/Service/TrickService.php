<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TrickService
{
    public function processMedia(Trick $trick, string $imageDirectory, ?UploadedFile $mainImage, ?array $multipleImages, ?string $urlVideo): void
    {   
        // Traitement de l'image principale
        if ($mainImage !== null) {
            $fileMainImg = md5(uniqid()) . '.' . $mainImage->guessExtension();
            $mainImage->move($imageDirectory, $fileMainImg);
            $mainImageEntity = new Image;
            $mainImageEntity->setName($fileMainImg);
            $mainImageEntity->setIsMain(true);
            $trick->addImage($mainImageEntity);
        }

        // Traitement des images secondaires
        if ($multipleImages !== null) {
            foreach ($multipleImages as $image) {
                $file = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move($imageDirectory, $file);

                $imageEntity = new Image;
                $imageEntity->setName($file);
                $imageEntity->setIsMain(false);
                $trick->addImage($imageEntity);
            }
        }

        // On ajoute la vidéo
        if ($urlVideo !== null) {
            $videoEntity = new Video;
            $videoEntity->setLink($urlVideo);
            $trick->addVideo($videoEntity);
        }
    }

    public function updateMainImage(Trick $trick, ?UploadedFile $mainImage, string $imageDirectory, ImageRepository $imageRepository): void
    {
        if ($mainImage !== null) {

            // // On supprimer l'image principale actuelle
            $imageCollection = $trick->getImages();
            if (!empty($imageCollection)) {
                foreach ($imageCollection as $img) {
                    if ($img->getIsMain()) {
                        $nameImageBool = file_exists($imageDirectory . $img->getName());

                        if ($nameImageBool !== false) {
                            unlink($imageDirectory . $img->getName());
                        }

                        $imageRepository->remove($img, true);
                    }
                }
            }
        }
    }
}
