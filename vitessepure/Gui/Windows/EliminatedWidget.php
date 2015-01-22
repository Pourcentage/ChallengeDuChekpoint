<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;

use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Structures\Challenger;

/**
 * \class EliminatedWidget
 * \brief Affiche les joueurs éliminés de la map courante
 */
class EliminatedWidget extends \ManiaLive\Gui\Windowing\Window
{
	static private $x = -68;
	static private $y = -28;

	private $background;		///< Arrière-plan
	private $header;			///< En-tête
	private $headerLabel;		///< Titre
	private $eliminatedText;	///< Texte à afficher
	private $eliminatedLabel;	///< Label
	private $icon;				///< Icône
	private $nicknames;			///< Pseudo des joueurs éliminés
	
	/**
	 * \brief Initialisation
	 */
	public function initializeComponents()
	{
		$this->setSize(20, 40);
		$this->setPosition(self::$x, self::$y);
		
		$this->background = new BgsPlayerCard();
		$this->background->setSize($this->sizeX, 0);
		$this->background->setSubStyle(BgsPlayerCard::BgPlayerCardBig);
		
		$this->header = new Bgs1InRace();
		$this->header->setSubStyle('BgTitle3_1');
		$this->header->setSize(20, 2.6);
		$this->header->setPosition(0.3, 0);

		$this->icon = new Quad(3, 4);
		$this->icon->setStyle('Icons64x64_1');
		$this->icon->setSubStyle('Opponents');
		$this->icon->setPosition(4, -0.75, 15);
		
		$this->headerLabel = new Label();
		$this->headerLabel->setTextSize(1);
		$this->headerLabel->setHalign('center');
		$this->headerLabel->setPosition(10, 0.3);
		$this->headerLabel->setText('$000Éliminés');
		
		$this->eliminatedLabel = new Label();
		$this->eliminatedLabel->setTextSize(1.6);
		$this->eliminatedLabel->setHalign('left');
		$this->eliminatedLabel->setPosition(5, 3);
		$this->eliminatedLabel->setSize(19, 2.6);
	}

	/**
	 * \brief Affichage
	 */
	public function onDraw()
	{
		$this->clearComponents();
		$this->addComponent($this->background);
		$this->addComponent($this->header);
		$this->addComponent($this->headerLabel);
		$this->addComponent($this->icon);
		$this->addComponent($this->eliminatedLabel);
	}

	/**
	 * \brief Ajout d'un joueur éliminé
	 * \param $players : Array de joueurs éliminés
	 */
	public function setEliminatedPlayers($players)
	{
		$this->eliminatedText = '';
		$players = array_reverse($players);
		foreach($players as $p)
		{
			$player = Challenger::getChallenger($p);
			if($player != null)
			{
				$this->eliminatedText .= "\n" . '$s$z' . $player->nickname;
			}
		}
		$this->background->setSize($this->sizeX, 3+3*count($players));
		$this->eliminatedLabel->setText($this->eliminatedText);
	}

	/**
	 * \brief Supprime la liste des joueurs éliminés
	 */
	public function clear()
	{
		$this->eliminatedText = '';
		$this->eliminatedLabel->setText($this->eliminatedText);	
	}
	
	/**
	 * \brief Remet à 0 la hauteur du background
	 */
	public function reinitY()
	{
		$this->background->setSize($this->sizeX, 0);
	}

}
