Infos/constatations :
* les valeurs en cache ne sont pas effacées lors de la sauvegarde (mais effacés au reboot), donc on garde les timestamps du dernier trigger d'activité et l'état des warning et alertes
* si on ne rempli pas la conf pour les champs de durée, le code lit 0. Dans ce cas le warning est appelé au prochain CRON et le alerte au CRON suivant (ils ne passent pas tous les 2 dans le meme CRON)

Tests sur v0.0.2 du 19 au 21 mars 2020
###

Tests sur détection inactivité
---

1. Creation personne et conf puis save et attendre CRON sans action
=> au 1er CRON il détecte une inactivité depuis 1970, sans warning actif, donc il va lancer le warning. => Documenter le comportement au demarrage

2. sans action, le timer warning arrive a échéance et les actions alertes sont réalisées. => OK

3. On active un capteur => les actions de desactivation warning et alertes sont bien lancées => OK

4. REBOOT JEEDOM => le cache est perdu, au 1er CRON après redemarrage, les actions warnings sont appliquées (last event en 1970 et aucune alerte en cours enregistrée) => A documenter, au moins ca va dans le sens de la sécurité...

5. Tests de desactivation warning puis alerte => OK

Tests sur bouton d'alerte (apres avoir ajouté les capteurs et actions de desactivation)
---

0. Creation de la conf et vérification en DB que les capteurs et les listeners sont bien présents => OK
1. Trigger bouton alerte => action alerte ok
2. Trigger bouton desactivation alerte => action desactivation alerte ok
3. Trigger bouton alerte plusieurs fois => action alerte relancée a chaque fois
4. idem bouton desactivation => idem
=> Test OK

Tests sur capteurs sécurité (apres avoir ajouté les capteurs et actions de désactivation)
---

0. Creation de la conf et vérification en DB que les capteurs et les listeners sont bien présents et tests d'erreurs si nom identiques ou absents => OK
1. Trigger capteur securité => action ok
2. Trigger bouton desactivation alerte => action desactivation alerte ok
3. Trigger bouton alerte plusieurs fois => action alerte relancée a chaque fois
4. idem bouton desactivation => idem
=> Test OK

Tests sur capteurs conforts (apres ajout actions desactivation et desactivation si all ok et menu deroulant de repetition de l'alerte)
---

Pourquoi a l'enregistrement la fct execute s'applique a tout le monde si commande nouvelle ou à toutes hors confort pour les commandes existantes ??? Alors que c'est traité par une boucle, donc forcement identique !!! (mais semble marcher quand meme...)

0. Creation de la conf, vérif en DB et tests d'erreurs de saisie de conf => OK (après correction bug...)
1. Tests lancement des actions sorties de seuils => ok
2. Tests lancement des actions de retour dans les seuils => ok
3. Tests lancement des actions TOUS les capteurs dans les seuils => OK
4. Tests de repetition 1 fois, toutes les 15 min => OK
5. Tests repetition toutes les 1h => en cours
6. Tests repetition toutes les 6h => non realisé...
