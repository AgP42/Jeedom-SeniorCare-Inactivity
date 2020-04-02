# Beta

0.0.1 - 24 mars 2020
---

* Gestion de détection d'inactivité
* Création documentation

0.0.2 - 29 mars
---

* Ajout de la possibilité d'un AR de l'alerte par les aidants
* Ajout séquencement des actions d'alertes et lien entre elles pour ne déclencher les annulations que si l'action liée a été réalisée
* Ajout des tags de configuration de la personne
* Ajout gestion absence via plugin agenda ou capteurs+timer ou via appel externe (API ou autre plugin jeedom)
* Ajout 1 timer de détection d'activité par capteur
* Tests et debugs
* Mise à jour documentation

0.0.3 - 31 mars
---

* Debug : prise en compte des capteurs uniquement si la valeur du capteur change (pour filtrer les capteurs qui renvoies periodiquement leur état hors changement)

0.0.4 - 1er avril
---

* Prise en compte des reformulations de mich0111 pour la configuration et la doc
* Mise à jour des logs infos pour ajouter le declenchement des actions
* La réception de l'état "absence" appelle maintenant les actions d'annulation d'alerte, au cas où une alerte était en cours
* Tests et debugs
* Mise à jour documentation

0.0.5 - 2 avril
---

* Ajout timer selon état du capteur (0->1 ou 1->0) et période jour/nuit
* Ajout des commandes pour déclarer état "jour" ou "nuit" et liaison avec le plugin agenda
* (debug) La réception de l'état "absence" appelle les actions d'annulation d'alerte => uniquement si l'alerte était active
* Tests, relecture et debugs
* Mise à jour documentation
