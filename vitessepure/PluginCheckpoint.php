<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint;

use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\ChallengeResultsWindowClassement;
use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\EliminatedWidget;
use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\LeaderWidget;
use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\InfoWindow;
use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\MapResultsWindowClassement;
use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\NbMapsWidget;
use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\ScoreWidget;

use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Structures\Challenger;

use ManiaLive\Gui\Windowing\CustomUI;
use ManiaLive\Gui\Windowing\WindowHandler;
use ManiaLive\Gui\Windowing\Window;
use ManiaLive\PluginHandler\PluginHandler;

/**
 * \class PluginCheckpoint
 * \brief Plugin du Challenge du Checkpoint
 */
class PluginCheckpoint extends \ManiaLive\PluginHandler\Plugin
{
	private $allowedPlayers = array();								///< Login des joueurs autorisés, si vide tout le monde peut participer
	private $admins = array('vitessepure', 'jen-roger');
	private $points = array(25, 20, 16, 13, 11, 10, 9, 8, 
							7, 6, 5, 4, 3, 2, 1);					///< Points à attribuer
	private $cpAllowed = 2;											///< Nombre de checkpoints d'écart autorisé
	private $nbMaps = 4;											///< Nombre de maps à jouer
	private $mapsPlayed = 0;										///< Nombre de maps jouées (prend en compte la map actuelle)

	private $comments = array(
								array('Jean-Michel Larqué', 'Être éliminé c\'est bien mais gagner c\'est mieux.'),
								array('Votre plus grand fan', 'Très bonne course, félicitations !'),
								array('Sébastien Vettel', 'On dirait que tu roules dans la purée...'),
								array('Raymond Domenech', 'Ça c\'est joué sur un coup du sort.'),
								array('Superman', 'J\'aurai fait mieux. Bien mieux.'),
								array('Michael Schumacher', 'Les voitures ça roule.'),
								array('Croustibat', 'Qui peut me battre ?'),
								array('Francis Cabrel', 'Tu manques de concentration mon ami !'),
								array('Nicolas Hulot', 'Les checkpoints ça pollue.'),
								array('LeStunterdu92', 'Moua j\'em pa kan c padu steunt lol XD'),
								array('Batman', 'De toutes façons les autres ils cuttent...'),
								array('Norbert le dragon', 'Course palpitante n\'est-ce pas ?'),
								array('Laurent Jalabert', 'Et voilà, encore une défaite à cause du vent de face !'),
								array('Aristide le cheminot', 'Allez les bleus, allez les bleus, alleeeeeeeeeeez'),
								array('Dernier des mohicans', 'Moi je suis habitué à être dernier.'),
								array('Zinédine Zidane', 'Le Qatar c\'est bien.'),
								array('Bibendum', 'Tes pneumatiques ne sont pas automatiques.'),
								array('Mika Häkkinen', 'Pour faire un bon vainqueur il faut être un bon perdant.'),
						);											///< Commentaires lors de l'élimination

	private $playersEliminated = array();							///< Logins des joueurs éliminés
	private $isStarted = false;										///< Map d'un challenge du checkpoint démarrée ? (false lors d'un WU)
	private $go = false;											///< Challenge lancé ?
	private $eliminatedWidget = null;
	private $nbMapsWidget = null;
	private $leaderWidget = null;   								///< Widget d'affichage du leader
	private $decisiveCp = 29;										///< Numéro du checkpoint décisif
	private $decisiveCpDisplayed = false;							///< L'avertissement comme quoi le prochain checkpoint est décisif a été affiché ?
	private $cpByLap = 0;											///< Nombre de checkpoints dans un tour
	private $wuIsFinished = false;									///< Le WU est terminé ?
	private $resultWindow = null;									///< Fenêtre d'affichage des résultats de la map

	/**
	 * \brief Initialisation du plugin
	 */
	public function onInit()
	{
		$this->setVersion(1.0);
	}

