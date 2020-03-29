Présentation
============

Ce plugin fait parti d'un ensemble de plugins pour Jeedom permettant l'aide au maintien à domicile des personnes âgées : SeniorCare.

La demande initiale vient de ce sujet sur le forum : [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111).

Ce plugin permet la détection d’inactivité, ses principales fonctionnalités sont les suivantes :
* Gestion d'une quantité illimitée de capteurs d'activité avec un délai individuel
* Gestion d'actions d'alertes séquentielles
* Gestion Accusé de Réception de l'alerte et liste d'actions associées
* Gestion annulation de l'alerte avec liste d'actions associées
* Gestion absence de la personne via le plugin Agenda ou des capteurs ou des appels externes

Les capteurs d'activité peuvent être de n'importe quel type : interrupteur mural, détecteur de mouvement, détecteur de porte/fenêtre, ...

Les actions peuvent être n'importe quelle action Jeedom : gestion lampe, avertisseur sonore, notification sur smartphone, sms, email, message vocal, ...

Lien vers le code source : [https://github.com/AgP42/seniorcareinactivity/](https://github.com/AgP42/seniorcareinactivity/)

Si vous souhaitez participer au projet, n’hésitez pas à le faire savoir ici [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111)

Avertissement
==========

Ce plugin a été conçu pour apporter une aide aux personnes souhaitant rester chez elles et à leurs aidants.
Nous ne pouvons toutefois pas garantir son bon fonctionnement ni qu'un dysfonctionnement de l’équipement domotique n'arrive au mauvais moment.
Merci de l'utiliser en tant que tel et de ne pas prendre de risque pour la santé de ceux que nous cherchons à aider !
Ce plugin est gratuit et open source, il est fourni sans garanti de bon fonctionnement.

Changelog
==========

Ce plugin est en cours de développement, il est encore en versions beta pour tests.

Voir le [Changelog](https://agp42.github.io/seniorcareinactivity/fr_FR/changelog)

Principe de fonctionnement
========================

Le principe est le suivant :
* Le logement de la personne âgée est équipé d'une multitude de capteurs, au déclenchement de l'un d'eux, une temporisation correspondant à ce capteur est lancée. Ceci permet une temporisation plus courte dans la salle de bain que le salon par exemple. Chaque détection dans le logement relancera la temporisation. Si la temporisation arrive à échéance, le mécanisme d'alerte est lancé.
* Vous pouvez alors définir des actions à réaliser dans le logement (changement de couleur d'une lampe, alerte sonore, ...) ainsi que des actions vers un ou plusieurs aidants extérieurs. Ces actions peuvent être des notifications sur leur téléphone, un sms, un email, ... Ces différentes actions peuvent être réalisées immédiatement ou être retardées. Ceci permet de définir plusieurs personnes à avertir successivement tant que l'alerte n'a pas été prise en compte par l'un d'eux. Il est aussi possible de définir la 1ere alerte dans le logement de la personne pour lui laisser le temps de réagir (en activant n'importe lequel des capteurs du logement) avant que l'alerte ne soit lancée vers les aidants. Merci de faire attention à ce que ceci ne soit pas stressant pour la personne.
* Les aidants peuvent accuser réception de l'alerte, ce qui aura pour effet de déclencher des actions spécifiques (changer la couleur de la lampe dans le logement de la personne pour la prévenir que l'alerte a été prise en compte par exemple). A la réception d'un AR, les actions d'alerte programmés qui n'ont pas encore été exécutées sont annulées. Ceci permet de couper la chaîne d'alerte. Il est possible de définir des actions à ne réaliser que si une action d'alerte précédente a été exécutée. Ceci permet de prévenir les autres personnes ayant reçu l'alerte que quelqu'un en a accusé réception par exemple.
* Une fois l’aidant sur place ou si la personne a réagit entre-temps, des actions d'annulation sont définis et la chaîne d'alerte est coupée. L'activation de n'importe quel capteur permet de couper l'alerte.
* Une gestion d'absence est disponible pour ne pas déclencher d'alerte alors que la personne n'est pas présente dans son logement. Il est possible de déclarer une absence avec le plugin agenda, via des scenarios jeedom, via n'importe quel plugin jeedom, via un appel extérieur via l'API, ou via des boutons dans le logement.

Configuration du plugin
========================

Ajouter les différentes personnes à suivre puis, pour chacune, configurer les différents onglets.

Onglet **Général**
---

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletGeneral.png)

