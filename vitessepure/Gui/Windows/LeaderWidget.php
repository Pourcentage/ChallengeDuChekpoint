<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;

/**
 * \class LeaderWidget
 * \brief Affiche le pseudo du leader
 */
class LeaderWidget extends \ManiaLive\Gui\Windowing\Window
{
	static private $x = 53;
	static private $y = -27;

	private $background;		///< Arrière-plan
	private $header;			///< En-tête
	private $headerLabel;		///< Titre
	private $leaderLabel;		///< Leader
	private $icon;				///< Icône

	/**
	 * \brief Initialisation
	 */
	function initializeComponents()
	{
		$this->setSize(12, 6);
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
		$this->headerLabel->setText('$000Leader');
		
		$this->icon = new Quad(2.3, 2.3);
		$this->icon->setStyle('Icons64x64_1');
		$this->icon->setSubStyle('First');
		$this->icon->setPosition(0, 0.3, 15); 
		
		$this->leaderLabel = new Label();
		$this->leaderLabel->setTextSize(1.6);
		$this->leaderLabel->setHalign('center');
		$this->leaderLabel->setPosition(6, 3);
		$this->leaderLabel->setSize(11.56);
	}

	/**
	 * \brief Affichage
	 */
	function onDraw()
	{
		$this->clearComponents();
		$this->addComponent($this->background);
		$this->addComponent($this->header);
		$this->addComponent($this->headerLabel);
		$this->addComponent($this->leaderLabel);
		$this->addComponent($this->icon);
	}

	/**
	 * \brief Modifie le leader
	 * \param $leader : Pseudo du leader
	 */
	public function setLeader($leader)
	{
		$this->leaderLabel->setText($leader);
	}

}
