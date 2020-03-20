* Décrire dans la doc :
   - Comportement au demarrage pour la fonction "Détection d'inactivité": après création et la 1ere sauvegarde, il faut initialiser le mecanisme en declenchant l'un des capteur d'activité. Les actions de "warning" vont se declencher dans la 1ere minute apres la 1ere sauvegarde si aucun capteur n'a été activé.
   - Aprés un reboot de jeedom, le systeme aura perdu l'information de la date du dernier capteur d'activité et si les alarmes ont déjà été envoyées, les actions warnings vont donc se déclancher
   - Pour les capteurs "detections d'activité", "bouton d'alerte", "bouton d'annulation d'alerte", "capteur de sécurité" et "bouton d'annulation d'alerte de sécurité", c'est le changement de valeur du capteur qui est detecté et déclenche les actions, la valeur (0 ou 1) n'est pas prise en compte !

* Dans la détection d'inactivité, comment l'aidant signale qu'il a vu l'alerte et arrive ?
* Idem pour les boutons d'alerte
* Comment desactiver les alertes conforts ?

* Pour les alertes conforts qui sont actuellement gérées par trigger par capteur, comment gerer si un sort des seuils et un autre y rerentre si l'action est une lampe qui change de couleur ? Gérer les actions par types de capteur confort ? Ca devient un bordel...
