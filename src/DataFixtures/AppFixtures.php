<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * This function load initial data
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {

        $comments = [];
        for ($j = 0; $j < 25; $j++){
            $comment = new Comment();
            $comment->setUserId('12')
                ->setContent('Si l’utilisateur est authentifié, il peut voir un formulaire au-dessus de la liste avec
                 un champs “message” qui est obligatoire. L’utilisateur peut poster autant de messages qu’il le souhaite.');
            $comments [] = $comment;
            $manager->persist($comment);
        }


        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Mute');
        $trick->setDescription('saisie de la carre frontside de la planche entre les deux pieds avec la main avant');
        $trick->setPicture(['mute.jpg']);
        for ($j = 0; $j < 25; $j++){
            $trick->addComment($comments[mt_rand(1, count($comments)-1)]);
        }

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Sad');
        $trick->setDescription('saisie de la carre backside de la planche, entre les deux pieds, avec la main avant');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Indy');
        $trick->setDescription('saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Stalefish');
        $trick->setDescription('saisie de la carre backside de la planche entre les deux pieds avec la main arrière');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Tail grab');
        $trick->setDescription('saisie de la partie arrière de la planche, avec la main arrière');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Nose grab');
        $trick->setDescription('saisie de la partie avant de la planche, avec la main avant');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Japan air');
        $trick->setDescription('saisie de l\'avant de la planche, avec la main avant, du côté de la carre frontside');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Grabs');
        $trick->setName('Seat belt');
        $trick->setDescription('saisie du carre frontside à l\'arrière avec la main avant');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Flips');
        $trick->setName('Front flip');
        $trick->setDescription('rotation en avant');

        $manager->persist($trick);

        $trick = new Trick();
        $trick->setTricksGroup('Flips');
        $trick->setName('Back flip');
        $trick->setDescription('rotation en arrière');

        $manager->persist($trick);


        $manager->flush();
    }



}
