# Challenge du Checkpoint #

/!\ Plugin en Français uniquement.

/!\ Seules des maps laps doivent être présentes sur le serveur

Ce plugin nécessite la dernière version de ManiaLive 1 (ManiaLive 1_r267).

1. Placer le répertoire "vitessepure" dans ManialivePlugins
2. Modifier les administrateurs à la ligne 27 de PluginCheckpoint.php
3. Utilisez /go [Nombre de maps] pour démarrer la compétition (/go 3 pour une compétition sur 3 maps)
4. Utiliser /endwu pour mettre fin prématurément aux 2 tours de chauffe qui ont lieu avant chaque map

## Principes du plugin ##

* Maps multilaps uniquement
* Uniquement sur TMU
* Toutes les maps se jouent en 9999 tours afin qu'on ne puisse pas les finir (et parce-que c'est trop lol de voir qu'on en est au tour 1/9999)
* Le but du jeu est d'être le seul joueur restant en jeu
* Pour cela il faut dégager en spectateur les autres joueurs
* Un joueur est propulsé en spectateur lorsqu'il a deux checkpoints de retard sur le premier
* Le premier est appelé "Leader" est à l'énorme honneur d'avoir son pseudo affiché à droite sur l'écran de tous les participants
* Lorsqu'il ne reste plus qu'un joueur en jeu la map est terminée et on passe à la suivante
* Puisque deux joueurs peuvent se disputer la victoire avec acharnement pendant les 9999 tours (et que ça peut devenir un peu long pour les spectateurs) le 30ème checkpoint est décisif. Le premier joueur qui franchit ce checkpoint remporte donc la map même si ses adversaires n'ont pas deux checkpoints de retard sur lui.

Il y a bien entendu un classement établi selon le nombre de points que chaque joueur possède sachant que les points sont distribués de la façon suivante à la fin de chaque map :

* Joueur ayant franchi le plus de checkpoints : 25
* Deuxième joueur ayant franchi le plus de checkpoints : 20
* Troisième joueur ayant franchi le plus de checkpoints : 16
* 4ème : 13
* 5ème : 11
* 6ème : 10
* puis 9, 6, 5, 4, 3, 2 et 1 points
