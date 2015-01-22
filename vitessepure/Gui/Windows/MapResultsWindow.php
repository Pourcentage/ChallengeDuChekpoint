<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Structures\Challenger;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Window;

/**
 * \class MapResults
 * \brief Résultats de la map
 *
 * Première fenêtre affichée lors du podium
 */
class MapResultsWindow extends \ManiaLive\Gui\Windowing\Window
{
	private $background;		///< Arrière-plan

	/**
	 * \brief Initialisation
	 */
	function initializeComponents()
	{
		$this->setSize(50, 32);

		$this->background = new BgsPlayerCard();
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->background->setSubStyle(BgsPlayerCard::BgPlayerCardBig);
		$this->addComponent($this->background);
	}

	/**
	 * \brief Affichage
	 */
	function onDraw()
	{
		$this->clearComponents();

		$this->addComponent($this->background);

		// Affichage des scores de la map (nombre de checkpoints franchis par chaque pilote et points gagnés sur la mapà
		$players = Challenger::getChallengers();
	    uasort($players, array('ManiaLivePlugins\vitessepure\PluginCheckpoint\PluginCheckpoint', 'sortByCheckpoints'));
		$i = 0;
		foreach($players as $key => $p)
		{
			$player = $p->getPlayerObject();

			// Pseudo
			$labelNickname = new Label();
			$labelNickname->setPosition(1, $i*3 + 3);
			$labelNickname->setText($player->nickName);
			$this->addComponent($labelNickname);

			// Checkpoints
			$labelCheckpoints = new Label();
			$labelCheckpoints->setPosition(20, $i*3 + 3);
			$labelCheckpoints->setText($p->getCheckpoints() . ' cps');
			$this->addComponent($labelCheckpoints);

			// Points
			$labelPoints = new Label();
			$labelPoints->setPosition(35, $i*3 + 3);
			$labelPoints->setText('+ ' . $p->getMapPoints() . ' pts');
			$this->addComponent($labelPoints);

			++$i;
		}
	}

}
