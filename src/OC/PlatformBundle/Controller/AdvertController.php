<?php
// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Bigbrother\BigbrotherEvents;
use OC\PlatformBundle\Bigbrother\MessagePostEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
/*
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;*/

class AdvertController extends Controller
{
    public function indexAction($page)
    {
		if ($page < 1) {
			die($page);
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		$nbPerPage = 2;
		//Récupération de la liste de toutes les annonces. Méthode findAll().
		$listAdverts = $this->getDoctrine()
		->getManager()
		->getRepository('OCPlatformBundle:Advert')
		->getAdverts($page, $nbPerPage)
		;
		$nbPages = ceil(count($listAdverts)/$nbPerPage);
		if($page>$nbPages){
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
		  'listAdverts' => $listAdverts,
		  'nbPages'		=> $nbPages,
		  'page'		=> $page
		));
    }
	
    public function viewAction($id)
    {
		//Récupération de l'EntityManager
		$em = $this->getDoctrine()->getManager();
		//On récupère l'annonce correspondante à l'id $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		//Si l'id n'existe pas
		if ($advert === null) {
			throw $this->createNotFoundException("L'annonce d'id ".$id." n'existe pas.");
		}

		//On récupère la liste des AdvertSkill pour l'annonce $advert
		$listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findByAdvert($advert);
	
		return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
		'advert' => $advert,
		'listAdvertSkills' => $listAdvertSkills
		));
	}
    // On récupère tous les paramètres en arguments de la méthode
    public function viewSlugAction($slug, $year, $format)
    {
        return new Response(
            "On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$format."."
        );
    }
	
	/**
	* @Security("has_role('ROLE_AUTEUR')")
	*/
    public function addAction(Request $request)
    {
		$advert = new Advert();
		$form = $this->createForm(new AdvertType(), $advert);

		if ($form->handleRequest($request)->isValid()) {
			
			/*$event = new MessagePostEvent($advert->getContent(),$advert->getUser());
			
			$this->get('event_dispatcher')
				->dispatch(BigbrotherEvents::onMessagePost,$event);
			*/
			$em = $this->getDoctrine()->getManager();
			if ( !is_null($advert->getImage()) )
			{
				$advert->getImage()->upload();  
			}
  
			$em->persist($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

			return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
		}

		// À ce stade :
		// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - Soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
		return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
		  'form' => $form->createView(),
		));
    }
	
	public function editAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
		  throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		$form = $this->createForm(new AdvertEditType(), $advert);

		if ($form->handleRequest($request)->isValid()) {
		  // Inutile de persister ici, Doctrine connait déjà notre annonce
		  if ( !is_null($advert->getImage()) )
		  {
			$advert->getImage()->upload();  
		  }
		  $em->flush();

		  $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

		  return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
		}

		return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
		  'form'   => $form->createView(),
		  'advert' => $advert // Je passe également l'annonce à la vue si jamais elle veut l'afficher
		));
	}
	
	public function deleteAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		// On récupère l'annonce $id
		$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

		if (null === $advert) {
		  throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		// On crée un formulaire vide, qui ne contiendra que le champ CSRF
		// Cela permet de protéger la suppression d'annonce contre cette faille
		$form = $this->createFormBuilder()->getForm();

		if ($form->handleRequest($request)->isValid()) {
		  $em->remove($advert);
		  $em->flush();

		  $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

		  return $this->redirect($this->generateUrl('oc_platform_home'));
		}

		// Si la requête est en GET, on affiche une page de confirmation avant de supprimer
		return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
		  'advert' => $advert,
		  'form'   => $form->createView()
		));
	}
	
	public function menuAction($limit = 3)
	{
		$listAdverts = $this->getDoctrine()
		  ->getManager()
		  ->getRepository('OCPlatformBundle:Advert')
		  ->findBy(
			array(),                 // Pas de critère
			array('date' => 'desc'), // On trie par date décroissante
			$limit,                  // On sélectionne $limit annonces
			0                        // À partir du premier
		);

		return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
		  'listAdverts' => $listAdverts
		));
	}
	
	public function translationAction($name)
	{
		return $this->render('OCPlatformBundle:Advert:translation.html.twig',array(
			'name' => $name
		));
	}
}