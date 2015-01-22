<?php
//v1.0
/*
$window = WindowClassement::Create($login);
$window->clearLines();
$window->setTitle("test");
$window->setColumns(array("Pseudo"=>20, "Temps"=>10, "Autre"=>10, "Test"=>6, "{NBVICT}"=>5));
foreach($classement as $c)
	$window->addLine(array($c[0], $c[1], $c[2], $c[3], $c[4]));
$window->show();
*/
namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\MedalsBig;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLive\Utilities\Time;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLive\Gui\Windowing\Controls\PageNavigator;

class WindowClassement extends \ManiaLive\Gui\Windowing\Window
{
	protected $bg;//Background
	protected $title;//Titre
	protected $bgTitle;//Background titre
	protected $ligneEntete;//Ligne d'entête des colonnes
	protected $frameClassement;//Frame contenant le classement
	protected $ligneMilieu;//Ligne du milieu du tableau
	
	protected $columns = array();
	protected $pos = 0; //Place
	protected $decx = 0; //Décalage en x pour 2ème colonne
	protected $miWidth = 0; //width d'un côté de tableau
	
	static $imagesIndications = array('UP'=>"http://nsm02.casimages.com/img/2009/10/31/091031021806483674754007.png", 'NO_CHANGE'=>"http://nsm02.casimages.com/img/2009/10/31/091031021727483674754005.png");
	
	function initializeComponents()
	{
		$this->setSize(96, 70);
		$this->centerOnScreen();
		
		//Background
		$this->bg = new BgRaceScore2();
		$this->bg->setSubstyle(BgRaceScore2::BgScores);
		$this->bg->setPosition(-0.8, 0, 0);
		$this->bg->setSize(98, 72);
		$this->addComponent($this->bg);
		
		//Background titre
		$this->bgTitle = new Bgs1InRace();
		$this->bgTitle->setSubStyle(Bgs1InRace::BgTitle3_2);
		$this->bgTitle->setSize(96, 4);
		$this->bgTitle->setPosition(0, 1, 4);
		$this->addComponent($this->bgTitle);
		
		//Titre
		$this->title = new Label();
		$this->title->setSize(96, 2.66);
		$this->title->setPosition(48, 1.5, 5);
		$this->title->setTextSize(3);
		$this->title->setHAlign('center');
		$this->addComponent($this->title);
		
		//Ligne d'entête des colonnes
		$this->ligneEntete = new Frame();
		$this->ligneEntete->setPosition(0,5.33,3);
		$this->ligneEntete->setSize(96, 4);
		$this->addComponent($this->ligneEntete);
		
		//Frame contenant le classement
		$this->frameClassement = new Frame();
		$this->frameClassement->setPosition(0,0.5,3);
		$this->frameClassement->setSize(96, 70);
		$this->addComponent($this->frameClassement);
		
		//Ligne séparatrice du milieu
		$this->ligneMilieu = new Bgs1InRace();
		$this->ligneMilieu->setSubStyle(Bgs1InRace::BgTitle3_2);
		$this->ligneMilieu->setPosition(47.2, 5, 3);
		$this->ligneMilieu->setSize(0.2, 67);
		$this->addComponent($this->ligneMilieu);
	}
	
	function setColumns($cols = array())
	{
		$this->ligneEntete->clearComponents();
		$this->columns = $cols;
		for($i=0; $i<=1; ++$i)
		{
			$label = new Label();
			$label->setText('$o$fffPlace');
			$label->setPosition(0.8+($this->miWidth*$i), 0, 2);
			$label->setSize(8, 1.06);
			$label->setTextSize(1);
			$this->ligneEntete->addComponent($label);
			
			$pos = 6;
			foreach($cols as $nom_colonne => $width)
			{
				if($nom_colonne == '{NBVICT}')
				{
					$this->addIconsFirst($pos+($this->miWidth*$i)); 
				}
				else
				{
					$label = new Label();
					$label->setText('$o$fff'.$nom_colonne);
					$label->setPosition($pos+($this->miWidth*$i), 0, 2);
					$label->setSize($width, 1.06);
					$label->setTextSize(1);
					$this->ligneEntete->addComponent($label);
				}
				
				$pos += $width;
			}
			$this->miWidth = $pos;
		}
		$this->setSize(2*$this->miWidth, 70);
		$this->centerOnScreen();
		$this->bg->setSize(2*$this->miWidth+2, 72);
		$this->bgTitle->setSize(2*$this->miWidth, 4);
		$this->title->setSize(2*$this->miWidth, 2.66);
		$this->title->setPosition($this->miWidth, 1.5, 5);
		$this->frameClassement->setSize(2*$this->miWidth, 70);
		$this->ligneMilieu->setPosition($this->miWidth-0.2, 5, 3);
	}
	
