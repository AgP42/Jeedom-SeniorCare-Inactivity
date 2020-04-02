Présentation
============

Ce plugin fait parti d'un ensemble de plugins pour Jeedom permettant l'aide au maintien à domicile des personnes âgées : SeniorCare.

La demande initiale vient de ce sujet sur le forum : [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111).

Ce plugin permet la détection d’inactivité, ses principales fonctionnalités sont les suivantes :
* Gestion d'une quantité illimitée de capteurs d'activité avec pour chacun 4 délais possible selon la valeur du capteur et la période jour/nuit en cours
* Gestion d'actions d'alertes séquentielles
* Gestion Accusé de Réception de l'alerte et liste d'actions associées
* Gestion annulation de l'alerte avec liste d'actions associées
* Gestion absence de la personne via le plugin Agenda ou des capteurs ou des appels externes
* Gestion jour/nuit via le plugin Agenda ou autres appels externes

Les capteurs d'activité peuvent être de n'importe quel type : interrupteur mural, détecteur de mouvement, détecteur de porte/fenêtre, ...

Les actions peuvent être n'importe quelle action Jeedom : gestion lampe, avertisseur sonore, notification sur smartphone, sms, email, message vocal, ...

Lien vers le code source : [https://github.com/AgP42/seniorcareinactivity/](https://github.com/AgP42/seniorcareinactivity/)

Si vous souhaitez participer au projet, n’hésitez pas à le faire savoir ici [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111)

Avertissement
==========

Ce plugin a été conçu pour apporter une aide aux personnes âgées ou dépendante et à leurs aidants.
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
* Le logement de la personne âgée est équipé d'une multitude de capteurs. Au déclenchement de l'un d'entre eux, une temporisation correspondant à ce capteur est lancée. Ceci permet une temporisation plus courte dans la salle de bain que le salon par exemple. Chaque détection dans le logement relancera la temporisation. Si la temporisation arrive à échéance, le mécanisme d'alerte est enclenché.
* Vous pouvez alors définir des actions à réaliser dans le logement (changement de couleur d'une lampe, alerte sonore, ...) ainsi que des actions vers un ou plusieurs aidants extérieurs. Ces actions peuvent être des notifications sur leur téléphone, un sms, un email, ... Ces différentes actions peuvent être à exécution immédiate ou différée. Celà permet de définir plusieurs personnes à prévenir successivement tant que l'alerte n'a pas été prise en compte par l'une d'entre elles. Il est aussi possible de définir la 1ère alerte dans le logement de la personne afin de lui laisser le temps de réagir (en activant n'importe lequel des capteurs du logement) avant que l'alerte ne soit lancée vers les aidants. Il est fortement conseillé de faire en sorte que cette alerte ne soit pas anxiogène pour la personne dépendante.
* Les aidants peuvent accuser réception de l'alerte. Cela aura pour effet de déclencher des actions spécifiques (changer la couleur de la lampe dans le logement de la personne pour l'informer de la prise en compte par exemple). A la réception d'un AR, les actions d'alerte programmées n'ayant pas encore été exécutées sont annulées. Ceci permet de couper la chaîne d'alerte. Il est possible de définir des actions à ne réaliser que si une action d'alerte précédente a été exécutée, Ceci afin de prévenir les autres personnes ayant reçu l'alerte que quelqu'un en a déjà accusé réception par exemple.
* Une fois l’aidant sur place ou si la personne a réagit entre-temps, des actions d'annulation sont définies et la chaîne d'alerte est coupée. L'activation de n'importe quel capteur permet d'interrompre l'alerte.
* Une gestion d'absence est disponible afin de ne pas déclencher d'alerte alors que la personne n'est pas présente dans son logement. Ces absences peuvent être déclarées de différentes façons (plugin agenda, scénarios ou plugins Jeedom, appel extérieur via l'API, des boutons dans le logement, ...).
* Il est possible de définir jusqu'à 4 valeurs de temporisation par capteur, pour prendre en compte l'état du capteur (0 ou 1) ainsi que la période de la journée (jour/nuit)

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

