<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Structures;

use \ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows\ScoreWidget;

/**
 * \class Challenger
 * \brief Classe gérant les participants au Challenge du Checkpoint
 */
class Challenger
{
	private static $leader = '';		   ///< Login du leader
	private static $leaderCP = 0;		   ///< Nombre de checkpoints franchis par le leader
	private static $challengers = array(); ///< Tous les Challengers (avec comme identifiant leur login)

	private $points = 0;				   ///< Total des points accumulés pendant le challenge
	private $mapPoints = 0;				   ///< Points gagnés lors de la dernière map jouée
	private $playerObject = null;		   ///< Objet Player de Manialive
	private $checkpoints = 0;			   ///< Nombre de checkpoints franchis sur la dernière map jouée
	private $totalCP = 0;			       ///< Nombre de checkpoints franchis pendant le challenge
	private $login = '';			       ///< Login du joueur
	private $victories = 0;				   ///< Nombre de victoires
	private $prevRank = 0;				   ///< Position précédente au classement général

	public $nickname = '';				   ///< Pseudo du joueur

	/**
	 * \brief Constructeur.
	 * \param $player : Objet du joueur
	 */
	public function __construct($player)
	{
		$this->login = $player->login;
		$this->nickname = $player->nickName;
		$this->playerObject = $player;

		if(!array_key_exists($this->login, self::$challengers))
		{
			self::$challengers[$this->login] = $this;
			echo $this->login . " participe. \n";
		}
		else // Si le login existe, on le réinitialise pour cette map
		{
			self::$challengers[$this->login]->setMapPoints(0);
			self::$challengers[$this->login]->setCheckpoints(0);
			self::$challengers[$this->login]->setPlayerObject($player);
		}
		
		$this->reinitPlayer();

		$widget = ScoreWidget::Create($this->login);
		$widget->setMyScore(0);
		$widget->setLeaderScore(self::$leaderCP);
		$widget->show();
	}
	
	public function setPlayerObject($player)
	{
		$this->playerObject = $player;
	}

	/**
	 * \brief Retourne le nombre de points accumulés
	 * \return Nombre de points gagnés pendant le challenge
	 */
	public function getPoints()
	{
		return $this->points;
	}
	
	/**
	 * \brief Retourne le nombre de points gagnés sur la map
	 * \return Nombre de points gagnés sur la dernière map jouée
	 */
	public function getMapPoints()
	{
		return $this->mapPoints;
	}
	
	/**
	 * \brief Retourne l'objet Player
	 * \return Objet Player de Manialive
	 */
	public function getPlayerObject()
	{
		return $this->playerObject;
	}
	
	/**
	 * \brief Retourne le nombre de checkpoints franchis sur la map
	 * \return Nombre de checkpoints franchis sur la dernière map jouée
	 */
	public function getCheckpoints()
	{
		return $this->checkpoints;
	}
	
	/**
	 * \brief Retourne le nombre total de checkpoints franchis
	 * \return Nombre total de checkpoints franchis
	 */
	public function getTotalCP()
	{
		return $this->totalCP;
	}

	/**
	 * \brief Ajoute des points
	 */
	public function addPoints($points)
	{
		$this->points += $points;
	}

	/**
	 * \brief Ajoute une victoire
	 */
	public function addVictory()
	{
		$this->victories += 1;
	}
	
	/**
	 * \brief Retourne le nombre de victoires
	 * \return Nombre de victoires
	 */
	public function getVictories()
	{
		return $this->victories;
	}
	
	
	/**
	 * \brief Modifie les points gagnés sur la map en cours
	 */
	public function setMapPoints($mapPoints)
	{
		$this->mapPoints = $mapPoints;
	}

