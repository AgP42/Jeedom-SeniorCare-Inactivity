Infos/constatations :
* les valeurs en cache ne sont pas effacées lors de la sauvegarde, donc on garde les timestamps du dernier trigger d'activité et l'état des warning et alertes
* si on ne rempli pas la conf pour les champs de durée, le code lit 0. Dans ce cas le warning est appelé au prochain CRON et le alerte au CRON suivant (ils ne passent pas tous les 2 dans le meme CRON)

Tests sur 0.0.2 du 19 mars 2020
###

Tests sur détection inactivité
---

1. Creation personne et conf puis save et attendre CRON sans action
=> au 1er CRON il détecte une inactivité depuis 1970, sans warning actif, donc il va lancer le warning. => Documenter le comportement au demarrage

2. sans action, le timer warning arrive a échéance et les actions alertes sont réalisées. => OK

3. On active un capteur => les actions de desactivation warning et alertes sont bien lancées => OK

4. REBOOT JEEDOM => le cache est perdu, au 1er CRON après redemarrage, les actions warnings sont appliquées (last event en 1970 et aucune alerte en cours enregistrée) => A documenter, au moins ca va dans le sens de la sécurité...

5. Tests de desactivation warning puis alerte => OK