Une gestion d'absence est disponible pour ne pas déclencher d'alerte alors que la personne est absente de son logement. Il est possible de déclarer une absence avec le plugin agenda, via des scenarios Jeedom, via n'importe quel plugin Jeedom, via un appel extérieur via l'API ou via des boutons dans le logement.

Dans le cas où une alerte était en cours, elle sera annulée (actions d'annulation d'alerte) à la déclaration de l'absence.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletAbsence.png)

### Avec le plugin **agenda**
   * **Note** : si vous ne disposez pas du plugin agenda sur votre Jeedom, cette zone ne s'affichera pas
   * Vous devez configurer l'absence directement dans le plugin agenda qui doit lancer en action de début la commande "Déclarer absence"
   * Les actions d'absences programmées dans le plugin agenda seront affichées dans cet onglet avec un lien
   * Il n'est pas nécessaire de déclarer une action de fin, toute activité dans le logement sera considérée comme un retour et relancera le mécanisme de détection. Il est à noter que si la personne est encore présente alors que le plugin agenda déclare l'absence, l'absence sera annulée par ses activités.
   * Si vous voulez forcer le mode "absence" avec le plugin agenda, vous pouvez définir une répétition du message d'absence toutes les 5 min pendant une période donnée :
   ![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/agenda.png)

### Avec un bouton et un délai
   * Vous pouvez configurer directement dans le plugin une liste de bouton qui déclareront l'absence à l'échéance du délai configuré.
   * Ce délai permet de sortir du logement sans que les dernières actions soient détectées comme un retour. Notamment le capteur de fermeture de porte ou le capteur de mouvement qui reviendrait à son état initial
   * Le délai n'est pas pris en compte en minutes pleines, mais en début de minute. Par exemple si vous demandez un délai de 1min, le capteur est déclenché à 12h05m54s, alors l'absence sera déclarée en début de la minute à 12h06 (selon la charge de votre jeedom). Si le capteur est déclenché à 12h10m03, alors l'absence sera déclarée en début de min à 12h11. Ainsi un timer d'1min peut correspondre à un délai réel de quelques secondes, à maximum 60s.
   * Si plusieurs capteurs sont déclarés ou s'ils sont déclenchés plusieurs fois, la temporisation est relancée à chaque fois.

### Avec n'importe quel autre plugin Jeedom, dont le plugin **Mode** par exemple ou avec un scenario
   * Définissez comme action la commande "Déclarer absence" pour appeler la fonction d'absence

### Via un appel extérieur
   * Utiliser le lien donné pour appeler cette fonction via un smartphone, IFTTT ou n'importe quel autre équipement.
   * Vous pouvez tester le lien donné en cliquant directement dessus
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée ici soit fonctionnelle.

Onglet **Périodes jour/nuit**
---

La notion de "jour" ou "nuit" permet de définir des valeur de temporisation par capteur différente selon l'heure de la journée. Il est possible de définir plusieurs périodes "nuit" dans une journée, par exemple pendant la sieste.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletJourNuit.png)

### Avec le plugin **agenda**
   * **Note** : si vous ne disposez pas du plugin agenda sur votre Jeedom, cette zone ne s'affichera pas
   * Vous devez configurer les périodes directement dans le plugin agenda qui doit lancer en action de début et de fin l'une des commande "Déclarer jour" ou "Déclarer nuit"
   * Les actions jour/nuit programmées dans le plugin agenda seront affichées dans cet onglet avec un lien

### Avec n'importe quel autre plugin Jeedom, dont le plugin **Mode** par exemple ou avec un scenario
   * Définissez comme action les commandes "Déclarer jour" ou "Déclarer nuit"

### Via un appel extérieur
   * Utiliser les liens donnés pour appeler ces fonctions via un smartphone, IFTTT ou n'importe quel autre équipement.
   * Vous pouvez tester le lien donné en cliquant directement dessus
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée ici soit fonctionnelle.

Onglet **Capteur d'activité**
---

Il s'agit ici de déclarer les capteurs concernant l’activité de la personne (ouverture, interrupteur, mouvement, …) et des délais associés pour chacun. Si aucun capteurs d'activité n'a été activé à l’échéance du délai, le plugin déclenchera l’étape suivante « Actions d'alertes ».

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/dev/docs/assets/images/OngletCapteurs.png)