	/**
	 * \brief Chargement du plugin
	 *
	 * On passe en spec les joueus non-autorisés.
	 * On autorise les Event du Dedicated.
	 */
	public function onReady()
    {
		$this->registerChatCommand('go', 'go', 1, true, $this->admins);
		$this->registerChatCommand('endwu', 'endwu', 0, false, $this->admins);
		$this->registerChatCommand('finish', 'finish', 0, false, $this->admins);
    }

	/**
	 * \brief Démarre le Challenge du checkpoint
	 * \param $login : Login du joueur
	 * \param $nbMaps : Nombre de maps
	 */
	public function go($login, $nbMaps)
	{
		$nbMaps = intval($nbMaps);
		if(!$this->go && $nbMaps > 0)
		{
			$this->connection->chatSendServerMessage('$3F0>>$z $FFFChallenge du Checkpoint démarré !');

			$this->mapsPlayed = 0;

			$this->nbMaps = $nbMaps;
			$this->go = true;
			$this->shuffleMaps();
			
			$this->resultWindow = MapResultsWindowClassement::Create(Window::RECIPIENT_ALL);
			$this->resultWindow->addCloseCallback(array($this, 'onChallengeResultsMapHide'));			

			$this->eliminatedWidget = EliminatedWidget::Create(Window::RECIPIENT_ALL);
			$this->nbMapsWidget = NbMapsWidget::Create(Window::RECIPIENT_ALL);
			$this->nbMapsWidget->setNbMapsTotal($this->nbMaps);
			$this->nbMapsWidget->setNbMaps($this->mapsPlayed+1);
			$this->nbMapsWidget->show();
			$this->leaderWidget = LeaderWidget::Create(Window::RECIPIENT_ALL);
			$this->leaderWidget->setLeader('');

			// On passe les spectateurs devant participer en joueur
			foreach($this->storage->spectators as $s)
			{
				$this->onPlayerConnect($s->login, true);
			}

			// On crée les objets Challengers avec les joueurs
			foreach($this->storage->players as $p)
			{
				$this->onPlayerConnect($p->login, false);
			}

			Challenger::reinit();

			// Configuration du serveur
			$this->enableDedicatedEvents();
			$this->connection->setGameMode(0); // Se joue en round
			$this->connection->setChatTime(25000); // 25 secondes entre les maps
			$this->connection->setRoundForcedLaps(9999);
			$this->connection->setAllWarmUpDuration(0);
			$this->connection->setRoundPointsLimit(1000); // 1000 points pour finir le round (c'est pas possible donc faut être trop lol)
			$this->connection->nextChallenge(); // On relance la map

			$this->nbMapsWidget->show();
		}
	}
	
	/**
	 * \brief Fin du WU
	 */
	public function endwu()
	{
		if(!$this->isStarted)
		{
			$this->connection->chatSendServerMessage('$3F0>>$z $FFFFin du Warm-Up, attention au départ...');
			$this->wuIsFinished = true;
			$this->connection->restartChallenge();
		}		
	}

	/**
	 * \brief Fin de la map
	 */
	public function finish()
	{
		if($this->go && $this->isStarted)
		{
			if($leader != null)
			{
				$leader->addVictory(); 	// On ajoute une victoire au leader
			}
			$this->givePoints();   	// On attribue les points
			$this->connection->nextChallenge(); // On next
		}		
	}

	/**
	 * \brief Connexion d'un joueur
	 */
    public function onPlayerConnect($login, $isSpectator)
    {
		if($this->go)
		{
			$player = $this->storage->getPlayerObject($login);
			$customUi = new CustomUI();
			$customUi->scoretable = false;
			WindowHandler::setCustomUI($customUi, $player);
			$this->nbMapsWidget->show();

			if(count($this->allowedPlayers) > 0 && !in_array($login, $this->allowedPlayers)) // Si le joueur n'est pas autorisé, on le passe en spec
			{
				$this->forceSpec($login);
			}
			else
			{
				if(!in_array($login, $this->playersEliminated)) // Si le joueur n'est pas déjà éliminé de la map en cours
				{
					$this->forcePlay($login);
					new Challenger($player);
					// Affichage de l'UI
					$widget = ScoreWidget::Create($player->login);
					$widget->setDecisiveCp($this->decisiveCp);
					$widget->setMyScore(0);
					if(!$this->isStarted)
						$widget->isWU(true);
					else
						$widget->isWU(false);
					$widget->show();
					$this->connection->chatSendServerMessage('$3F0>>$z $FFF' . $player->nickName . ' $s$z entre en jeu avec la ferme intention d\'en découdre');
				}
				else // Sinon on le passe en spec
				{
					$this->forceSpec($login);
				}
			}
		}
    }
	