	/**
	 * \brief Ajoute un checkpoint franchi pour la map en cours
	 * \return True si nouveau leader, False sinon
	 */
	public function addCheckpoint()
	{
		$this->checkpoints += 1;

		// Affichage
		if($this->checkpoints > self::$leaderCP)
		{
			self::$leader = $this->login;
			self::$leaderCP = $this->checkpoints;
			foreach(self::$challengers as $login => $c)
			{
				$widget = ScoreWidget::Create($login);
				$widget->setMyScore($c->getCheckpoints());
				$widget->setLeaderScore(self::$leaderCP);
				$widget->show();
			}
			return true;
		}
		else
		{
			$widget = ScoreWidget::Create($this->login);
			$widget->setMyScore($this->checkpoints);
			$widget->setLeaderScore(self::$leaderCP);
			$widget->show();
		}
		return false;
	}
	
	/**
	 * \brief Ajoute les checkpoints franchis au totalCP
	 */
	public function addCheckpoints($checkpoints)
	{
		$this->totalCP += $checkpoints;
	}

	/**
	 * \brief Modifie le nombre de checkpoints franchis sur la map en cours
	 * \return True si nouveau leader, False sinon
	 */
	public function setCheckpoints($checkpoints)
	{
		$this->checkpoints = $checkpoints;
		if($this->checkpoints > self::$leaderCP)
		{
			self::$leader = $this->login;
			self::$leaderCP = $this->checkpoints;
			return true;
		}
		return false;
	}
	
	/**
	 * \brief Modifie la position précédente au classement général
	 */
	public function setPrevRank($prevRank)
	{
		$this->prevRank = $prevRank;
	}
	
	/**
	 * \brief Retourne la position précédente au classement général
	 */
	public function getPrevRank()
	{
		return $this->prevRank;
	}
	
	/**
	 * \brief Réinitialisation du joueur
	 */
	public function reinitPlayer()
	{
		$this->setMapPoints(0);
		$this->setCheckpoints(0);
		$widget = ScoreWidget::Create($this->login);
		$widget->setMyScore(0);
		$widget->setLeaderScore(0);
		$widget->show();	
	}

	/**
	 * \brief Réinitialise les variables valables pour une map
	 */
	static public function reinit()
	{
		self::$leaderCP = 0;
		foreach(self::$challengers as $login => $c)
		{
			$c->setMapPoints(0);
			$c->setCheckpoints(0);
			$widget = ScoreWidget::Create($login);
			$widget->setMyScore(0);
			$widget->setLeaderScore(0);
			$widget->show();
		}
	}

	/**
	 * \brief Retourne le leader actuel de la map
	 * \return Objet Challenger correspondant au leader ou null s'il n'y a pas de leaders
	 */
	static public function getLeader()
	{
		if(self::$leader != '' && array_key_exists(self::$leader, self::$challengers))
			return self::$challengers[self::$leader];
		return null;
	}

	/**
	 * \brief Retourne le nombre de checkpoints franchis par le leader
	 * \return Nombre de checkpoints franchis par le leader
	 */
	static public function getLeaderCP()
	{
		return self::$leaderCP;
	}

	/**
	 * \brief Retourne le login du leader actuel de la map
	 * \return Objet Challenger correspondant au leader ou null s'il n'y a pas de leaders
	 */
	static public function getLeaderLogin()
	{
		return self::$leader;
	}

	/**
	 * \brief Affiche les résultats de la map
	 */
	static public function mapResults()
	{
		if($leader != '' && array_key_exists(self::$leader, self::$challengers))
			return self::$challengers[self::$leader];
		return null;
	}

	/**
	 * \brief Retourne le challenger de login $login
	 * \param $login : Login du Challenger à obtenir
	 */
	static public function getChallenger($login)
	{
		if(array_key_exists($login, self::$challengers))
			return self::$challengers[$login];
		return null;
	}

	/**
	 * \brief Retourne le tableau des joueurs
	 */
	static public function getChallengers()
	{
		return self::$challengers;
	}
	
	/**
	 * \brief Test si $login est un Challenger
	 * \return True si $login est un Challenger, False sinon
	 */
	static public function playerExists($login)
	{
		if(array_key_exists($login, self::$challengers))
			return true;
		return false;
	}

}
