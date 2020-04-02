<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


/*
Logique de fonctionnement du plugin :

Capteur activité => appel fct "sensorLifeSign". Si on était en phase d'alerte, lancera les actions d'annulation via la fct "execCancelActions". Sinon gere les timers et laisse le cron jeedom faire.
Capteur absence => appel fct "sensorAbsence". Si délai configuré => set cron qui appellera la fct "lifeSignAbsenceDelayed".

Reception d'un AR => appel fct "lifeSignAR" qui va appeler ses actions d'AR et couper la chaine d'alerte (supprimer les cron)
Réception appel cmd "déclarer absence" => set le cache à "absence" et lance les actions d'annulation via la fct "execCancelActions"
Réception appel cmd "déclarer jour" ou "déclarer nuit" => set le cache à "jour" à 0 ou 1 et c'est tout (on ne change pas le timer courant, on attend le prochain trigger)


Toutes les minutes (cron jeedom) => on évalue si on est present et si les timers d'alerte sont depassés, si oui on lance les actions immédiates et on set les cron pour les actions différees
*/

/*
Les infos en cache (effacées ni lors de la sauvegarde, ni au reboot, ni update jeedom, mais mal lues au 1er cron après reboot...) :

* $eqLogic->getCache('presence')); => savoir si la personne est présente ou absente. Sera a nouveau déclarée présente au 1ere capteur d'activité déclenché
* $eqLogic->getCache('sensor_' . $_option['event_id']) => la valeur précédente de chaque capteur d'activité, pour ne déclencher que sur un changement d'état et non une répétition.
* $eqLogic->setCache('nextLifeSignAlertTimestamp', time() + $lifeSignDetectionDelay); => le timestamp auquel il faut déclencher l'alerte
* $eqLogic->getCache('alertLifeSignState'); => l'état actuel de l'alerte (déjà déclenchée ou non)
* $eqLogic->setCache('execAction_'.$action['action_label'], 1); => l'état d'execution de chacune des actions d'alertes ayant un label (celles sans label sont mémorisées aussi mais s'écrasent entre elles et elles ne sont jamais lues, donc on s'en fout), pour conditionner l'exécution des actions d'AR et d'annulation
* $eqLogic->setCache('jour', 0); => savoir si on est en jour ou en nuit
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class seniorcareinactivity extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    public static function lifeSignAbsenceDelayed($_options) { // fonction appelée par les cron qui servent a reporter l'execution de l'absence.

      $seniorcareinactivity = seniorcareinactivity::byId($_options['eqLogic_id']); // on prend l'eqLogic du cron qui nous a appelé

      $seniorcareinactivity->setCache('presence', 0); //on declare l'absence dans le cache. C'est le cron1 de jeedom qui gere le reste
      log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - ABSENCE (par activation d\' un capteur AVEC timer)');
      log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache *presence* : ' . $seniorcareinactivity->getCache('presence'));

      if ($seniorcareinactivity->getCache('alertLifeSignState')){ // si on était en phase d'alerte, on lance les actions d'annulation
        $seniorcareinactivity->execCancelActions();
      }
    }

    public static function lifeSignActionDelayed($_options) { // fonction appelée par les cron qui servent a reporter l'execution des actions d'alerte. Dans les options on trouve le eqLogic_id et 'action' qui lui meme contient tout ce qu'il faut pour executer l'action reportée, incluant le titre et message pour les messages

      $seniorcareinactivity = seniorcareinactivity::byId($_options['eqLogic_id']); // on prend l'eqLogic du cron qui nous a appelé
      $seniorcareinactivity->execAction($_options['action']);

      log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - Exécution (différée) de l\'action d\'alerte : ' . $_options['action']['cmd'] . ' - Label : ' . $_options['action']['action_label']);
    }

    public static function sensorAbsence($_option) { // fct appelée par le listener des boutons d'absence, n'importe quel bouton arrive ici

      $seniorcareinactivity = seniorcareinactivity::byId($_option['seniorcareinactivity_id']); // on prend l'eqLogic du trigger qui nous a appelé

      $lifeSignAbsenceDelay = $seniorcareinactivity->getConfiguration('absence_timer'); // on récupere le timer d'absence configuré, direct en min

      log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - Détection d\'un capteur d\'ABSENCE, l\'absence sera effective d\'ici ' . $lifeSignAbsenceDelay . ' minutes');

      if(is_numeric($lifeSignAbsenceDelay) && $lifeSignAbsenceDelay > 0){

        $seniorcareinactivity->setCache('presence', 0); //on declare dés maintenant l'absence dans le cache. Elle sera repetée par le cron qu'on met en place ci-dessous. Permet d'éviter de lancer une alerte entre le moment où on a lancé le bouton d'absence et l'absence effective (ne devrait pas arriver vu qu'on bouge de toute facon, mais bon...)

        $cron = cron::byClassAndFunction('seniorcareinactivity', 'lifeSignAbsenceDelayed', array('eqLogic_id' => intval($seniorcareinactivity->getId()))); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et cette personne (on ne veut)
        // lors de la suppression de l'eqLogic, si un cron est existant, il sera supprimé

        if (is_object($cron)) { // s'il existe, on le supprime et on le reset à la nouvelle heure. C'est pour gerer les appui multiples ou si on a plusieurs capteurs : on décale le CRON à chaque fois pour que seul le dernier soit pris en compte
          $cron->remove();
        }

        $cron = new cron();
        $cron->setClass('seniorcareinactivity');
        $cron->setFunction('lifeSignAbsenceDelayed');

        $options['eqLogic_id'] = intval($seniorcareinactivity->getId());
        $cron->setOption($options);

        $cron->setEnable(1);
        $cron->setTimeout(5); //minutes

        $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$lifeSignAbsenceDelay.' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'lifeSignAbsenceDelay' min
        $cron->setSchedule(cron::convertDateToCron($delai));

        $cron->setOnce(1); //permet qu'il s'auto supprime une fois executé
        $cron->save();

        log::add('seniorcareinactivity', 'debug', 'Set CRON absence différée pour eqLogic: ' . $options['eqLogic_id']);

      } else { // immédiat

        log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - ABSENCE (par activation d\' un capteur SANS timer)');
        $seniorcareinactivity->setCache('presence', 0); //on declare l'absence dans le cache. C'est le cron1 de jeedom qui gere le reste
        log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache *presence* : ' . $seniorcareinactivity->getCache('presence'));

        if ($seniorcareinactivity->getCache('alertLifeSignState')){ // si on était en phase d'alerte, on lance les actions d'annulation
          $seniorcareinactivity->execCancelActions();
        }

      }

    }


    public static function sensorLifeSign($_option) { // fct appelée par le listener des capteurs d'activité, n'importe quel capteur arrive ici

      $seniorcareinactivity = seniorcareinactivity::byId($_option['seniorcareinactivity_id']); // on prend l'eqLogic du trigger qui nous a appelé

      log::add('seniorcareinactivity', 'debug', '################ Detection d\'un capteur d\'activité ############ pour : ' . $seniorcareinactivity->getHumanName() . ' - Presence : ' . $seniorcareinactivity->getCache('presence') . ' - Jour : ' . $seniorcareinactivity->getCache('jour'));

      // on va chercher quel capteur nous a declenché pour aller chercher son timer et sa valeur
      foreach ($seniorcareinactivity->getConfiguration('life_sign') as $sensor) { // on boucle direct dans la conf
        if ('#' . $_option['event_id'] . '#' == $sensor['cmd']) { // si on est sur le capteur qui vient de nous declencher

          if ($seniorcareinactivity->getCache('sensor_' . $_option['event_id']) != $_option['value']){ // si notre valeur a changé, donc a prendre en compte

            log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - Détection d\'un capteur d\'ACTIVITÉ, commande : ' . $sensor['cmd'] . ', nom : ' . $sensor['name'] . ', timer état haut jour : ' . $sensor['life_sign_timer_high_day'] . 'min, timer état bas jour : ' . $sensor['life_sign_timer_low_day'] . 'min, timer état haut nuit : ' . $sensor['life_sign_timer_high_night'] . 'min, timer état bas nuit : ' . $sensor['life_sign_timer_low_night'] . 'min, nouvel état capteur : ' . $_option['value']);

            if(!$seniorcareinactivity->getCache('presence')) { // on log uniquement si la presence est nouvelle
              log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - PRESENCE (par activation d\' un capteur d\'activité)');
            }

            if(!$seniorcareinactivity->getCache('jour')){ // si on est pas sur d'etre en nuit, par defaut on prendra jour
              $lifeSignDetectionDelay = $_option['value'] ? $sensor['life_sign_timer_high_night'] * 60 : $sensor['life_sign_timer_low_night'] * 60; //choppe le timer selon état haut ou bas
            } else {
              $lifeSignDetectionDelay = $_option['value'] ? $sensor['life_sign_timer_high_day'] * 60 : $sensor['life_sign_timer_low_day'] * 60; //choppe le timer selon état haut ou bas
            }

            if(is_numeric($lifeSignDetectionDelay) && $lifeSignDetectionDelay > 0){ // si on a un timer bien defini et > 0 min, le trigger est donc valable, on le prend en compte (sinon : on fait rien)

              log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - Détection d\'un capteur d\'ACTIVITÉ, commande : ' . $sensor['cmd'] . ', nom : ' . $sensor['name'] . ', valeur timer à utiliser : ' . $lifeSignDetectionDelay/60 . 'min, nouvel état capteur : ' . $_option['value']);

              $seniorcareinactivity->setCache('presence', 1); //on declare la personne présente
              $seniorcareinactivity->setCache('nextLifeSignAlertTimestamp', time() + $lifeSignDetectionDelay); // on met en cache le timestamp auquel il faudra déclencher l'alerte. C'est le cron qui regardera toutes les min si on est hors delais

              log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache *nextLifeSignAlertTimestamp* : ' . $seniorcareinactivity->getCache('nextLifeSignAlertTimestamp') . ' - cache *presence* : ' . $seniorcareinactivity->getCache('presence'));

              if ($seniorcareinactivity->getCache('alertLifeSignState')){ // si on était en phase d'alerte, on lance les actions d'annulation
                $seniorcareinactivity->execCancelActions();
              }

              $seniorcareinactivity->setCache('alertLifeSignState', 0); // on declare qu'on est pas ou plus en phase d'alerte, puisqu'on vient de recevoir un signe de vie valide
              log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache *alertLifeSignState* : ' . $seniorcareinactivity->getCache('alertLifeSignState'));

            } // fin condition on a un timer >0 pour le cas actuel

          } // fin notre valeur est nouvelle (pas une repetition de 0 ou de 1)

          $seniorcareinactivity->setCache('sensor_' . $_option['event_id'], $_option['value']); //on garde en cache la valeur actuelle du capteur
          log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache *sensor_' . $_option['event_id'] . '* : ' . $seniorcareinactivity->getCache('sensor_' . $_option['event_id']));

        } // fin if on est sur le capteur qui nous a déclenché
      } // fin foreach tous les capteurs de la conf

    } // fin de la fonction appellée par le listener


    public static function cron() { //executée toutes les min par Jeedom

      log::add('seniorcareinactivity', 'debug', '#################### CRON ###################');


      //pour chaque équipement (personne) déclaré par l'utilisateur
      foreach (self::byType('seniorcareinactivity',true) as $seniorcareinactivity) {

    //    log::add('seniorcareinactivity', 'debug', 'Lecture des caches - presence : ' . $seniorcareinactivity->getCache('presence') . ' - sensor 665 : ' . $seniorcareinactivity->getCache('sensor_665') . ' - nextLifeSignAlertTimestamp : ' . $seniorcareinactivity->getCache('nextLifeSignAlertTimestamp') . ' - alertLifeSignState : ' . $seniorcareinactivity->getCache('alertLifeSignState') . ' - execAction_lampe : ' . $seniorcareinactivity->getCache('execAction_lampe') );


        log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache presence lu : ' . $seniorcareinactivity->getCache('presence'));
        // on constate ici qu'après un reboot de jeedom, le cache n'est pas lu, donc le plugin se comporte comme s'il y avait absence, c'est très bien comme ca. Au pire on loupe 1 min avant de lancer une alerte mais on a pas de comportement bizarre après un reboot

        if (is_object($seniorcareinactivity) && $seniorcareinactivity->getIsEnable() == 1 && $seniorcareinactivity->getCache('presence')) { // si notre eq existe et est actif et que la personne est présente !

          $now = time(); // timestamp courant, en s
          $nextLifeSignAlertTimestamp = $seniorcareinactivity->getCache('nextLifeSignAlertTimestamp'); // le timestamp enregistré auquel il faut déclencher l'alerte

          // on recupere l'état de l'alerte
          $alertLifeSignState = $seniorcareinactivity->getCache('alertLifeSignState');

          log::add('seniorcareinactivity', 'debug', 'Etat alerte : ' . $alertLifeSignState . ' - Alerte à lancer dans : ' . intval($nextLifeSignAlertTimestamp-$now) . 's');

          if ($now >= $nextLifeSignAlertTimestamp && !$alertLifeSignState){
          //= on est au dela du delai et alerte pas encore en cours --> on va lancer les actions alerte
            log::add('seniorcareinactivity', 'info', $seniorcareinactivity->getHumanName() . ' - Actions Alerte Inactivité à lancer.');

            // boucler dans les actions, les lancer ou set cron si timer défini
            $seniorcareinactivity->execAlerteActions();

            $seniorcareinactivity->setCache('alertLifeSignState', 1); // on memorise qu'on a lancé les actions pour ne pas repeter toutes les min
            log::add('seniorcareinactivity', 'debug', $seniorcareinactivity->getHumanName() . ' - cache *alertLifeSignState* : ' . $seniorcareinactivity->getCache('alertLifeSignState'));


          } // sinon : on fait juste rien...

        } // fin if eq actif

      } // fin foreach equipement

    } //fin cron

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function execAlerteActions($action) { // appelée par le cron (celui de jeedom toutes les min), si besoin

      foreach ($this->getConfiguration('action_alert_life_sign') as $action) { // pour toutes les actions définies

        log::add('seniorcareinactivity', 'debug', 'Action LifeSign Alert - action_label : ' . $action['action_label'] . ' - action_timer : ' . $action['action_timer']);

        if(is_numeric($action['action_timer']) && $action['action_timer'] > 0){ // si on a un timer bien defini et > 0 min, on va lancer un cron pour l'execution retardée de l'action
          // si le CRON existe deja, on ne l'update pas

          $cron = cron::byClassAndFunction('seniorcareinactivity', 'lifeSignActionDelayed', array('eqLogic_id' => intval($this->getId()), 'action' => $action)); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (personne, action (qui contient cmd, option (les titres et messages notamment) et label))" Si on change le label ou le message, c'est plus le meme "action" et donc cette fonction ne le trouve pas et un nouveau cron sera crée !
          // lors d'une sauvegarde ou suppression de l'eqLogic, si des crons sont existants, ils seront supprimés avec un message d'alerte

          if (!is_object($cron)) { // pas de cron trouvé, on le cree

              $cron = new cron();
              $cron->setClass('seniorcareinactivity');
              $cron->setFunction('lifeSignActionDelayed');

              $options['eqLogic_id'] = intval($this->getId());
              $options['action'] = $action; //inclu tout le detail de l'action : sa cmd, ses options pour les messages, son label, ...
              $cron->setOption($options);

              log::add('seniorcareinactivity', 'debug', 'Set CRON : ' . $options['eqLogic_id'] . ' - ' . $options['action']['cmd'] . ' - ' . $options['action']['action_label']);

              $cron->setEnable(1);
              $cron->setTimeout(5); //minutes

              $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_timer'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
              $cron->setSchedule(cron::convertDateToCron($delai));

              $cron->setOnce(1); //permet qu'il s'auto supprime une fois executé
              $cron->save();

          } else {

            log::add('seniorcareinactivity', 'debug', 'CRON existe deja pour : ' . $cron->getOption()['eqLogic_id'] . ' - ' . $cron->getOption()['action']['cmd'] . ' - ' . $cron->getOption()['action']['action_label'] . ' => on ne fait rien !');
          }

        }else{ // pas de timer valide defini, on execute l'action immédiatement

          log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - Exécution (immédiate) de l\'action d\'alerte : ' . $action['cmd'] . ' - Label : ' . $action['action_label']);

          $this->execAction($action);
        }

      } // fin foreach toutes les actions

    }

    public function execCancelActions() { // appelée par un trigger de signe de vie ou passage en mode "absence", si l'alerte était active

      log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - Actions Annulation Alerte Inactivité à lancer. ');

      foreach ($this->getConfiguration('action_cancel_life_sign') as $action) { // pour toutes les actions définies
        $execActionLiee = $this->getCache('execAction_'.$action['action_label_liee']); // on va lire le cache d'execution de l'action liée, savoir si deja lancé ou non...
        log::add('seniorcareinactivity', 'debug', 'Config Action Annulation Alerte inactivité, action : '. $action['cmd'] .', label action liée : ' . $action['action_label_liee'] . ' - action liée deja executée : ' . $execActionLiee);

        if($action['action_label_liee'] == ''){ // si pas d'action liée, on execute direct

          log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - Exécution de l\'action d\'annulation : ' . $action['cmd'] . ' (pas d\'action de référence)');

          $this->execAction($action);

        }else if(isset($action['action_label_liee']) && $action['action_label_liee'] != '' && $execActionLiee == 1){ // si on a une action liée définie et qu'elle a été executée => on execute notre action et on remet le cache de l'action liée à 0 (fait uniquement pour annulation et non à la réception de l'AR, donc l'aidant ayant recu une alerte pourra recevoir l'info qu'il y a eu une AR (mais on sait pas par qui... TODO...) puis que l'alerte est résolue)

        //  log::add('seniorcareinactivity', 'debug', 'Action liée ('.$action['action_label_liee'].') executée précédemment, donc on execute ' . $action['cmd'] . ' et remise à 0 du cache d\'exec de l\'action origine');
          log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - Exécution de l\'action d\'annulation : ' . $action['cmd'] . ' - Label de référence (précédemment exécuté) : ' . $action['action_label_liee']);

          $this->execAction($action);
          $this->setCache('execAction_'.$action['action_label_liee'], 0);
          log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *execAction_' . $action['action_label_liee'] . '* : ' . $this->getCache('execAction_'.$action['action_label_liee']));

        }else{ // sinon, on log qu'on n'execute pas l'action et la raison
          log::add('seniorcareinactivity', 'debug', 'Action liée ('.$action['action_label_liee'].') non executée précédemment, donc on execute pas ' . $action['cmd']);
        }

      } // fin foreach toutes les actions

      //coupe les CRON des actions d'alertes non encore appelés
      $this->cleanAllCron();

    }

    public function lifeSignAR() { // fct appelée par la cmd action appelée par l'extérieur pour AR de l'alerte en cours

      log::add('seniorcareinactivity', 'debug', '################ Detection d\'un appel d\'Accusé de Réception ############');

      foreach ($this->getConfiguration('action_ar_life_sign') as $action) { // pour toutes les actions définies pour les AR
        $execActionLiee = $this->getCache('execAction_'.$action['action_label_liee']);
        log::add('seniorcareinactivity', 'debug', 'Config Action Accusé Réception bouton d\'alerte, action : '. $action['cmd'] .', label action liée : ' . $action['action_label_liee'] . ' - action liée deja executée : ' . $execActionLiee);

        if($action['action_label_liee'] == ''){ // si pas d'action liée, on execute direct

          log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - Exécution de l\'action d\'AR: ' . $action['cmd'] . ' (pas d\'action de référence)');
          $this->execAction($action);

        }else if(isset($action['action_label_liee']) && $action['action_label_liee'] != '' && $execActionLiee == 1){ // si on a une action liée définie et qu'elle a été executée => on execute notre action

          log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - Exécution de l\'action d\'AR: ' . $action['cmd'] . ' - Label de référence (précédemment exécuté) : ' . $action['action_label_liee']);

          $this->execAction($action);

        }else{ // sinon, on log qu'on ne l'execute pas
          log::add('seniorcareinactivity', 'debug', 'Action liée ('.$action['action_label_liee'].') non executée précédemment, donc on execute pas ' . $action['cmd']);
        }

      } // fin foreach toutes les actions

      //coupe les CRON des actions d'alertes non encore appelés
      $this->cleanAllCron();

    }

    public function execAction($action) { // execution d'une seule action, avec son label si c'est une alerte
    // $this doit rester l'eqLogic et non la commande elle meme, pour chopper les tags

      log::add('seniorcareinactivity', 'debug', '################ Execution de l\' actions ' . $_config . ' pour ' . $this->getName() .  ' ############');

      try {
        $options = array(); // va permettre d'appeler les options de configuration des actions, par exemple un scenario ou les textes pour un message
        if (isset($action['options'])) {
          $options = $action['options'];
          foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
            // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
            $value = str_replace('#senior_name#', $this->getConfiguration('senior_name'), $value);
            $value = str_replace('#senior_phone#', $this->getConfiguration('senior_phone'), $value);
            $value = str_replace('#senior_address#', $this->getConfiguration('senior_address'), $value);

            $value = str_replace('#trusted_person_name#', $this->getConfiguration('trusted_person_name'), $value);
            $value = str_replace('#trusted_person_phone#', $this->getConfiguration('trusted_person_phone'), $value);

      //      $value = str_replace('#url_ar#', $this->getConfiguration('url_ar'), $value); // marche pas malheureusement, probablement une histoire de formatage... TODO...

      //      $value = str_replace('#sensor_name#', $_sensor_name, $value);
      //      $value = str_replace('#sensor_type#', $_sensor_type, $value);
            $options[$key] = str_replace('#sensor_value#', $_sensor_value, $value);
          }
        }
        scenarioExpression::createAndExec('action', $action['cmd'], $options);

        if(isset($action['action_label'])){ // si on avait un label (donc c'est une action d'alerte), on memorise qu'on a lancé l'action
          $this->setCache('execAction_'.$action['action_label'], 1);
          log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *execAction_' . $action['action_label'] . '* : ' . $this->getCache('execAction_'.$action['action_label']));
        }

      } catch (Exception $e) {
        log::add('seniorcareinactivity', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());

      }

    }

    public function cleanAllCron($displayWarningMessage = false) { // en fait c'est pas tous, mais tous les cron des ACTIONS différées. Le cron pour l'absence différé n'est pas annulé ici, Il n'est annulé que dans la fonction preRemove (suppression de l'eqLogic)

      log::add('seniorcareinactivity', 'debug', 'Fct cleanAllCron pour : ' . $this->getName());

      $cron = cron::byClassAndFunction('seniorcareinactivity', 'lifeSignActionDelayed'); //on cherche le 1er cron pour ce plugin et cette action (il n'existe pas de fonction core renvoyant un array avec tous les cron de la class, comme pour les listeners... dommage...)

      while (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId()) { // s'il existe et que l'id correspond, on le vire puis on cherche le suivant et tant qu'il y a un suivant on boucle

        log::add('seniorcareinactivity', 'debug', 'Cron trouvé à supprimer pour eqLogic_id : ' . $cron->getOption()['eqLogic_id'] . ' - cmd : ' . ' - ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);

        if($displayWarningMessage){

          log::add('seniorcareinactivity', 'error', 'Attention, des actions d\'alerte avec un délai avant exécution sont en cours et vont être supprimées, merci de vous assurer que la personne associée n\'a pas besoin d\'assistance ! Il s\'agit de ' . $this->getConfiguration('senior_name') . ' - pour l\'eqLogic ' . $this->getName() . ', action supprimée : ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);
        }

        $cron->remove();
        $cron = cron::byClassAndFunction('seniorcareinactivity', 'lifeSignActionDelayed'); // on cherche le suivant et on recommence
      }

    }

    public function cleanAllListener() {

      log::add('seniorcareinactivity', 'debug', 'Fct cleanAllListener pour : ' . $this->getName());

      $listeners = listener::byClass('seniorcareinactivity'); // on prend tous nos listeners de ce plugin, pour toutes les personnes
      foreach ($listeners as $listener) {
        $seniorcareinactivity_id_listener = $listener->getOption()['seniorcareinactivity_id'];

        if($seniorcareinactivity_id_listener == $this->getId()){ // si on correspond a la bonne personne, on le vire
          $listener->remove();
        }

      }

    }

    public function preInsert() {

    }

    public function postInsert() {

      log::add('seniorcareinactivity', 'info', 'Création de ' . $this->getHumanName());

      // on va créer la commande pour l'AR
      $cmd = $this->getCmd(null, 'life_sign_ar');
      if (!is_object($cmd)) {
        $cmd = new seniorcareinactivityCmd();
        $cmd->setName(__('Accuser Réception Alerte', __FILE__));
      }
      $cmd->setLogicalId('life_sign_ar');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setIsVisible(0);
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'none'); //on garde en mémoire tous les AR recu.
      //TODO : voir comment on pourrait avoir l'info de "qui" a accusé réception...
      $cmd->save();

      // et la commande pour absence
      $cmd = $this->getCmd(null, 'life_sign_absence');
      if (!is_object($cmd)) {
        $cmd = new seniorcareinactivityCmd();
        $cmd->setName(__('Déclarer absence', __FILE__));
      }
      $cmd->setLogicalId('life_sign_absence');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();

      $this->setCache('presence', 1); //A la creation de l'équipement, on declare la personne présente. C'est juste histoire d'initialiser le truc
      log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *presence* : ' . $this->getCache('presence'));
      log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - PRESENCE (à la création de l\'eqLogic)');

      // et les commandes pour jour et nuit
      $cmd = $this->getCmd(null, 'life_sign_jour');
      if (!is_object($cmd)) {
        $cmd = new seniorcareinactivityCmd();
        $cmd->setName(__('Déclarer jour', __FILE__));
      }
      $cmd->setLogicalId('life_sign_jour');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();

      $cmd = $this->getCmd(null, 'life_sign_nuit');
      if (!is_object($cmd)) {
        $cmd = new seniorcareinactivityCmd();
        $cmd->setName(__('Déclarer nuit', __FILE__));
      }
      $cmd->setLogicalId('life_sign_nuit');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();

      $this->setCache('jour', 1); //A la creation de l'équipement, on declare qu'on est en jour. Histoire d'initialiser le truc
      log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *jour* : ' . $this->getCache('jour'));
      log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - JOUR (à la création de l\'eqLogic)');

    }

    public function preSave() {

    }

    // fct appellée par Jeedom aprés l'enregistrement de la configuration
    public function postSave() {

      log::add('seniorcareinactivity', 'debug', 'Début Sauvegarde de ' . $this->getHumanName());


      //########## 1 - On va lire la configuration des capteurs dans le JS et on la stocke dans un grand tableau #########//

      $jsSensors = array(
        'life_sign' => array(), // sous-tableau pour stocker toutes les infos des capteurs de détection d'activité
        'absence' => array(), // et celui pour les boutons d'absence
      );

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs pour recuperer les infos
        log::add('seniorcareinactivity', 'debug', 'Boucle de $jsSensors : key : ' . $key);

        if (is_array($this->getConfiguration($key))) {
          foreach ($this->getConfiguration($key) as $sensor) {
            if ($sensor['name'] != '' && $sensor['cmd'] != '') { // si le nom et la cmd sont remplis

              $jsSensors[$key][$sensor['name']] = $sensor; // on stocke toute la conf, c'est à dire tout ce qui dans notre js avait la class "expressionAttr". Pour retrouver notre champs exact : $jsSensors[$key][$sensor['name']][data-l1key]. // attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut écrire, pas dans la variable qui le represente dans cette boucle
              log::add('seniorcareinactivity', 'debug', 'Capteurs sensor config lue : ' . $sensor['name'] . ' - ' . $sensor['cmd']);

            }
          }
        }
      }

      //########## 2 - On boucle dans toutes les cmd existantes, pour les modifier si besoin #########//


      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos différents types de capteurs. $key va prendre les valeurs suivantes : life_sign puis absence

        foreach ($this->getCmd() as $cmd) {
          if ($cmd->getLogicalId() == 'sensor_' . $key) {
            if (isset($jsSensor[$cmd->getName()])) { // on regarde si le nom correspond à un nom dans le tableau qu'on vient de recuperer du JS, si oui, on actualise les infos qui pourraient avoir bougé

              $sensor = $jsSensor[$cmd->getName()];
              $cmd->setValue($sensor['cmd']);

/*              if(isset($sensor['sensor_'.$key.'_type'])){ // ce sera vrai pour les types life-sign
                $cmd->setGeneric_type($sensor['sensor_'.$key.'_type']);
              }*/

              $cmd->save();

              // va chopper la valeur de la commande puis la suivre a chaque changement
              if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
                $cmd->setCollectDate('');
                $cmd->event($cmd->execute());
              }

              unset($jsSensors[$key][$cmd->getName()]); // on a traité notre ligne, on la vire. Attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut virer notre ligne

            } else { // on a un sensor qui était dans la DB mais dont le nom n'est plus dans notre JS : on la supprime ! Attention, si on a juste changé le nom, on va le supprimer et le recreer, donc perdre l'historique éventuel.
              $cmd->remove();
            }
          }
        } // fin foreach toutes les cmd du plugin
      } // fin foreach nos differents types de capteurs//*/

      //########## 3 - Maintenant on va creer les cmd nouvelles de notre conf (= celles qui restent dans notre tableau) #########//

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs. $key va prendre les valeurs suivantes : life_sign, alert_bt, confort puis security

        foreach ($jsSensor as $sensor) { // pour chacun des capteurs de ce type

          // ce qui identifie d'un point de vu unique notre capteur c'est son type et sa value(cmd)

          log::add('seniorcareinactivity', 'debug', 'New Capteurs config : type : ' . $key . ', sensor name : ' . $sensor['name'] . ', sensor cmd : ' . $sensor['cmd']);

          $cmd = new seniorcareinactivityCmd();
          $cmd->setEqLogic_id($this->getId());
          $cmd->setLogicalId('sensor_' . $key);
          $cmd->setName($sensor['name']);
          $cmd->setValue($sensor['cmd']);
          $cmd->setType('info');
          $cmd->setSubType('numeric');
          $cmd->setIsVisible(0);
          $cmd->setIsHistorized(1);
          $cmd->setConfiguration('historizeMode', 'none');

         /* if(isset($sensor['sensor_'.$key.'_type'])){ // ce sera vrai pour les types life-sign
            $cmd->setGeneric_type($sensor['sensor_'.$key.'_type']);
          }*/

          $cmd->save();

          // va chopper la valeur de la commande puis la suivre a chaque changement
          if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
            $cmd->setCollectDate('');
            $cmd->event($cmd->execute());
          }

        } //*/ // fin foreach restant. A partir de maintenant on a des capteurs qui refletent notre config lue en JS
      }


      //########## 4 - Mise en place des listeners de capteurs pour réagir aux events #########//

      if ($this->getIsEnable() == 1) { // si notre eq est actif, on va lui definir nos listeners de capteurs

        // un peu de menage dans nos events avant de remettre tout ca en ligne avec la conf actuelle
        $this->cleanAllListener();

        // on boucle dans toutes les cmd existantes
        foreach ($this->getCmd() as $cmd) {

          // on assigne la fonction selon le type de capteur
          if ($cmd->getLogicalId() == 'sensor_life_sign') {
            $listenerFunction = 'sensorLifeSign';
          } else if ($cmd->getLogicalId() == 'sensor_absence') {
            $listenerFunction = 'sensorAbsence';
          } else {
            continue; // sinon c'est que c'est pas un truc auquel on veut assigner un listener, on passe notre tour
          }

          // on set le listener associée
          $listener = listener::byClassAndFunction('seniorcareinactivity', $listenerFunction, array('seniorcareinactivity_id' => intval($this->getId())));
          if (!is_object($listener)) { // s'il existe pas, on le cree, sinon on le reprend
            $listener = new listener();
            $listener->setClass('seniorcareinactivity');
            $listener->setFunction($listenerFunction); // la fct qui sera appellée a chaque evenement sur une des sources écoutée
            $listener->setOption(array('seniorcareinactivity_id' => intval($this->getId())));
          }
          $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs definis. On cherchera le trigger a l'appel de la fonction si besoin

          log::add('seniorcareinactivity', 'debug', 'sensor listener set - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

          $listener->save();

        } // fin foreach cmd du plugin
      } // fin if eq actif
      else { // notre eq n'est pas actif ou il a ete desactivé, on supprime les listeners s'ils existaient

        $this->cleanAllListener();

      }

      //########## 5 - initialisation du cache #########//

      // on declare qu'on est pas en phase d'alerte
  //    $this->setCache('alertLifeSignState', 0);

      log::add('seniorcareinactivity', 'debug', 'Fin Sauvegarde de ' . $this->getHumanName());
      log::add('seniorcareinactivity', 'info', 'Sauvegarde de ' . $this->getHumanName());


    } // fin fct postSave

    // preUpdate ⇒ Méthode appellée avant la mise à jour de votre objet
    // ici on vérifie la présence de nos champs de config obligatoire
    public function preUpdate() {

      $sensorsType = array( // liste des types avec des champs a vérifier
        'life_sign',
      );

      foreach ($sensorsType as $type) {
        if (is_array($this->getConfiguration($type))) {
          foreach ($this->getConfiguration($type) as $sensor) { // pour tous les capteurs de tous les types, on veut un nom et une cmd
            if ($sensor['name'] == '') {
              throw new Exception(__('Le champs Nom pour les capteurs d\'activité ne peut être vide',__FILE__));
            }

            if ($sensor['cmd'] == '') { // tests réalisés avec une cmd qui n'existe pas ('verrtgtr'), aucune erreur particuliere ni aucun log systeme, donc pas besoin de vérifier que c'est une cmd correcte et existante
              throw new Exception(__('Le champs Capteur d\'activité ne peut être vide',__FILE__));
            }
          }
        }
      }
    }

    public function postUpdate() {

    }

    public function preRemove() {

      log::add('seniorcareinactivity', 'info', 'Suppression de ' . $this->getHumanName());

      // quand on supprime notre eqLogic, on vire nos listeners associés
      $this->cleanAllListener();

      //supprime les CRON des actions d'alertes non encore appelés, affiche une alerte s'il y en avait
      //sert à ne pas laisser trainer des CRONs en cours si on change le message ou le label puis en enregistre. Mais ne devrait arriver qu'exceptionnellement
      $this->cleanAllCron(true);

      // On cherche et vire le cron pour les ABSENCES différées
      $cron = cron::byClassAndFunction('seniorcareinactivity', 'lifeSignAbsenceDelayed', array('eqLogic_id' => intval($this->getId()))); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et cette personne
      if (is_object($cron)){
        log::add('seniorcareinactivity', 'debug', 'Cron ABSENCE différé trouvé à supprimer pour eqLogic_id : ' . $cron->getOption()['eqLogic_id']);
        $cron->remove();
      }

    }

    public function postRemove() {

    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class seniorcareinactivityCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */




    public function execute($_options = array()) {
      //$this est une cmd ici

      $eqLogic = $this->getEqLogic(); // on recupere l'eqLogic (la personne) de cette commande

      if ($this->getLogicalId() == 'life_sign_ar') { //appel (via API ou extérieur) de l'AR

        log::add('seniorcareinactivity', 'info', $eqLogic->getHumanName() . ' - Actions Accusé de réception Inactivité à lancer.');

        $eqLogic->lifeSignAR();

      } else if ($this->getLogicalId() == 'life_sign_jour') { // appel de la commande action "declarer jour"

        log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - JOUR (par appel extérieur)');

        $eqLogic->setCache('jour', 1);
        log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *jour* : ' . $eqLogic->getCache('jour'));

      } else if ($this->getLogicalId() == 'life_sign_nuit') { // appel de la commande action "declarer nuit"

        log::add('seniorcareinactivity', 'info', $this->getHumanName() . ' - NUIT (par appel extérieur)');

        $eqLogic->setCache('jour', 0);
        log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *jour* : ' . $eqLogic->getCache('jour'));

      } else if ($this->getLogicalId() == 'life_sign_absence') { //appel (via API ou extérieur) de l'absence

        log::add('seniorcareinactivity', 'info', $eqLogic->getHumanName() . ' - ABSENCE (par appel extérieur)');

        $eqLogic->setCache('presence', 0); //on declare l'absence dans le cache. On reviendra present a n'importe quel detecteur de presence declenché
        log::add('seniorcareinactivity', 'debug', $this->getHumanName() . ' - cache *presence* : ' . $eqLogic->getCache('presence'));

        if ($eqLogic->getCache('alertLifeSignState')){ // si on était en phase d'alerte, on lance les actions d'annulation
          $eqLogic->execCancelActions();
        }

      } else { // sinon c'est un sensor et on veut juste sa valeur

        log::add('seniorcareinactivity', 'debug', 'Fct execute pour : ' . $this->getLogicalId() . $this->getHumanName() . '- valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));

        return jeedom::evaluateExpression($this->getValue());

      }

    }

    /*     * **********************Getteur Setteur*************************** */
}