	/**
	 * \brief Un joueur quitte la partie
	 * \param $login : Login du joueur
	 */
	public function onPlayerDisconnect($login)
	{
		if($this->go)
		{

		}
	}

	/**
	 * \brief Début d'une map (appelée à chaque restart)
	 */
	public function onBeginRound()
	{
		if($this->go)
		{
			foreach($this->storage->spectators as $s)
			{
				$customUi = new CustomUI();
				$customUi->scoretable = false;
				WindowHandler::setCustomUI($customUi, $s);
			}
			foreach($this->storage->players as $p)
			{
				$customUi = new CustomUI();
				$customUi->scoretable = false;
				WindowHandler::setCustomUI($customUi, $p);
			}
			
			if($this->nbMapsWidget != null)
			{
				$this->nbMapsWidget->setNbMaps($this->mapsPlayed+1);
				$this->nbMapsWidget->show();
			}
			foreach(ScoreWidget::getAll() as $s)
			{
				$s->show();
			}
			$challenge = $this->connection->getCurrentChallengeInfo();
			$this->cpByLap = $challenge->nbCheckpoints;

			if(!$this->isStarted) // Si c'est un WU
			{
				$this->connection->chatSendServerMessage('$3F0>>$z $FFFC\'est parti pour deux tours de chauffe !');
				foreach(ScoreWidget::getAll() as $s)
				{
					$s->isWU(true);
				}
				if($this->eliminatedWidget != null)
				{
					$this->eliminatedWidget->hide();
				}
			}
			else // Sinon
			{
				foreach(ScoreWidget::getAll() as $s)
				{
					$s->isWU(false);
				}
				$this->cpAllowed = 2;
				Challenger::reinit();
				if($this->eliminatedWidget != null)
				{
					$this->eliminatedWidget->clear();
					$this->eliminatedWidget->reinitY();
					$this->eliminatedWidget->show();
				}
				$this->connection->chatSendServerMessage('$3F0>>$z $FFF$oGogogogogo !');
			}
		}
	}

	/**
	 * \brief Lorsque le résultat de la map est cachée on affiche le classement général
	 */
	public function onChallengeResultsMapHide()
	{
		// Affichage du classement général
		$window = ChallengeResultsWindowClassement::Create(Window::RECIPIENT_ALL);
		$window->setTimeout(21);
		$window->clearLines();
		if($this->mapsPlayed == $this->nbMaps)
			$window->setTitle('$F00Classement général final');
		else
			$window->setTitle('Classement général');
		$window->setColumns(array('Pseudo'=>20, 'Points'=>10, 'Checkpoints'=>10, 'Victoires'=>10));
		$players = Challenger::getChallengers();		
		uasort($players, array('ManiaLivePlugins\vitessepure\PluginCheckpoint\PluginCheckpoint', 'sortByPoints'));
		$rank = 0;
		foreach($players as $p)
		{
			if($this->mapsPlayed == $this->nbMaps && $rank == 0)
				$this->connection->chatSendServerMessage('$3F0>>$z $FFF' . $p->nickname . '$s$z remporte facilement ce Challenge du Checkpoint !');
			++$rank;
			$placeGain = 0;
			if($p->getPrevRank() > 0)
				$placeGain = $p->getPrevRank() - $rank;
			$window->addLine(array($p->nickname, $p->getPoints(), $p->getTotalCP(), $p->getVictories()), $placeGain);
			$p->setPrevRank($rank);
		}
		$window->show();
	}

