Présentation
============

Ce plugin fait parti d'un ensemble de plugins pour Jeedom permettant l'aide au maintien à domicile des personnes âgées : SeniorCare.

La demande initiale vient de ce sujet sur le forum : [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111/2).

Ce plugin permet :
* Détection d’inactivité

Lien vers le code source : [https://github.com/AgP42/seniorcareinactivity/](https://github.com/AgP42/seniorcareinactivity/)

Si vous souhaitez participer au projet, n’hésitez pas à le faire savoir ici [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111/2)

Avertissement
==========

Ce plugin a été conçu pour apporter une aide aux personnes souhaitant rester chez elles et à leurs aidants.
Nous ne pouvons toutefois pas garantir son bon fonctionnement ni qu'un dysfonctionnement de l’équipement domotique n'arrive au mauvais moment.
Merci de l'utiliser en tant que tel et de ne pas prendre de risque pour la santé de ceux que nous cherchons à aider !

Changelog
==========

Ce plugin est en cours de développement, toutes les fonctions ne sont pas encore codées, certaines n'ont été que partiellement testées.

Beta 0.0.1 - 24 mars 2020
---

* Gestion de détection d'inactivité
* Création documentation

Configuration du plugin
========================

Ajouter les différentes personnes à suivre, puis pour chacune configurer les différents onglets.

Onglet Général
---
* Indiquer le nom de la personne
* "Objet parent" : il s'agit de l'objet Jeedom auquel rattacher la personne. Il doit être différent de "Aucun"
* Activer le plugin pour cette personne
* Visible sert a visualiser les infos sur le dashboard : il n'y a aucune information à visualiser pour l'instant

Onglet **Gestion absences**
---
To Do
L’objectif ici étant de lier ce plugin au plugin « Agenda » pour les jours d’absence réguliers et les vacances.
Il permet également d’ajouter les capteurs adéquats pour savoir si la personne est présente ou non pedant les absences ponctuelles.
En cas d'absence, il convient de désactiver entre autres la fonction de détection d’inactivité.

Onglet **Détection d'inactivité**
---
Il s'agit ici de déclencher une alerte en cas d'absence d’activité de la personne, cas d'un malaise ou d'une chute par exemple.

**A améliorer : le délai de détection d'inactivité selon jour ou nuit**

Trois étapes de fonctionnement :

1. Définir les capteurs de détection d’activité (ouverture porte, fenêtre, interrupteur, mouvement, …) et un délai associé. Si aucun des capteurs d'activité n'a été activé à l’échéance du délai, le plugin déclenchera l’étape suivante « Avertissement ».
2. « Avertissement » :
  * L'objectif de cette étape est de prévenir la personne que le système a détecté une inactivité et lui permettre de réagir avant que l’alerte ne soit envoyée aux aidants extérieurs
  * Configuration à réaliser :
    - définir la liste des actions à lancer pour l’avertissement
    - définir la durée maximum pendant laquelle la personne pourra réagir avant de déclencher l'alerte
    - définir les actions permettant d'annuler l’avertissement si la personne réagit (la réaction de la personne est détectée par n’importe quel capteur définis à l’étape 1)
  * Si la personne ne réagit pas dans le délai imparti, le plugin passera a l'étape "Alerte". Dans ce cas, les actions pour "annuler l'avertissement" ne seront pas réalisées.
3. « Alerte » :
  * Définir la liste des actions à lancer afin d’alerter les aidants et informer la personne dépendante qu’une alerte est en cours
  * Définir la liste d’actions permettant de désactiver ces actions d’alerte. L'alerte est déactivée par n’importe quel capteur de l’étape 1. La personne dans son logement ou un aidant une fois sur place pourront donc le faire

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/dev/docs/assets/images/Detection_inactivite.png)

Onglet **Avancé - Commandes Jeedom**
---

Panneau desktop
================

To Do - Il permettra de suivre les différents capteurs et visualiser les alertes

Comportement au démarrage et après redémarrage Jeedom
======

Fonction de **Détection d'inactivité**
---
* Après création et première sauvegarde, le déclenchement de l'un des capteur d'activité initialise le mécanisme. En cas de non-déclenchement de l'un de ces capteurs d'activité dans cette minute, les actions d' "avertissement" vont se lancer.
* Après un redémarrage de Jeedom, le système aura perdu l'information de la date du dernier capteur d'activité et si des alarmes ont déjà été envoyées. Les actions warnings vont donc se déclencher. Aussi, en cas de redémarrage de Jeedom, vous devrez penser à activer un des capteurs d'activité dans la première minute.


Remarques générales
===
* Pour les capteurs de "détections d'inactivité", c'est le changement de valeur du capteur qui est détecté et déclenche les actions, la valeur en elle-même n'est pas prise en compte !
* L'ensemble des capteurs définis dans le plugin doivent posséder un nom unique. Le changement de nom d'un capteur revient à le supprimer et à en créer un nouveau. De fait, la totalité de l'historique associé à ce capteur sera donc perdu.

Exemples d'usage et configuration associée
========================

Bonus : configuration pour recevoir les alertes par notification sur un smartphone android
========================

Bonus 2 : configuration pour mettre un jour un widget sur un smartphone android selon les infos de ce plugin
========================
