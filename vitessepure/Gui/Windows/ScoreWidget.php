<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;

/**
 * \class ScoreWidget
 * \brief Affiche le nombre de checkpoints franchis et du nombre de checkpoints franchis
 * par le leader
 */
class ScoreWidget extends \ManiaLive\Gui\Windowing\Window
{
	static private $x = 53;
	static private $y = -20;

	private $background;		///< Arrière-plan
	private $header;			///< En-tête
	private $headerLabel;		///< Titre
	private $scoreLabel;		///< Nombre de checkpoints
	private $myScore = 0;		///< Nombre de checkpoints du joueurs
	private $leaderScore = 0;	///< Nombre de checkpoints du leader
	private $icon;				///< Icône
	private $isWU = false;		///< C'est un WU ?
	private $decisiveCp = 29;	///< Index du checkpoint décisif

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
		$this->headerLabel->setPosition(6.3, 0.6);
		$this->headerLabel->setText('$000Checkpoints');

		$this->scoreLabel = new Label();
		$this->scoreLabel->setTextSize(2.6);
		$this->scoreLabel->setHalign('center');
		$this->scoreLabel->setPosition(6, 4.1);
		
		$this->icon = new Quad(3, 3);
		$this->icon->setStyle('Icons128x128_1');
		$this->icon->setSubStyle('Stunts');
		$this->icon->setPosition(-0.3, 0, 15); 
	}

	/**
	 * \brief Affichage
	 */
	function onDraw()
	{
		$this->clearComponents();

		if($this->isWU)
			$this->scoreLabel->setText('$F90Warm-Up');
		else if($this->leaderScore == $this->decisiveCp)
			$this->scoreLabel->setText('$F00' . $this->myScore . '/' . $this->leaderScore);
		else if($this->myScore == $this->leaderScore)
			$this->scoreLabel->setText('$0F0' . $this->myScore . '/' . $this->leaderScore);
		else
			$this->scoreLabel->setText($this->myScore . '/' . $this->leaderScore);

		$this->addComponent($this->background);
		$this->addComponent($this->header);
		$this->addComponent($this->headerLabel);
		$this->addComponent($this->scoreLabel);
		$this->addComponent($this->icon);
	}

	/**
	 * \brief Modifie le score du joueur
	 * \param $score : Nombre de checkpoints franchis par le joueur
	 */
	public function setMyScore($score)
	{
		$this->myScore = $score;
	}

	/**
	 * \brief Modifie le score du leader
	 * \param $score : Nombre de checkpoints franchis par le leader
	 */
	public function setLeaderScore($score)
	{
		$this->leaderScore = $score;
	}
	
	public function isWU($isWU)
	{
		$this->isWU = $isWU;
	}
	
	public function setDecisiveCp($decisiveCp)
	{
		$this->decisiveCp = $decisiveCp;
	}

}