	/**
	 * \brief Fin d'une map
	 */
	public function onEndRound()
	{
		if($this->go)
		{
			$this->decisiveCpDisplayed = false;
			foreach(ScoreWidget::getAll() as $s)
			{
				$s->hide();
			}
			if($this->eliminatedWidget != null)
			{
				$this->eliminatedWidget->hide();
			}
		}

		if($this->go && $this->isStarted)
		{
			$this->connection->chatSendServerMessage('$3F0>>$z $FFFC\'est terminé pour cette map.');
			$this->isStarted = false;
			$this->wuIsFinished = false;

			$this->leaderWidget->setLeader('');
			$this->leaderWidget->hide();
			
			// Affichage des résultats pour la map
			$this->resultWindow->setTimeout(12);
			$this->resultWindow->clearLines();
			$this->resultWindow->setTitle('Classement de la map');
			$players = Challenger::getChallengers();
			uasort($players, array('ManiaLivePlugins\vitessepure\PluginCheckpoint\PluginCheckpoint', 'sortByCheckpoints'));
			$this->resultWindow->setColumns(array('Pseudo'=>20, 'Checkpoints'=>10, 'Points'=>10));
			foreach($players as $p)
			{
				$this->resultWindow->addLine(array($p->nickname, $p->getCheckpoints(), $p->getMapPoints()));
			}
			$this->resultWindow->show();

			// On réinitialise les joueurs éliminés
			foreach($this->playersEliminated as $player)
				$this->forcePlay($player);
			$this->playersEliminated = array();

			++$this->mapsPlayed; // Une map de plus de jouée

			 // Fin du Challenge du Checkpoint /////////////////////////////////////////////////////////////
			if($this->mapsPlayed == $this->nbMaps)
			{
				$this->connection->chatSendServerMessage('$3F0>>$z $FFFFin du Challenge du Checkpoint...');
				$this->generateFile('FinalResult.txt');
				NbMapsWidget::EraseAll();
				ScoreWidget::EraseAll();
				EliminatedWidget::EraseAll();
				LeaderWidget::EraseAll();
				$this->go = false;
			} /////////////////////////////////////////////////////////////////////////////////////////////
			else // Sinon on génère le résultat de la map
			{
				$this->generateFile('result_map_' . $this->mapsPlayed . '.txt');
			}

			gc_collect_cycles();
		}
		else if($this->wuIsFinished)
			$this->isStarted = true;
	}

	/**
	 * \brief Passage d'un Checkpoint
	 * \param $playerUid 		: Uid du joueur
	 * \param $login	 		: Login du joueur
	 * \param $timeOrScore		: Temps ou score du joueur
	 * \param $curLap			: Tour courant
	 * \param $checkpointIndex 	: Nombre de checkpoints franchis par le joueur
	 *
	 * Mise à jour du checkpoint passé par le premier.
	 * Mise à jour du nombre de checkpoints passés par un joueur.
	 * Test l'élimination lorsque le premier passe un checkpoint
	 */
	 public function onPlayerCheckpoint($playerUid, $login, $timeOrScore, $curLap, $checkpointIndex)
	 {
		// Si le challenge est en court, que le joueur est un participant et qu'il n'est pas éliminé
		if($this->go && $this->isStarted && Challenger::playerExists($login) && !in_array($login, $this->playersEliminated))
		{
			if($checkpointIndex == $this->decisiveCp-1)
			{
				if(!$this->decisiveCpDisplayed)
				{
					$this->connection->chatSendServerMessage('$3F0>>$z $F00Le prochain checkpoint est décisif !');
					$this->decisiveCpDisplayed = true;
				}
			}
			
			if($checkpointIndex == $this->decisiveCp)
				$this->cpAllowed = 1;

			$player = Challenger::getChallenger($login);
			// Si son login existe
			if($player != null)
			{
				// S'il a le plus de checkpoints (s'il est leader)
				if($player->addCheckpoint())
				{
					$leader = Challenger::getLeader();
					// Si le leader est un joueur qui existe
					if($leader != null)
					{
						$this->leaderWidget->setLeader($leader->nickname);
						$this->leaderWidget->show();
					
						// On test les joueurs non-éliminés pour voir s'ils sont éliminés
						$players = Challenger::getChallengers();
						foreach($players as $key => $p)
						{
							if(!in_array($key, $this->playersEliminated))
							{
								if($p->getCheckpoints() <= Challenger::getLeaderCP() - $this->cpAllowed) // Éliminé
								{
									$this->eliminatePlayer($key);
								}
							}
						}
						$nbPlayersInRace = $this->nbPlayersInGame();						
						
						// Si tous les joueurs ont été éliminés sauf un, c'est terminé pour la map en cours
						if($nbPlayersInRace <= 1)
						{
							$leader->addVictory(); 	// On ajoute une victoire au leader
							$this->givePoints();   	// On attribue les points
							$this->connection->nextChallenge(); // On next
						}
					}
				}
			}
		}
		else if($this->go && !$this->isStarted && $checkpointIndex+1 == $this->cpByLap*2)
		{
			$this->connection->chatSendServerMessage('$3F0>>$z $FFFFin du Warm-Up, attention au départ...');
			$this->wuIsFinished = true;
			$this->connection->restartChallenge();
		}
	 }

