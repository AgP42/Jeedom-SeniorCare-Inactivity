Beta
####

0.0.1 - 18 mars 2020
---

* Gestion des boutons d'alerte
* Gestion des capteurs de confort
* Détection d'inactivitée

0.0.2 - 21 mars 2020
---

* refactorisation du code, relecture et debug
* Ajout Gestion des capteurs Sécurité
* session de test n°1 et debug associé - voir docs/fr_FR/tests.md
* Ajout des actions de désactivation des alertes "bouton d'alerte", "capteur sécurité" et "capteur confort"
* Ajout de la gestion de la non-répétition des alertes sur les capteurs confort
* Capteurs conforts évalués par cron15 et non plus par listener
* Mise à jour de la doc

0.0.3 & 0.0.4 - 22 mars 2020
---

* debug erreur js sur la liste des actions lorsque tous les capteurs sont dans les seuils
* debug 2 autres erreurs js de ce type
* creation puis correction de l'erreur de suppression du "bouton d'alerte"
* changement du tag #value# en #sensor_value# pour éviter les interferences avec le tag #value# du core
* ajout des tag #sensor_name# et #sensor_type# pour les actions d'alerte de securité
* debug sur les capteurs conforts !
* Mise à jour de la doc
