<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadSkill.php_egg_logo_guid

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Skill;

class LoadSkill implements FixtureInterface
{
	public function load(ObjectManager $manager)
	{
		//Liste des noms de compétences à ajouter
		$names = array('PHP','Symfony2','C++','Java','Photoshop','Blender','Bloc-note');
		
		foreach ($names as $name) {
			//On crée la compétences
			$skill = new Skill();
			$skill->setName($name);
			
			//On la persiste
			$manager->persist($skill);
		}
		//On déclenche l'enregistrement de toutes les catégories
		$manager->flush();
	}
}