	 /**
	  * \brief Passe $login en spec car il n'est pas autorisé à participer
	  * \param $login : Login du pilote à forcer en spec
	  *
	  * Affiche une fenêtre contenant les logins des joueurs autorisés à participer
	  */
	 private function forceSpec($login)
	 {
		if($this->go)
		{
			/*
			$window = InfoWindow::Create($login);
					
			$window->setSizeX(60);
			$window->setSizeY(26);

			$list = '';
			foreach($this->allowedPlayers as $p)
				$list .= $p . '$s$z | ';
			
			$window->setTitle('Challenge du checkpoint');
			$window->setText("Vous n'êtes pas autorisé à participer.\nLogins des pilotes autorisés à participer :\n" . $list);
					
			$window->centerOnScreen();
			$window->show();
			*/

			$newSpec = \ManiaLive\Data\Storage::getInstance()->getPlayerObject($login);
			if($newSpec != null)
				$this->connection->forceSpectator($newSpec, 1);
		}
	 }

	 /**
	  * \brief Passe $login en joueur car il doit participer
	  * \param $login : Login du pilote à forcer en joueur
	  */
	 private function forcePlay($login)
	 {
		$newSpec = \ManiaLive\Data\Storage::getInstance()->getPlayerObject($login);
		if($newSpec != null)
			$this->connection->forceSpectator($newSpec, 2);	 
	 }

	 /**
	  * \brief Élimine un joueur et affiche une fenêtre avec quelques informations
	  * \param $login : Joueur éliminé
	  */
	 private function eliminatePlayer($login)
	 {
		if($this->go && $this->isStarted)
		{
			$newSpec = Challenger::getChallenger($login);
			$newSpecObject = $newSpec->getPlayerObject();

			if($newSpecObject != null)
			{
				$spec = \ManiaLive\Data\Storage::getInstance()->getPlayerObject($login);
				if($spec != null)
				{
					$this->connection->sendNotice(null, $spec->nickName . '$z$s est éliminé !', $login);
					$this->connection->forceSpectator($spec, 1);  // Force en spec
					
					$nbPlayersInRace = $this->nbPlayersInGame();
					if($nbPlayersInRace > 1 && $newSpecObject != null)
					{
						$window = InfoWindow::Create($login);
						$window->setSizeX(76);
						$window->setSizeY(29);  
						$window->setTitle('Challenge du checkpoint - Vous êtes éliminé de cette map...');

						$random = array_rand($this->comments);
						$avis = $this->comments[$random];
						$window->setText("Vous êtes malheureusement éliminé de cette map...\n
Vous avez franchi " . $newSpec->getCheckpoints() . " checkpoints.\n\n" . '$FD0' .
"L'avis de " . $avis[0] . " : \"" . $avis[1] . "\"");

						$window->centerOnScreen();
						$window->show();
					}
				}
			}

			$this->playersEliminated[] = $login;
			$this->eliminatedWidget->setEliminatedPlayers($this->playersEliminated);
			$this->eliminatedWidget->show();
		}
	 }

