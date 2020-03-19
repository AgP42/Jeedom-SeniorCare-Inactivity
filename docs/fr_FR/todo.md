* Décrire dans la doc :
   - Comportement au demarrage pour la fonction "Détection d'inactivité": après création et la 1ere sauvegarde, il faut initialiser le mecanisme en declenchant l'un des capteur d'activité. Les actions de "warning" vont se declencher dans la 1ere minute apres la 1ere sauvegarde si aucun capteur n'a été activé.
   - Aprés un reboot de jeedom, le systeme aura perdu l'information de la date du dernier capteur d'activité et si les alarmes ont déjà été envoyées, les actions warnings vont donc se déclancher

* Dans la détection d'inactivité, comment l'aidant signale qu'il a vu l'alerte et arrive ?
* Idem pour les boutons d'alerte
* Comment desactiver les actions des boutons d'alertes
* Comment desactiver les alertes conforts ?
* Comment desactiver les alertes sécurité ?
