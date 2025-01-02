# Stables

1.0.0 - juin 2020 => voir beta 0.0.9
---

1.0.1 - 26 juin 2020 => idem beta 1.0.1
---

1.0.2 - 02 janvier 2024
---

* Correction erreurs cron
* compatibilité debian 12

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
* Mise à jour des logs infos pour ajouter le déclenchement des actions
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

0.0.6 - 4 avril
---

* Modif mineur pour cohérance avec les autres plugin SeniorCare
* Update doc

0.0.7 - 16 avril
---

* Filtrage de la repetition des valeurs pour les capteurs d'absences

0.0.8 - 5 mai
---

* Ajout choix actions d'alertes restantes en cas de reception d'un AR : les annuler, les décaler, les laisser en l'état
* Ajout menu déroulant pour choisir les actions de références pour les actions d'AR et les actions d'annulation
* Robustification du code (ajout d'exceptions) et amélioration des logs
* Mise à jour de la documentation

0.0.9 - 14 juin 2020
---

* Update doc pour passage stable

1.0.1 - 26 juin 2020
---

* debug cas où plusieurs actions d'annulation sont liées à un même label d'action de reference