* **Informations Jeedom**
   * Indiquer le nom de la personne
   * Objet parent : il s'agit de l'objet Jeedom auquel rattacher la personne. Il doit être différent de "Aucun"
   * Activer le plugin pour cette personne
   * Visible sert a visualiser les infos sur le dashboard. Vous pouvez choisir d'afficher les boutons d'AR ou de déclaration d'absence si besoin

* **Informations concernant la personne dépendante**

Vous pouvez saisir ici des informations sur la personne dépendante. Ces informations seront utilisées uniquement pour la saisie de tags dans les messages d'alertes, tous ces champs sont facultatifs.

Onglet **Gestion absences**
---

Une gestion d'absence est disponible pour ne pas déclencher d'alerte alors que la personne n'est pas présente dans son logement. Il est possible de déclarer une absence avec le plugin agenda, via des scenarios jeedom, via n'importe quel plugin jeedom, via un appel extérieur via l'API, ou via des boutons dans le logement.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletAbsence.png)

### Avec le plugin **agenda**
   * **Note** : si vous ne disposez pas du plugin agenda sur votre jeedom, cette zone ne s'affichera pas
   * Vous devez configurer l'absence directement dans le plugin agenda qui doit lancer en action de début la commande "Déclarer absence"
   * Les actions d'absences programmées dans le plugin agenda seront affichées dans cet onglet avec un lien
   * Il n'est pas nécessaire de déclarer une action de fin, toute activité dans le logement sera considérée comme un retour et relancera le mécanisme de détection. Attention donc si la personne n'est pas encore absente lorsque le plugin agenda déclare l'absence, l'absence sera annulée par ses activités !
   * Si vous voulez forcer le mode "absence" avec le plugin agenda, vous pouvez définir une répétition du message d'absence toutes les 5 min pendant une période donnée :
   ![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/agenda.png)

### Avec un bouton et un délai
   * Vous pouvez configurer directement dans le plugin une liste de bouton qui déclareront l'absence à l'échéance du délai configuré.
   * Ce délai permet de sortir du logement sans que les dernières actions soient détectées comme un retour. Notamment le capteur de fermeture de porte ou le capteur de mouvement qui reviendrait à son état initial

### Avec n'importe quel autre plugin jeedom, dont le plugin **Mode** par exemple ou avec un scenario
   * Définissez comme action la commande "Déclarer absence" pour appeler la fonction d'absence

### Via un appel extérieur
   * Utiliser le lien donné pour appeler cette fonction via un smartphone, IFTTT ou n'importe quel autre équipement.
   * Vous pouvez tester le lien donné en cliquant directement dessus
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée ici soit fonctionnelle.

Onglet **Capteur d'activité**
---

Il s'agit ici de déclarer les capteurs concernant l’activité de la personne (ouverture, interrupteur, mouvement, …) et un délai associé pour chacun. Si aucun des capteurs d'activité n'a été activé à l’échéance du délai, le plugin déclenchera l’étape suivante « Actions d'alertes ».

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/dev/docs/assets/images/OngletCapteurs.png)

Pour chaque capteur, saisir :
* **Nom** : champs obligatoire
* **Capteur** : champs obligatoire
* **Délai avant alerte (min)** : il s'agit de la durée de la temporisation relancée par ce capteur. Cette temporisation sera relancée du même délai que l'état du capteur soit 0 ou 1. C'est à dire pour un détecteur de mouvement par exemple que le début de détection ou la fin de détection relancera la temporisation de la même durée. Ou l'ouverture et la fermeture d'une porte.

