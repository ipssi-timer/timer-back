<?php
namespace App\DataFixtures;

use App\Entity\Entry;
use App\Entity\GroupUsers;
use App\Entity\Project;
use App\Entity\user;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Faker;

class FakerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $faker = Faker\Factory::create('fr_FR');

        // on créé 10 users
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setPassword($faker->password);
            $user->setRoles('ROLE_USER');
            $user->setPseudo($faker->userName);
            $user->setEmail($faker->email);
            $user->setBirthDate($faker->dateTime);
            $manager->persist($user);
            $manager->flush();


            $group = new GroupUsers();
            $group->setName($faker->colorName);
            $group->setCreatorId($user->getId());
            $group->addUser($user);
            $manager->persist($group);
            $manager->flush();



            $project = new Project();
            $project->setName($faker->city);
            $project->setDescription($faker->title);
            $project->setCreator($user->getId());
            $group->addProject($project);
            $manager->persist($project);
            $manager->flush();

            $entry = new Entry();
            $entry->setStartsAt($faker->dateTime);
            $entry->setEndsAt($faker->dateTime);
            $user->attachEntry($entry);
            $project->addEntry($entry);

            $manager->persist($entry);
            $manager->flush();


        }

        $manager->flush();
    }
}