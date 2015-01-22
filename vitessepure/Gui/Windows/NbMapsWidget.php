<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;

/**
 * \class NbMapsWidget
 * \brief Affiche le nombre de maps jouées ainsi que du nombre de maps total
 */
class NbMapsWidget extends \ManiaLive\Gui\Windowing\Window
{
	static private $x = 53;
	static private $y = 23.9;

	private $background;		///< Arrière-plan
	private $nbMapsLabel;		///< Texte
	private $headerLabel;		///< Titre
	private $nbMaps = 0;		///< Nombre de maps jouées
	private $nbMapsTotal = 0;	///< Nombre total de maps
	private $icon;				///< Icône

	/**
	 * \brief Initialisation
	 */
	function initializeComponents()
	{
		$this->setSize(12, 8);
		$this->setPosition(self::$x, self::$y);
		
		$this->background = new BgsPlayerCard();
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->background->setSubStyle(BgsPlayerCard::BgPlayerCardBig);

		$this->header = new Bgs1InRace();
		$this->header->setSubStyle('BgTitle3_1');
		$this->header->setSize(14, 2.6);
		$this->header->setPosition(-0.3, 0);

		$this->headerLabel = new Label();
		$this->headerLabel->setTextSize(1);
		$this->headerLabel->setHalign('center');
		$this->headerLabel->setPosition(6, 0.6);
		$this->headerLabel->setText('$000Maps');

		$this->nbMapsLabel = new Label();
		$this->nbMapsLabel->setTextSize(2.6);
		$this->nbMapsLabel->setHalign('center');
		$this->nbMapsLabel->setPosition(6, 4.1);
		
		$this->icon = new Quad(3.5, 3.5);
		$this->icon->setStyle('Icons128x128_1');
		$this->icon->setSubStyle('Challenge');
		$this->icon->setPosition(-0.5, -0.5, 15); 
	}

	/**
	 * \brief Affichage
	 */
	function onDraw()
	{
		$this->clearComponents();

		if($this->nbMaps > $this->nbMapsTotal)
			$this->nbMapsLabel->setText('Terminé');
		else
			$this->nbMapsLabel->setText($this->nbMaps . '/' . $this->nbMapsTotal);

		$this->addComponent($this->background);
		$this->addComponent($this->header);
		$this->addComponent($this->headerLabel);
		$this->addComponent($this->nbMapsLabel);
		$this->addComponent($this->icon);
	}
	
	public function nbMaps()
	{
		return $this->nbMaps;
	}

	public function setNbMaps($nbMaps)
	{
		$this->nbMaps = $nbMaps;
	}

	public function setNbMapsTotal($totalMaps)
	{
		$this->nbMapsTotal = $totalMaps;
	}

}
