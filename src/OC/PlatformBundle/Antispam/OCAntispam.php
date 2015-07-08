<?php
// src/OC/PlatformBundle/Antispam/OCAntispam.php

namespace OC\PlatformBundle\Antispam;

class OCAntispam extends \Twig_Extension
{
	protected $mailer;
	protected $locale;
	protected $nbForSpam;
	
	public function __construct(\Swift_Mailer $mailer, $nbForSpam)
	{
		$this->mailer = $mailer;
		$this->nbForSpam = (int) $nbForSpam;
	}
	
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}
	
  /**
   * Vérifie si le texte est un spam ou non
   *
   * @param string $text
   * @return bool
   */
  public function isSpam($text)
  {
    return strlen($text) < $this->nbForSpam;
  }
  
  public function getFunctions()
  {
	  return array(
		'checkIfSpam' => new \Twig_Function_Method($this,'isSpam')
	  );
  }
  public function getName()
  {
	  return 'OCAntispam';
  }
}