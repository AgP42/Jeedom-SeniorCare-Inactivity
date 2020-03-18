Présentation
============

Ce plugin pour Jeedom permet d’aider au maintien a domicile pour les personnes agées. La demande initiale vient de ce sujet sur le forum : [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111/2). Les principales fonctionnalités (à ce jour) :
* Détection d’inactivité
* Gestion de boutons d’alertes
* Surveillance du confort (température, humidité, CO2, …)
* Surveillance sécurité (détecteurs de fumée, de fuite de gaz, …)
* Surveillance de « Dérive comportementale » pour essayer de détecter et prendre en charge au plus tôt et donc au mieux les soucis inhérent à la vieillesse

Lien vers le code source : https://github.com/AgP42/seniorcare/

Si vous souhaitez participer au projet, n’hésitez pas à le faire savoir ici [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111/2)

Avertissement
==========

Ce plugin a été concu pour apporter une aide aux personnes souhaitant rester chez elles et à leurs aidants. Toutefois nous ne pouvons garantir son bon fonctionnement ni qu'un "plantage" de l'équipemnent domotique n'arrive justement au mauvais moment. Merci de l'utiliser en tant que tel et de ne pas prendre de risque pour la santé de ceux que nous cherchons à aider !

Changelog
==========

Ce plugin est en cours de developpement, toutes les fonctions ne sont pas encore codées, certaines n'ont été que partiellement testées. 

* Version Beta 0.0.1 - 18 mars 2020 contient : 
  - Fonction "Détection d'inactivitées"
  - Fonction "Bouton d'alerte"
  - Fonction "Capteurs confort"

Configuration du plugin
========================

Ajouter les différentes personnes à suivre. Puis pour chacune configurer les différents onglets.

Onglet Général
---
Donner le nom de la personne. 
"Objet parent" : il s'agit de l'objet Jeedom auquel rattacher la personne. Il doit etre différent de "Aucun".
Activer la personne. 
Visible sert a visualiser les infos sur le dashboard, pour l'instant il n'y a rien a visualiser.

Onglet **Gestion absences**
---

To Do

L’objectif ici étant de lier ce plugin au plugin « Agenda » pour les jours d’absences régulier et les vacances. Et d’ajouter les capteurs adéquats pour savoir si la personne est présente ou non pour les absences ponctuelles. Si la personne est absente, il faut désactiver la fonction de détection d’inactivité notamment.

Onglet **Détection d'inactivité**
---
3 étapes de fonctionnement :

1. Définir des capteurs de détection d’activité, quelle qu’elle soit (porte, fenêtre, interrupteur, mouvement, …). Et un timer associé. Si aucun des capteurs d'activité n'a été activé à l’échéance du timer : le plugin passera à l’étape « Avertissement »
2. « Avertissement » : 
  * définir une liste d’actions pour prévenir la personne que le système a détecté une inactivité et lui permettre de réagir avant que l’alerte soit envoyée aux aidants extérieurs. 
  * Définir la durée maximum pendant laquelle la personne pourra réagir avant de déclencher l'alerte. 
  * Puis définir les actions pour cette étape : 
    * les actions pour lancer l’avertissement 
    * et les actions pour couper l’avertissement si la personne réagit (la réaction de la personne est détectée par n’importe lequel des capteurs définis à l’étape 1). Si la personne ne réagit pas et que le plugin passe a l'étape "Alerte" à la fin du timer, ces actions pour "couper l'avertissement" ne seront pas réalisées !
3. « Alerte » : 
  * Définir la liste d’actions pour lancer l’alerte aux aidants et avertir la personne qu’une alerte est en cours. 
  * Définir la liste d’action pour désactiver ces actions d’alerte. L'alerte est déactivée par n’importe quel capteur de l’étape 1. Donc la personne dans son logement peut le faire elle-meme, ou un aidant en entrant dans le logement (si le capteur de porte d’entrée est un « capteur de détection d’activité » défini à l’étape 1)
  
![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Detection_inactivite.png)

Onglet **Bouton d'alerte**
---
* Définir un ou plusieurs capteurs de type "bouton" ou "interupteur" 
* Définir les actions qui seront immédiatement réalisées à l'activation de n'importe quel capteur. 
Si l'une de vos action est de type "message", vous pouvez utiliser le tag #nom_personne# qui enverra le nom configuré dans l'onglet "Général"
![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Boutons_alerte.png)

Onglet **Confort**
---
* Définir les différents capteurs de confort du logement que vous souhaitez suivre. Il peut s'agit de capteurs de Température, d'humidité, de CO2 ou de tout autre type. 
  * Vous devez donner un nom unique à chacun de vos capteurs. Attention : le changement de nom d'un capteur revient à le supprimer et a en recréer un nouveau, vous perdez donc l'historique associé. 
  * Sélectionner le capteur associé
  * Définir les seuils haut et bas
* Définir les actions qui seront executées lors du depassement d'un seuil. Si l'une de vos action est de type "message", vous pouvez utiliser les tags suivants : 
  * #nom_personne# qui enverra le nom configuré dans l'onglet "Général"
  * #nom_capteur#
  * #type_capteur#
  * #valeur#
  * #seuil_bas# 
  * #seuil_haut#
  * #unite#
  
![](https://raw.githubusercontent.com/AgP42/seniorcare/dev/docs/assets/images/Confort.png)

Onglet **Sécurité**
---

To Do

L’idée ici est de regrouper les capteurs du logement sur les urgences : détecteur de fumée, alerte fuite de gaz, … pour la personne dans son logement mais aussi immédiatement l’extérieur au cas où la personne ne peut déjà plus réagir.

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

Exemples d'usage et configuration associée
========================

Bonus : configuration pour recevoir les alertes par notification sur un smartphone android
========================

Bonus 2 : configuration pour mettre un jour un widget sur un smartphone android selon les infos de ce plugin
========================