Pour chaque capteur, saisir :
* **Nom** : champs obligatoire
* **Capteur** : champs obligatoire
* **Délais** : il s'agit des différentes durée possible de la temporisation relancée par ce capteur. Saisir 0 ou laisser le champs vide pour ne pas considérer l’événement dans cette situation.
   * **Front montant** : lorsque ce capteur passe de l'état 0 à l'état 1. Pour un capteur de mouvement il s'agit généralement d'un début de détection. Pour un capteur de porte, il s'agit généralement de la fermeture de la porte.
   * **Front descendant** : lorsque ce capteur passe de l'état 1 à l'état 0. Pour un capteur de mouvement il s'agit généralement d'une fin de détection, c'est à dire typiquement une action qu'il n'est pas utile de prendre en compte, vous pouvez alors définir 0 ou ne pas remplir le champs pour que ce cas soit ignoré. Pour un capteur de porte il s'agit généralement de l'ouverture de la porte.
   * **Jour** : Correspond à la période "jour" déclarée dans l'onglet **Périodes jour/nuit**
   * **Nuit** : Correspond à la période "nuit" déclarée dans l'onglet **Périodes jour/nuit**

Pour vous aider à configurer vos capteurs, vous pouvez activer les logs en mode "Info" qui afficheront en temps réel les différents capteurs détectés par le plugin et les délais applicables dans la situation actuelle (il faut des délais non nuls dans la configuration pour afficher les logs correspondant) :

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/dev/docs/assets/images/logInfo.png)

> Il est important de noter que la performance du plugin dépendra beaucoup du choix des capteurs et de leur installation pertinente dans le logement.
> Plus le nombre de capteurs sera élevé, meilleure sera la performance.
> Il est nécessaire de faire des essais de capteurs et des délais associés et de réaliser une période de test sans générer d'alerte pour la personne âgée.

###Exemple

Dans la copie d'écran ci-dessus, 3 capteurs sont configurés, voici le comportement correspondant :
* Capteur **Porte** :
   * De jour, lors de l'ouverture ou la fermeture de la porte, la temporisation sera relancée de 10 minutes
   * De nuit, ce capteur ne sera pas considéré, l'ouverture ou la fermeture de la porte ne relancera pas la temporisation
* Capteur **Mouvement** :
   * De jour ou de nuit, la temporisation sera relancée de 20 minutes lors de la détection d'un mouvement. Attention, selon votre capteur il est possible que le capteur reste à l'état de détection pendant une longue période, dans ce cas seul le début de la détection est pris en compte. Il est préférable de choisir un capteur revenant régulierement à l'état inactif pour détecter le prochain mouvement.
   * De jour ou de nuit, lorsque le capteur repasse à l'état "pas de mouvement", il n'est pas considéré
* Capteur **Lit** :
   * De jour ou de nuit, lorsque le capteur détecte un coucher la temporisation est relancée pour 10h
   * De jour, le levé relance la temporisation de 10 min
   * De nuit, le levé relance la temporisation de 30 min

Onglet **Actions d'alerte**
---

Cet onglet permet de définir les actions à déclencher lors de la détection d'une inactivité (= à l'échéance de la temporisation du dernier capteur activé).

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletActions.png)

* Cliquer sur "ajouter une action" pour définir une ou plusieurs actions
* **Label** : Champs facultatif permettant de lier cette action aux actions définies pour la réception d'un accusé de réception ou d'annulation.
* **Délai avant exécution (min)** :
   * ne pas remplir ou 0 : cette action sera exécutée immédiatement.
   * valeur supérieure à 0 : cette action sera enregistrée dans le moteur de tâches Jeedom (cron) pour une exécution différée selon le délai saisi.
   * le délai doit être saisi par rapport au déclenchement de l'alerte. Si vous souhaitez 3 actions, l'une immédiate puis 10 min après puis 10 min après, il faudra saisir 0, 10 et 20.
* **Action** : la commande Jeedom correspondant à l'action voulue. L'action peut être de n'importe quel type : une lampe du logement, un message vers les aidants, l'appel d'un scenario Jeedom, ...