Pour vous aider à configurer vos capteurs, vous pouvez activer les logs en mode "Info" qui afficheront en temps réel les différents capteurs détectés par le plugin :

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/dev/docs/assets/images/logInfo.png)

> Il est important de noter que la performance du plugin dépendra beaucoup du choix des capteurs et de leur installation pertinente dans le logement.
> Plus le nombre de capteurs sera élevé, meilleure sera la performance.
> Il est nécessaire de faire des essais de capteurs et des délais associés et de réaliser une période de test sans générer d'alerte pour la personne âgée.

**A améliorer : le délai de détection d'inactivité selon jour ou nuit ?**


Onglet **Actions d'alerte**
---

Cet onglet permet de définir les actions à déclencher à l'échéance des délais de détection d'activité.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletActions.png)

* Cliquer sur "ajouter une action" pour définir une ou plusieurs actions
* **Label** : Champs facultatif permettant de lier cette action aux actions lors de la réception d'un accusé de réception ou d'annulation.
* **Délai avant exécution (min)** :
   * ne pas remplir ou 0 : cette action sera exécutée immédiatement.
   * valeur supérieure à 0 : cette action sera enregistrée dans le moteur de tâches Jeedom (cron) pour une exécution différée selon le délai saisi.
   * le délai doit être saisi par rapport au déclenchement de l'alerte. Si vous souhaitez 3 actions, l'une immédiate puis 10 min après puis 10 min après, il faudra saisir 0, 10 et 20.
* **Action** : la commande jeedom correspondant à l'action voulue. L'action peut être de n'importe quel type : une lampe du logement, un message vers les aidants, l'appel d'un scenario jeedom, ...

Remarques :
* Dans le cas d'un redémarrage de Jeedom alors que des actions sont enregistrées, les actions seront réalisées dès le lancement de Jeedom (si l'heure de l'action est dépassée).
* Lors de l'enregistrement ou de la suppression, si des actions étaient enregistrées, elles seront supprimées avec un message d'erreur donnant le nom de la personne
* Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**
* Pour prévenir la personne qu'une inactivité est détectée et lui laisser la possibilité de désactiver l'alerte avant qu'elle ne soit transmise aux aidants, définissez les actions vers la personne âgée avec un délai de 0 puis les actions vers l'extérieur après le délai voulu
* Tout capteur activé pendant la période d'alerte annulera cette alerte et réinitialisera le mécanisme de surveillance

Onglet **Accusé de réception** (AR)
---
Cet onglet fourni l'URL à appeler pour déclencher l'Accusé de Réception et il permet de définir les actions à réaliser lors de la réception de cet AR

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletAR.png)

* **Commande à appeler depuis l'extérieur pour accuser réception de l'alerte**
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée soit fonctionnelle.
   * Vous pouvez cliquer sur le lien pour tester son bon fonctionnement
   * Cet URL peut être appelé par n'importe quel équipement extérieur, notamment un smartphone
