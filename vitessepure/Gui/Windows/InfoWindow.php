<?php

namespace ManiaLivePlugins\vitessepure\PluginCheckpoint\Gui\Windows;

use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Label;
use ManiaLive\Gui\Windowing\Controls\Panel;

/**
 * \class InfoWindow
 * \brief Fenêtre d'informations
 *
 * Affiche du texte
 */
class InfoWindow extends \ManiaLive\Gui\Windowing\ManagedWindow
{
        protected $text; ///< Texte à afficher

		/**
		 * \brief Initialisation des composants
		 */
        protected function initializeComponents()
        {
			$this->setSize(40, 30);

			$this->text = new Label();
			$this->text->enableAutonewline();
			$this->addComponent($this->text);
        }

		/**
		 * \brief Fermeture de la fenêtre
		 */
        protected function onHide() {}

		/**
		 * \brief Affichage de la fenêtre
		 */
        protected function onShow()
        {
			$this->text->setPosition(2, 6);
			$this->text->setSize($this->sizeX - 4, $this->sizeY - 6);
        }

		/**
		 * \brief Modification du texte
		 */
        public function setText($text)
        {
			$this->text->setText($text);
        }

}