Remarques :
* Dans le cas d'un redémarrage de Jeedom alors que des actions sont enregistrées, les actions seront réalisées :
   * dès le lancement de Jeedom, si l'heure de l'action est dépassée
   * à leur heure de programmation si l'heure n'est pas encore dépassée. (Les actions enregistrées ne sont pas perdues)
* Lors de l'enregistrement ou de la suppression, si des actions étaient enregistrées, elles seront supprimées avec un message d'erreur donnant le nom de la personne
* Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**
* Pour prévenir la personne qu'une inactivité est détectée et lui laisser la possibilité de désactiver l'alerte avant qu'elle ne soit transmise aux aidants, définissez les actions vers la personne âgée avec un délai de 0 puis les actions vers l'extérieur après le délai voulu
* Tout capteur activé pendant la période d'alerte annulera cette alerte et réinitialisera le mécanisme de surveillance
* La déclaration de l' "absence" de la personne entraînera l'annulation des actions d'alertes en cours

Onglet **Accusé de réception** (AR)
---

Cet onglet fourni l'URL à appeler pour déclencher l'Accusé de Réception et il permet de définir les actions à réaliser lors de la réception de cet AR.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletAR.png)

* **Commande à appeler depuis l'extérieur pour accuser réception de l'alerte**
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée soit fonctionnelle
   * Vous pouvez cliquer sur le lien pour tester son bon fonctionnement
   * Cet URL peut être appelé par n'importe quel équipement extérieur, notamment un smartphone

* **Actions à la réception d'un accusé de réception**
   * **Label action de référence** :
      * Vous pouvez ici saisir le label de l'action de référence de l'onglet "Actions d'alerte"
      * Le label saisi doit être strictement identique, attention aux espaces
      * Lorsque le label est renseigné et correspond à une action d'alerte, il faut que l'action d'alerte de référence ait été précédemment lancée pour que la présente action s'exécute
      * Attention, si vous renseignez un label qui n'existe pas (et donc ne sera jamais exécuté), l'action liée ne s'exécutera jamais
      * Exemple 1 : l'action d'alerte est d'envoyer un message à Mr x, 30 min après la détection d'inactivité (une alerte immédiate vers un autre aidant étant définie par ailleurs). L'action lors de l'AR est d'envoyer un message à Mr x pour le prévenir que quelqu'un a accusé réception de l'alerte. L'action d'AR ne sera exécutée que si l'action d'alerte initiale avait été exécutée à la fin de son délai de 30min. Ceci permet de ne pas envoyer des messages lors d'un AR alors que la personne n'avait pas reçu le message d'alerte initial
      * Exemple 2 : l'action d'alerte est d'allumer immédiatement une lampe en orange (signaler à la personne que le système a détecté une inactivité), puis en rouge (signaler à la personne que l'alerte a été transmise aux aidants. L'action d'AR est de passer cette lampe en vert lorsqu'un aidant a accusé réception de l'alerte. Il n'est ici pas nécessaire de définir un label pour les lier, car il n'y a pas de risque d'annuler une action n'ayant jamais eu lieu.
   * **Action** : la commande jeedom correspondant à l'action voulue. L'action peut être de n'importe quel type : une lampe du logement, un message vers les aidants, l'appel d'un scenario Jeedom, ... Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**

Lors de la réception d'un accusé de réception, toutes les actions d'alertes "futures" sont annulées.

Onglet **Annulation d'alerte**
---

Cet onglet permet de configurer les actions d'annulation d'alerte. Il s'agit ici de désactiver le mécanisme d'alerte lorsqu'un aidant arrive dans le logement ou si la personne déclenche elle-même un capteur d'activité.

> Tout capteur d'activité déclenché pendant une alerte en cours annulera l'alerte et lancera les actions définies dans cet onglet.

> La déclaration de l' "absence" de la personne pendant une alerte en cours annulera l'alerte et lancera les actions définies dans cet onglet.

> Lors de l'annulation, toutes les actions d'alertes "futures" sont annulées.