* **Actions à la réception d'un accusé de réception**
   * **Label action de référence** :
      * Vous pouvez ici saisir le label de l'action de référence de l'onglet "Actions d'alerte".
      * Le label saisi doit être strictement identique, attention aux espaces.
      * Lorsque le label est renseigné et correspond à une action d'alerte, il faut que l'action d'alerte de référence ait été précédemment lancée pour que la présente action s'exécute.
      * Attention, si vous renseignez un label qui n'existe pas (et donc ne sera jamais exécuté), l'action liée ne s'exécutera jamais.
      * Exemple 1 : l'action d'alerte est d'envoyer un message à Mr x, 30 min après la détection d'inactivité (une alerte immédiate vers un autre aidant étant définie par ailleurs). L'action lors de l'AR est d'envoyer un message à Mr x pour le prévenir que quelqu'un a accusé réception de l'alerte. L'action d'AR ne sera exécutée que si l'action d'alerte initiale avait été exécutée à la fin de son délai de 30min. Ceci permet de ne pas envoyer des messages lors d'un AR alors que la personne n'avait pas reçu le message d'alerte initial.
      * Exemple 2 : l'action d'alerte est d'allumer immédiatement une lampe en orange (signaler à la personne que le système a détecté une inactivité), puis en rouge (signaler à la personne que l'alerte a été transmise aux aidants. L'action d'AR est de passer cette lampe en vert lorsqu'un aidant a accusé réception de l'alerte. Il n'est ici pas nécessaire de définir un label pour les lier, car il n'y a pas de risque d'annuler une action n'ayant jamais eu lieu.
   * **Action** : la commande jeedom correspondant à l'action voulue. L'action peut être de n'importe quel type : une lampe du logement, un message vers les aidants, l'appel d'un scenario jeedom, ... Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**

Lors de la réception d'un accusé de réception, toutes les actions d'alertes "futures" sont annulées.

Onglet **Annulation d'alerte**
---
Cet onglet permet de configurer les actions d'annulation d'alerte. Il s'agit ici de désactiver le mécanisme d'alerte lorsqu'un aidant arrive dans le logement ou si la personne déclenche elle-même un capteur d'activité.
Tout capteur d'activité déclenché pendant une alerte en cours annulera l'alerte et lancera les actions définies dans cet onglet.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletAnnulation.png)

* Définir les actions qui seront réalisées pour les annulations d'alerte. Le fonctionnement des labels est identique aux actions de l'onglet **Accusé de réception**.

Si l'une de vos action est de type "message", vous pouvez utiliser les tags configurés dans l'onglet "Général".
Lors de l'annulation, toutes les actions d'alertes "futures" sont annulées.

Onglet **Avancé - Commandes Jeedom**
---

Vous pouvez configurer ici les commandes utilisées par ce plugin. Vous pouvez notamment définir la visibilité du bouton d'accusé de réception sur le dashboard Jeedom (pour tests notamment) ainsi que le bouton de déclaration d'absence de la personne.
Les autres commandes correspondent aux différents capteurs.


Panneau desktop
================

To Do - Il permettra de suivre les différents capteurs et visualiser les alertes

Remarques sur le comportement du plugin
======

Comportement au démarrage et après redémarrage Jeedom
---

* Après création et première sauvegarde, le déclenchement de l'un des capteur d'activité initialise le mécanisme. En cas de non-déclenchement de l'un de ces capteurs d'activité dans cette minute, les actions d'alerte vont se lancer.
* Après un redémarrage de Jeedom, le système aura perdu l'information de la date du dernier capteur d'activité, si des alarmes ont déjà été envoyées ainsi que l'information d'absence de la personne. Les actions vont donc se déclencher. En cas de redémarrage de Jeedom, vous devrez penser à activer un des capteurs d'activité dans la première minute.

Infos capteurs
---
* Pour les capteurs de "détections d'inactivité", c'est le changement de valeur du capteur qui est détecté et relancera la temporisation, la valeur en elle-même n'est pas prise en compte !
   * Pour un capteur de mouvement, cela signifie que la détection (passage du capteur de 0->1) déclenchera la temporisation, mais aussi lors du retour à l'état 0 (pas de mouvement)
   * Pour un capteur de porte, l'ouverture et la fermeture relanceront la temporisation
* L'ensemble des capteurs définis dans le plugin doivent posséder un nom unique. Le changement de nom d'un capteur revient à le supprimer et à en créer un nouveau, la totalité de l'historique associé à ce capteur sera donc perdue.

Exemples d'usage et configuration associée
========================

Bonus : configuration pour recevoir les alertes par notification sur un smartphone android
========================

Bonus 2 : configuration pour mettre un jour un widget sur un smartphone android selon les infos de ce plugin
========================