	function setTitle($texte)
	{
		$this->title->setText('$o'.$texte);
	}
	
	function addIconsFirst($posx)
	{
		for($i=0; $i<=1; ++$i)
		{
			$icon = new Icons64x64_1();
			$icon->setSubstyle(Icons64x64_1::First);
			$icon->setPosition($posx, -0.53, 2);
			$icon->setSize(2, 2.66);
			$this->ligneEntete->addComponent($icon);
		}
	}
	
	function addMedal($medal,$posn)
	{
		list($posx, $posy, $posz) = explode(' ',$posn);
		$icon = new MedalsBig();
		if($medal == 'Gold')$icon->setSubstyle(MedalsBig::MedalGold);
		else if($medal == 'Silver')	$icon->setSubstyle(MedalsBig::MedalSilver);
		else if($medal == 'Bronze')	$icon->setSubstyle(MedalsBig::MedalBronze);

		$icon->setPosition($posx, $posy, $posz);
		$icon->setSize(1.6,2.13);
		$this->frameClassement->addComponent($icon);
	}
	
	function addPlaceIndication($indication, $posn)
	{
		list($posx, $posy, $posz) = explode(' ',$posn);
		if($indication == 'DOWN')
		{
			$icon = new Icons64x64_1();
			$icon->setSubstyle(Icons64x64_1::RedLow);
		}
		else
		{
			$icon = new Quad();
			$icon->setImage(self::$imagesIndications[$indication]);
		}
		$icon->setPosition($posx, $posy, $posz);
		$icon->setSize(1.5,2);
		$this->frameClassement->addComponent($icon);
	}
	
	function addLabel($posn, $sizen, $text, $textsize = 2, $halign = '')
	{
		list($posx, $posy, $posz) = explode(' ',$posn);
		list($sizex, $sizey) = explode(' ',$sizen);
		$label = new Label();
		$label->setText($text);
		$label->setPosition($posx, $posy, $posz);
		$label->setSize($sizex, $sizey);
		$label->setTextSize($textsize);
		if($halign != '')$label->setHAlign($halign);
		$this->frameClassement->addComponent($label);
	}
	
	function addLine($values = array(), $evolution = '')
	{
		++$this->pos;
		$y = (($this->pos-1)%18)*3.5+8;
		if($this->pos == 19){$this->decx = $this->miWidth;}
		else if($this->pos > 36)return;
		
		if($this->pos == 1)
		{
			$color = '$ff0'; 
			$this->addMedal("Gold", (4+$this->decx).' '.($y+0.1).' 2');
		}
		else if($this->pos == 2)
		{
			$color = '$BBB'; 
			$this->addMedal("Silver", (4+$this->decx).' '.($y+0.1).' 2');
		}
		else if($this->pos == 3)
		{
			$color = '$B99'; 
			$this->addMedal("Bronze", (4+$this->decx).' '.($y+0.1).' 2');
		}
		else $color = '$fff';
		
		if($evolution !== '')
		{
			if($evolution > 0)
			{
				$this->addPlaceIndication('UP', (0.2+$this->decx).' '.($y).' 2');
			}
			else if($evolution < 0)
			{
				$this->addPlaceIndication('DOWN', (0.2+$this->decx).' '.($y).' 2');
			}
			else 
			{
				$this->addPlaceIndication('NO_CHANGE', (0.2+$this->decx).' '.($y).' 2');
			}
		}
		
		$this->addLabel((3.8+$this->decx).' '.$y.' 2', "3.2 2", $color.$this->pos, 2, 'right');
		$pos = 6;
		$i = 0;
		foreach($this->columns as $nom_colonne => $width)
		{
			if(!isset($values[$i]))break;
			$this->addLabel(($pos+$this->decx).' '.$y.' 2', $width." 2", $color.$values[$i]);
			$pos += $width;
			++$i;
		}
	}
	
	function clearLines()
	{
		$this->frameClassement->clearComponents();
		$this->pos = 0;
		$this->decx = 0;
	}
	
	function onDraw()
	{
	}
}

?>