![](https://raw.githubusercontent.com/AgP42/seniorcareinactivity/master/docs/assets/images/OngletAnnulation.png)

* Définir les actions qui seront réalisées pour les annulations d'alerte. Le fonctionnement des labels est identique aux actions de l'onglet **Accusé de réception**.

Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**

Onglet **Avancé - Commandes Jeedom**
---

Vous pouvez configurer ici les commandes utilisées par ce plugin. Vous pouvez notamment définir la visibilité du bouton d'accusé de réception sur le dashboard Jeedom (pour tests notamment) ainsi que les boutons de déclaration d'absence de la personne ou de passage jour/nuit.

Les autres commandes correspondent aux différents capteurs.
Les capteurs sont historisés par défaut, vous pouvez choisir de ne pas les historiser sans impact sur le comportement du plugin (permet de limiter l'usage de la base de donnée).

Panneau desktop
================

To Do - Il permettra de suivre les différents capteurs et visualiser les alertes.

=> Il a finalement été décidé de ne pas développer de panel, les fonctions Jeedom de visualisation d'historique, timeline et vues étant *a priori* suffisantes.

Remarques sur le comportement du plugin
======

Lors de la création et initialisation du mécanisme
---

* Pour initialiser le plugin, vous devez au moins 1 fois :
   * Activer chaque capteur (et vérifier sa bonne prise en compte dans les logs "info", ce qui est uniquement visible dans une des conditions où il a un timer valide défini)
   * Déclencher une alerte, laisser toutes les actions s'exécuter puis annuler l'alerte (= déclencher un capteur)

*Pourquoi ? Pour les curieux, voilà le détail : les informations suivantes sont stockées dans le cache de jeedom, il est recommandé de les initialiser lors de la 1ère installation (le cache de Jeedom n'est perdu ni lors d'une sauvegarde, ni lors d'un redémarrage, ni lors d'une mise à jour du core ou du plugin. Il n'est perdu que lorsque vous décidez de le vider manuellement ou lors de la réinstallation totale de votre jeedom) :
   * jour => état jour par défaut si cette valeur n'est pas initialisée.
   * presence/absence => le déclenchement de n'importe quel capteur (avec une temporisation >0) initialisera la présence
   * l'état courant de chaque capteur d'activité (pour ne déclencher que sur un changement d'état et non une répétition) => il est donc recommandé d'activer au moins 1 fois chaque capteur dans le logement. Si ça n'est pas fait, le 1er état sera nouveau et donc ça devrait fonctionner quand même.
   * le timestamp auquel il faudra déclencher la prochaine alerte => le déclenchement de n'importe quel capteur (avec une temporisation >0) initialisera ce timestamp. S'il n'est pas initialisé dans la 1ere minute après la création de l'équipement, les actions d'alerte vont se lancer (il suffit alors de les annuler avec le déclenchement d'un capteur).
   * l'état actuel de l'alerte (déjà déclenchée ou non) => le déclenchement de n'importe quel capteur (avec une temporisation >0) initialisera cette valeur à "pas d'alerte en cours"
   * l'état d'exécution de chacune des actions d'alertes ayant un label (celles sans label sont mémorisées aussi mais s'écrasent entre elles et elles ne sont jamais lues, donc sans impact fonctionnel), pour conditionner l'exécution des actions d'AR et d'annulation. => laisser toutes les actions d'alerte déclencher au moins 1 fois puis les annuler*


Comportement après redémarrage Jeedom
---
* Après un redémarrage de Jeedom, aucune information ne sera (ne devrait être...) perdue, mais il peut y avoir un délai supplémentaire d'1 min avant le déclenchement des alertes

Infos capteurs
---
* L'ensemble des capteurs définis dans le plugin doivent posséder un nom unique. Le changement de nom d'un capteur revient à le supprimer et à en créer un nouveau, l'historique associé à ce capteur sera donc perdu
* Si vous associez un capteur dont les valeurs ne sont pas binaires (0 ou 1), il ne sera jamais pris en compte. Vous devez alors passer par un virtuel pour générer une information binaire selon vos besoins.

Exemples d'usage et configuration associée
========================

Bonus : configuration pour recevoir les alertes par notification sur un smartphone android
========================

Bonus 2 : configuration pour mettre un jour un widget sur un smartphone android selon les infos de ce plugin
========================
