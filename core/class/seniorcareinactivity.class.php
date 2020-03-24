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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class seniorcareinactivity extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    public static function sensorLifeSign($_option) { // fct appelée par le listener des capteurs d'activité, n'importe quel capteur arrive ici
      log::add('seniorcareinactivity', 'debug', '################ Detection d\'un capteur d\'activité ############');


      $seniorcareinactivity = seniorcareinactivity::byId($_option['seniorcareinactivity_id']);
      $seniorcareinactivity->setCache('lastLifeSignTimestamp', time()); // on met en cache le timestamp à l'heure du dernier event. C'est le cron qui regardera toutes les min si on est hors timer

      // on recupere l'état des des warning et alertes
      $actionWarningLifeSignLaunched = $seniorcareinactivity->getCache('actionWarningLifeSignLaunched');
      $actionAlertLifeSignLaunched = $seniorcareinactivity->getCache('actionAlertLifeSignLaunched');

      log::add('seniorcareinactivity', 'debug', 'Fct sensorLifeSign appelé par le listener, seniorcareinactivity_id : ' . $_option['seniorcareinactivity_id'] . ' - value : ' . $_option['value'] . ' - event_id : ' . $_option['event_id'] . ' - timestamp mis en cache : ' . time() . ' cache lu : ' . $actionWarningLifeSignLaunched . ' - '. $actionAlertLifeSignLaunched);

      if ($actionWarningLifeSignLaunched){ // si on était en phase d'avertissement, on lance les actions d'arret warning
        $seniorcareinactivity->execActions('action_desactivate_warning_life_sign');
      }

      if ($actionAlertLifeSignLaunched){ // si on était en phase d'alerte, on lance les actions d'arret alerte
        $seniorcareinactivity->execActions('action_desactivate_alert_life_sign');
      }

      // dans tous les cas on declare qu'on est pas en phase de warning ni d'alerte, puisqu'on vient de recevoir un signe de vie
      $seniorcareinactivity->setCache('actionWarningLifeSignLaunched', false);
      $seniorcareinactivity->setCache('actionAlertLifeSignLaunched', false);

    }


    public static function cron() { //executée toutes les min par Jeedom

      log::add('seniorcareinactivity', 'debug', '#################### CRON ###################');

      //pour chaque equipement (personne) declaré par l'utilisateur
      foreach (self::byType('seniorcareinactivity',true) as $seniorcareinactivity) {

        if (is_object($seniorcareinactivity) && $seniorcareinactivity->getIsEnable() == 1) { // si notre eq existe et est actif
          //TODO : c'est ici qu'il faudra gerer l'absence de la personne de son logement

          /********* Gestion de l'onglet "détection d'inactivité" ********/

          $lifeSignDetectionDelay = $seniorcareinactivity->getConfiguration('life_sign_timer') * 60; // on va lire la durée des timers dans la conf et on le met en secondes
          $lifeSignWarningDelay = $seniorcareinactivity->getConfiguration('warning_life_sign_timer') * 60;

          $lastLifeSignTimestamp = $seniorcareinactivity->getCache('lastLifeSignTimestamp'); // on va lire le timestamp du dernier trigger, en secondes
          $actionWarningLifeSignTimestamp = $seniorcareinactivity->getCache('actionWarningLifeSignTimestamp'); // on va lire le timestamp du lancement du warning, en secondes

          $now = time(); // timestamp courant, en s
          $secSinceLastLifeSign = $now - $lastLifeSignTimestamp; // le nb de secondes écoulées depuis le dernier event
          $secSinceWarningLifeSign = $now - $actionWarningLifeSignTimestamp; // le nb de secondes écoulées depuis le lancement des actions de warning

          $actionWarningLifeSignLaunched = $seniorcareinactivity->getCache('actionWarningLifeSignLaunched'); // on recupere l'état des des warning et alertes
          $actionAlertLifeSignLaunched = $seniorcareinactivity->getCache('actionAlertLifeSignLaunched');

          log::add('seniorcareinactivity', 'debug', 'Cache lu : ' . $actionWarningLifeSignLaunched . ' - ' . $actionAlertLifeSignLaunched);

          if ($secSinceLastLifeSign > $lifeSignDetectionDelay && !$actionWarningLifeSignLaunched && !$actionAlertLifeSignLaunched){
          //= le premier timer est échu mais aucune action ni warning si alerte n'est en cours --> on va lancer les actions warning
            log::add('seniorcareinactivity', 'debug', 'Actions Warning Life Sign A lancer. Timer lu : ' . $lifeSignDetectionDelay . ', sec depuis last event : ' . $secSinceLastLifeSign);

            $seniorcareinactivity->execActions('action_warning_life_sign');

            $seniorcareinactivity->setCache('actionWarningLifeSignLaunched', true); // on memorise qu'on a lancé les actions pour ne pas repeter toutes les min
            $seniorcareinactivity->setCache('actionWarningLifeSignTimestamp', $now); // on memorise l'heure du lancement du warning

          } else if ($secSinceLastLifeSign > $lifeSignDetectionDelay // 1er timer toujours échu
            && $actionWarningLifeSignLaunched && $secSinceWarningLifeSign > $lifeSignWarningDelay // on a deja lancé les actions warning et le timer de warning est échu aussi
            && !$actionAlertLifeSignLaunched){ // mais on a pas encore lancé d'alerte --> c'est le moment de le faire !
            log::add('seniorcareinactivity', 'debug', 'Actions Alerte Life Sign à lancer, temps depuis last event : ' . $secSinceLastLifeSign . ', sec depuis le lancement du warning : ' . $secSinceWarningLifeSign);

            // lance les actions alertes
            $seniorcareinactivity->execActions('action_alert_life_sign');

            $seniorcareinactivity->setCache('actionAlertLifeSignLaunched', true); // on memorise qu'on a lancé les actions d'alertes
            //TODO : gerer la repetition d'alerte toutes les 5min par exemple ?
          }

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

    public function execActions($_config, $_sensor_name = NULL, $_sensor_type = NULL, $_sensor_value = NULL) { // on donne le type d'action en argument et ca nous execute toute la liste. Les autres arguments sont pour les tag des messages si applicable

      log::add('seniorcareinactivity', 'debug', '################ Execution des actions du type ' . $_config . ' pour ' . $this->getName() .  ' ############');

      foreach ($this->getConfiguration($_config) as $action) { // on boucle pour executer toutes les actions définies
        try {
          $options = array(); // va permettre d'appeler les options de configuration des actions, par exemple un scenario un message
          if (isset($action['options'])) {
            $options = $action['options'];
            foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
              // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
              $value = str_replace('#senior_name#', $this->getName(), $value);
              $value = str_replace('#sensor_name#', $_sensor_name, $value);
              $value = str_replace('#sensor_type#', $_sensor_type, $value);
              $options[$key] = str_replace('#sensor_value#', $_sensor_value, $value);
            }
          }
          scenarioExpression::createAndExec('action', $action['cmd'], $options);
        } catch (Exception $e) {
          log::add('seniorcareinactivity', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
        }
      } //*/

    }

    /*     * *********************Méthodes d'instance************************* */

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

    }

    public function preSave() {

    }

    // fct appellée par Jeedom aprés l'enregistrement de la configuration
    public function postSave() {


      //########## 1 - On va lire la configuration des capteurs dans le JS et on la stocke dans un grand tableau #########//

      $jsSensors = array(
        'life_sign' => array(), // sous-tableau pour stocker toutes les infos des capteurs de détection d'activité
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


      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos différents types de capteurs. $key va prendre les valeurs suivantes : life_sign, alert_bt, confort puis security

        foreach ($this->getCmd() as $cmd) {
          if ($cmd->getLogicalId() == 'sensor_' . $key) {
            if (isset($jsSensor[$cmd->getName()])) { // on regarde si le nom correspond à un nom dans le tableau qu'on vient de recuperer du JS, si oui, on actualise les infos qui pourraient avoir bougé

              $sensor = $jsSensor[$cmd->getName()];
              $cmd->setValue($sensor['cmd']);

              if(isset($sensor['sensor_'.$key.'_type'])){ // ce sera vrai pour les types life-sign
                $cmd->setGeneric_type($sensor['sensor_'.$key.'_type']);
              }

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

          if(isset($sensor['sensor_'.$key.'_type'])){ // ce sera vrai pour les types life-sign
            $cmd->setGeneric_type($sensor['sensor_'.$key.'_type']);
          }

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

      // on declare qu'on est pas en phase de warning ni d'alerte
  //    $this->setCache('actionWarningLifeSignLaunched', false);
  //    $this->setCache('actionAlertLifeSignLaunched', false);


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
              throw new Exception(__('Le champs Nom pour les capteurs ('.$type.') ne peut être vide',__FILE__));
            }

            if ($sensor['cmd'] == '') { // TODO on pourrait aussi ici vérifier que notre commande existe pour pas avoir de problemes apres...
              throw new Exception(__('Le champs Capteur ('.$type.') ne peut être vide',__FILE__));
            }
          }
        }
      }
    }

    public function postUpdate() {

    }

    public function preRemove() {

      // quand on supprime notre eqLogic, on vire nos listeners associés
      $this->cleanAllListener();

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

      log::add('seniorcareinactivity', 'debug', 'Fct execute pour : ' . $this->getLogicalId() . $this->getHumanName() . '- valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));

      return jeedom::evaluateExpression($this->getValue());

    }

    /*     * **********************Getteur Setteur*************************** */
}


