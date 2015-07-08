<?php 
// src/OC/PlatformBundle/Entity/Image.php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 *
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="OC\PlatformBundle\Entity\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
    * @var integer
    *
	* @ORM\Column(name="id",type="integer")
	* @ORM\Id
	* @ORM\GeneratedValue(strategy="AUTO")
	*/
	private $id;
	
    /**
    * @var string
    *
	* @ORM\Column(name="alt",type="string",length=255)
	*/
	private $alt;
	/**
	* Image file
	* 
	* @var File
	* 
	* @Assert\File(maxSize = "5M")
	*/
	protected $file;
	
	/**
	* Image path
	* 
	* @var string
	* 
	* @ORM\Column(type="text",length=255,nullable=false)
	*/
	protected $path;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set alt
     *
     * @param string $alt
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string 
     */
    public function getAlt()
    {
        return $this->alt;
    }

	/**
    * Set file
    *
    * @param UploadedFile $file
    * @return Image
    */
	public function setFile(UploadedFile $file)
	{
		$this->file = $file;
		return $this;
	}


    /**
     * Set path
     *
     * @param string $path
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }
	
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/documents';
    }
	
	public function upload()
	{
		// la propriété « file » peut être vide si le champ n'est pas requis
		if (null === $this->file) {
			return;
		}

		// utilisez le nom de fichier original ici mais
		// vous devriez « l'assainir » pour au moins éviter
		// quelconques problèmes de sécurité

		// la méthode « move » prend comme arguments le répertoire cible et
		// le nom de fichier cible où le fichier doit être déplacé
		$this->file->move($this->getUploadRootDir(), $this->file->getClientOriginalName());

		// définit la propriété « path » comme étant le nom de fichier où vous
		// avez stocké le fichier
		$this->path = $this->file->getClientOriginalName();
		$this->alt = 'job de rêve';

		// « nettoie » la propriété « file » comme vous n'en aurez plus besoin
		$this->file = null;
	}
}
