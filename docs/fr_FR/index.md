Présentation
============

Ce plugin pour Jeedom permet d’aider au maintien à domicile pour les personnes âgées. La demande initiale vient de ce sujet sur le forum : [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111/2). Les principales fonctionnalités (à ce jour) :
* Détection d’inactivité
* Gestion de boutons d’alertes
* Surveillance du confort (température, humidité, CO2, …)
* Surveillance sécurité (détecteurs de fumée, de fuite de gaz, …)
* Surveillance de « Dérive comportementale » pour essayer de détecter et prendre en charge au plus tôt et donc au mieux les soucis inhérent à la vieillesse

Lien vers le code source : [https://github.com/AgP42/seniorcare/](https://github.com/AgP42/seniorcare/)

Si vous souhaitez participer au projet, n’hésitez pas à le faire savoir ici [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111/2)

Avertissement
==========

Ce plugin a été conçu pour apporter une aide aux personnes souhaitant rester chez elles et à leurs aidants. Toutefois nous ne pouvons garantir son bon fonctionnement ni qu'un "plantage" de l’équipement domotique n'arrive justement au mauvais moment. Merci de l'utiliser en tant que tel et de ne pas prendre de risque pour la santé de ceux que nous cherchons à aider !

Changelog
==========

Ce plugin est en cours de développement, toutes les fonctions ne sont pas encore codées, certaines n'ont été que partiellement testées.

Beta 0.0.1 - 18 mars 2020 :
---
* Fonction "Détection d’inactivités"
* Fonction "Bouton d'alerte"
* Fonction "Capteurs confort"

Beta 0.0.2 - 21 mars 2020
---

* refactorisation du code, relecture et debug
* Ajout Gestion des capteurs Sécurité
* session de test n°1 et debug associé - voir docs/fr_FR/tests.md
* Ajout des actions de désactivation des alertes "bouton d'alerte", "capteur sécurité" et "capteur confort"
* Ajout gestion non-repetition des alertes sur les capteurs confort
* Capteurs conforts évalués par cron15 et non plus par listener sur chacun
* Mise à jour de la doc


Configuration du plugin
========================

Ajouter les différentes personnes à suivre. Puis pour chacune configurer les différents onglets.

Onglet Général
---
* Donner le nom de la personne.
* "Objet parent" : il s'agit de l'objet Jeedom auquel rattacher la personne. Il doit etre différent de "Aucun".
* Activer la personne.
* Visible sert a visualiser les infos sur le dashboard, pour l'instant il n'y a rien a visualiser.

Onglet **Gestion absences**
---

To Do

L’objectif ici étant de lier ce plugin au plugin « Agenda » pour les jours d’absences régulier et les vacances. Et d’ajouter les capteurs adéquats pour savoir si la personne est présente ou non pour les absences ponctuelles. Si la personne est absente, il faut désactiver la fonction de détection d’inactivité notamment.

Onglet **Détection d'inactivité**
---

Il s'agit ici de déclencher une alerte en cas d'absence d’activité de la personne, ce qui pourrait laisser penser à une chute.

**A améliorer : le délai de détection d'inactivité selon jour ou nuit**

3 étapes de fonctionnement :

1. Définir des capteurs de détection d’activité, quelle qu’elle soit (porte, fenêtre, interrupteur, mouvement, …), et un délai associé. Si aucun des capteurs d'activité n'a été activé à l’échéance du délai : le plugin déclenchera l’étape suivante « Avertissement »
2. « Avertissement » :
  * L'objectif de cette étape est de prévenir la personne que le système a détecté une inactivité et lui permettre de réagir avant que l’alerte ne soit envoyée aux aidants extérieurs.
  * Configuration à réaliser :
    - définir la liste d’actions pour lancer l’avertissement
    - définir la durée maximum pendant laquelle la personne pourra réagir avant de déclencher l'alerte.
    - définir les actions pour couper l’avertissement si la personne réagit (la réaction de la personne est détectée par n’importe lequel des capteurs définis à l’étape 1).
  * Si la personne ne réagit pas dans le délai imparti, le plugin passera a l'étape "Alerte". Dans ce cas, les actions pour "couper l'avertissement" ne seront pas réalisées.
3. « Alerte » :
  * Définir la liste d’actions pour lancer l’alerte aux aidants et avertir la personne qu’une alerte est en cours.
  * Définir la liste d’action pour désactiver ces actions d’alerte. L'alerte est déactivée par n’importe quel capteur de l’étape 1. Donc la personne dans son logement pourra le faire elle-même, ou un aidant une fois sur place.

![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Detection_inactivite.png)

Onglet **Bouton d'alerte**
---

Cet onglet permet de regrouper différents boutons d'alertes immédiates que la personne pourra activer pour demander de l'aide. Il peut s'agir d'un bouton à porter sur soi ou de boutons dans une zone dangereuse.

* Définir un ou plusieurs capteurs de type "bouton" ou "interrupteur"
* Définir les actions qui seront immédiatement réalisées à l'activation de n'importe lequel des capteurs.
* Définir un ou plusieurs capteurs de type "bouton" ou "interrupteur" qui serviront à annuler l'alerte
* Définir les actions qui seront réalisées à l'activation des capteurs d'annulation.

Si l'une de vos action est de type "message", vous pouvez utiliser le tag #nom_personne# qui enverra le nom configuré dans l'onglet "Général"

![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Boutons_alerte.png)

Onglet **Confort**
---

Cet onglet permet de regrouper les informations de confort sur le logement. Il peut s'agir de la température ou humidité pour différentes pièces ou du niveau de CO2. A partir de 1000 ppm (CO2), il est recommandé d'aérer le logement. Vous pouvez aussi suivre la température extérieure.

* Définir les différents capteurs de confort du logement que vous souhaitez suivre. Il peut s'agit de capteurs de Température, d'humidité, de CO2 ou de tout autre type.
  * Vous devez donner un nom unique à chacun de vos capteurs. Attention : le changement de nom d'un capteur revient à le supprimer et a en recréer un nouveau, vous perdez donc l'historique associé.
  * Sélectionner la commande jeedom du capteur associé
  * Définir son type
  * Définir les seuils haut et bas
* Définir les actions qui seront exécutées pour chaque capteur lors du dépassement d'un seuil, et la gestion voulu pour les répétitions (tant que le capteur est hors seuils).
* Définir (ou non) les actions qui seront exécutées pour chaque capteur lors du retour dans les seuils aprés un dépassement de seuil (exécutées à chaque "retour", pour chaque capteur).
* Définir (ou non) les actions à exécuter lorsque tous les capteurs ont une valeur dans leurs seuils.

Détails de fonctionnement :
* Toutes les 15 min, Jeedom évaluera pour chacun des capteurs si sa valeur est dans les seuils définis ou non.
* Les actions "Actions avertissement (pour chaque capteur hors seuils, je dois ?)" seront alors exécutées pour chacun des capteurs hors seuils, sauf si l'avertissement a déjà été donné pour ce capteur et que l’utilisateur a choisi de ne pas le répéter
* Lorsqu'un capteur était hors seuil précédemment et revient dans ses seuils, les actions "Actions arrêt l'avertissement - pour chaque capteur de retour dans les seuils, je dois ?" seront alors exécutées pour chacun des capteurs
* Si tous les capteurs sont évalués "dans les seuils", les actions "Actions arrêt l'avertissement - lorsque tous les capteurs sont dans les seuils, je dois ?" seront alors exécutées


Si l'une de vos action est de type "message", vous pouvez utiliser les tags suivants :
  * #nom_personne# : le nom configuré dans l'onglet "Général"
  * #nom_capteur# : le nom du capteur ayant déclenché l'avertissement
  * #type_capteur# : le type de ce capteur
  * #valeur# : sa valeur courante
  * #seuil_bas# : le seuil bas défini
  * #seuil_haut# : le seuil haut défini
  * #unite# : l'unité correspondant à la valeur

![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Confort.png)

Onglet **Sécurité**
---

Cet onglet permet de regrouper les capteurs du logement pour les urgences : détecteur de fumée, alerte fuite de gaz, … pour la personne dans son logement mais aussi avertir immédiatement l’extérieur dans le cas où la personne ne peut déjà plus réagir.

* Définir un ou plusieurs capteurs de sécurité. L'alerte sera enclenchée à chaque changement d'état du capteur, peu importe que ca soit 0->1 ou 1->0.
* Définir les actions qui seront immédiatement réalisées à l'activation de n'importe lequel des capteurs.
* Définir un ou plusieurs capteurs de type "bouton" ou "interrupteur" qui serviront à annuler l'alerte
* Définir les actions qui seront réalisées à l'activation des capteurs d'annulation.

Si l'une de vos action est de type "message", vous pouvez utiliser le tag #nom_personne# qui enverra le nom configuré dans l'onglet "Général"

**A améliorer : ajouter les tags #nom_capteur# et #type_capteur#**

![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Onglet_securité.png)

Onglet **Dérive comportementale**
---

To Do

Prévu à ce jour : faire une liste de cases à cocher selon les risques que l’on souhaite suivre pour cette personne, avec les risques suivants (liste à compléter, toute suggestion sera bienvenue !) :

* Rythme de vie : suivre les horaires de lever/coucher/sieste
* Isolement : suivre le nombre de sorties par jour/semaines (éventuellement liées a la météo)
* Infection urinaire : ici on pourra surveiller 2 capteurs (au choix ou les 2 ensemble) : Nombre de lever la nuit et Nombre de chasse d’eau jour et nuit.
* Alimentation : suivre le Nombre d’ouverture du frigo / jour et les tranches horaires pour voir si décalage dans la prise des repas avec alerte si le frigo n’a pas été ouvert bien que la personne soit présente.


Onglet **Avancé - Commandes Jeedom**
---

Panneau desktop
================
To Do - Il permettra de suivre les différents capteurs et visualiser les alertes

Comportement au démarrage et après redémarrage Jeedom
======

Fonction de **Détection d'inactivité**
---
* Après la création et la 1ere sauvegarde, il faut initialiser le mécanisme en déclenchant l'un des capteur d'activité. Les actions d' "avertissement" vont se déclencher dans la 1ere minute après la 1ere sauvegarde si aucun capteur n'a été activé.
* Après un redémarrage de Jeedom, le système aura perdu l'information de la date du dernier capteur d'activité et si les alarmes ont déjà été envoyées, les actions warnings vont donc se déclencher. Si le redémarrage est prévu, pensez à activer un des capteurs d'activité dans la 1ere minute.


Fonction **Bouton d'alerte**
---
RAS

Fonction **Confort**
---
RAS

Fonction **Sécurité**
---
RAS

Remarques générales
===
* Pour les capteurs "détections d'inactivité", "bouton d'alerte", "bouton d'annulation d'alerte", "capteur de sécurité" et "bouton d'annulation d'alerte de sécurité", c'est le changement de valeur du capteur qui est détecté et déclenche les actions, la valeur (0 ou 1) n'est pas prise en compte !
* Pour les capteurs conforts, leur valeur est évaluée toutes les 15 min et non à chaque changement de l'une d'elle
* Pour tous les capteurs définis dans le plugin, il faut donner un nom unique. Le changement de nom d'un capteur revient à le supprimer et a en recréer un nouveau, vous perdez donc l'historique associé.

Exemples d'usage et configuration associée
========================

Bonus : configuration pour recevoir les alertes par notification sur un smartphone android
========================

Bonus 2 : configuration pour mettre un jour un widget sur un smartphone android selon les infos de ce plugin
========================