	 /**
	  * \brief Attribution des points
	  */
	 private function givePoints()
	 {
		if($this->go && $this->isStarted)
		{
			// On attribue les points selon le classement du nombre de checkpoints franchis
			$players = Challenger::getChallengers();
			uasort($players, array('ManiaLivePlugins\vitessepure\PluginCheckpoint\PluginCheckpoint', 'sortByCheckpoints'));
			$rank = 0; // Rang de points (si rang = 0 => 25pts, 1 => 20pts, ...)
			$checkpoints = 0; // Nombre de checkpoints franchis par le joueur précédemment testé
			$egalites = 0; // Nombre de joueurs précédents à égalité

			foreach($players as $p)
			{
				if($p->getCheckpoints() < $checkpoints) // Si le joueur a passé moins de checkpoints que le joueur précédent on lui attribue les points du rang d'en-dessous
				{
					++$rank;
					$rank += $egalites;
					$egalites = 0;
				}
				else if($p->getCheckpoints() == $checkpoints)// Sinon on lui attribue autant de points que le joueur précédent
				{
					++$egalites;
				}
				// Sinon c'est le premier

				if($rank < count($this->points)) // Si on ne sort pas du tableau
				{
					$p->addPoints($this->points[$rank]); // On attribue les points
					$p->setMapPoints($this->points[$rank]);
				}
				else // Sinon 0, car seuls les 15 premiers gagnent des points
				{
					$p->setMapPoints(0);
				}
				$p->addCheckpoints($p->getCheckpoints());
				$checkpoints = $p->getCheckpoints();
			}
		}
	 }

	 /**
	  * \brief Génère un fichier .txt du classement général
	  * \param $filename : Nom du fichier
	  */
	 private function generateFile($filename)
	 {
		if($this->go && $this->isStarted)
		{
			$file = fopen('logs/' . $filename, 'a');

			$players = Challenger::getChallengers();		
			uasort($players, array('ManiaLivePlugins\vitessepure\PluginCheckpoint\PluginCheckpoint', 'sortByPoints'));
			foreach($players as $key => $p)
			{
				$player = $p->getPlayerObject();
				$txt = $player->nickName . ' (' . $key . ') | ' . $p->getTotalCP() . 'cps | ' . $p->getPoints() . 'pts | ' . $p->getVictories() . ' victoires' . "\n";
				fputs($file, $txt);
			}

			fclose($file);
		}
	 }

	/**
	 * \brief Mélangeage des maps
	 */
	private function shuffleMaps()
	{
		$challenges = $this->connection->getChallengeList(-1, 0);
		$nouvel_ordre = array();
		foreach($challenges as $ch)
		{
			$nouvel_ordre[] = $ch->fileName;
		}
		shuffle($nouvel_ordre);
		$this->connection->chooseNextChallengeList($nouvel_ordre);
		$this->connection->chatSendServerMessage("\$3F0>>\$z\$fff Mélangeage des maps.");  
	}
	
	/**
	 * \brief Nombre de joueurs en jeu
	 */
	private function nbPlayersInGame()
	{
		return count(Challenger::getChallengers()) - count($this->playersEliminated);
	}

	 /**
	  * \brief Tri par nombre de points total et départage par nombre de checkpoints franchis
	  * \param $a : Joueur 1
	  * \param $b : Joueur 2
	  */
	static public function sortByPoints($a, $b)
	{
		if($a->getPoints() == $b->getPoints())
		{
			if($a->getTotalCP() == $b->getTotalCP())
				return 0;
			return ($a->getTotalCP() > $b->getTotalCP()) ? -1 : 1;
		}
		return ($a->getPoints() > $b->getPoints()) ? -1 : 1;
	}

	/**
	 * \brief Tri par nombre de checkpoints franchis sur la map
	  * \param $a : Joueur 1
	  * \param $b : Joueur 2
	 */
	static public function sortByCheckpoints($a, $b)
	{
		if($a->getCheckpoints() == $b->getCheckpoints())
			return 0;
		return ($a->getCheckpoints() > $b->getCheckpoints()) ? -1 : 1;
	}

}
