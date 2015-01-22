<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Structures\Challenger;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;
use ManiaLive\Gui\Windowing\Window;

/**
 * \class ChallengeResults
 * \brief Classement général
 *
 * Deuxième fenêtre affichée lors du podium
 */
class ChallengeResultsWindow extends \ManiaLive\Gui\Windowing\Window
{

	/**
	 * \brief Initialisation
	 */
	function initializeComponents()
	{
		$this->setSize(60, 32);

		$this->background = new BgsPlayerCard();
		$this->background->setSize($this->sizeX, $this->sizeY);
		$this->background->setSubStyle(BgsPlayerCard::BgPlayerCardBig);
	}

	/**
	 * \brief Affichage
	 */
	function onDraw()
	{
		$this->clearComponents();
		$this->addComponent($this->background);

		// Affichage du classement général (pour chaque pilote : nombre de checkpoints franchis depuis le début + points + victoires)
		$players = Challenger::getChallengers();		
		uasort($players, array('ManiaLivePlugins\vitessepure\PluginCheckpoint\PluginCheckpoint', 'sortByPoints'));
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
			$labelCheckpoints->setText($p->getTotalCP() . ' cps');
			$this->addComponent($labelCheckpoints);

			// Points
			$labelPoints = new Label();
			$labelPoints->setPosition(35, $i*3 + 3);
			$labelPoints->setText($p->getPoints() . ' pts');
			$this->addComponent($labelPoints);

			// Victoires
			$labelVictories = new Label();
			$labelVictories->setPosition(45, $i*3 + 3);
			$nbVictories = $p->getVictories();
			if($nbVictories > 1)
				$labelVictories->setText($nbVictories. ' victoires');
			else
				$labelVictories->setText($nbVictories. ' victoire');			
			$this->addComponent($labelVictories);

			++$i;
		}
	}

}
