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

class seniorcare extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */


    public static function buttonAlert($_option) { // fct appelée par le listener des buttons d'alerte, n'importe quel bouton arrive ici
      log::add('seniorcare', 'debug', '################ Detection d\'un trigger d\'un bouton d\'alerte ############');

      $seniorcare = seniorcare::byId($_option['seniorcare_id']); // on cherche la personne correspondant au bouton d'alerte
      $seniorcare->execActions('action_alert_bt'); // on appelle les actions definies pour cette personne pour les boutons d'alertes

    }

    public static function sensorSecurity($_option) { // fct appelée par le listener des capteurs de sécurité, n'importe quel capteur arrive ici
      log::add('seniorcare', 'debug', '################ Detection d\'un trigger de sécurité ############');

      $seniorcare = seniorcare::byId($_option['seniorcare_id']); // on cherche la personne correspondant au bouton d'alerte
      $seniorcare->execActions('action_security'); // on appelle les actions definies pour cette personne

    }

    public static function checkAndActionSeuilsSensorConfort($seniorcare, $_name, $_value, $_seuilBas, $_seuilHaut, $_type) { // appelée soit par le cron, soit par un listener (via la fct sensorConfort), va regarder si on est dans les seuils définis et si non appliquer les actions voulues

    // TODO on pourrait ajouter une durée min pendant laquelle le capteur est hors seuils avant de déclencher l'alerte
    // TODO on pourrait limiter l'alerte à 1 fois par heure (à parametrer ?)
    // TODO on pourrait ajouter la date de collecte de la valeur pour ne pas faire des alertes sur une vieille info, ou au contraire ajouter une alerte si pas de valeur fraiche pendant un certain temps. Mais ça peut etre aussi géré par le core dans les configuration de la cmd...

      log::add('seniorcare', 'debug', 'Fct checkAndActionSeuilsSensorConfort, name : ' . $_name . ' - ' . $_type . ' - ' . $_value . ' - ' . $_seuilBas . ' - ' . $_seuilHaut);

      if ($_value > $_seuilHaut || $_value < $_seuilBas){
        foreach ($seniorcare->getConfiguration('action_warning_confort') as $action) {
        log::add('seniorcare', 'debug', 'Capteurs confort :' . $_name . ' sort des seuils !, on va executer l action : ' . $action['cmd']);
          try {
            $options = array(); // va permettre d'appeler les options de configuration des actions, par exemple quel scenario ou le message si action messagerie
            if (isset($action['options'])) {
              $options = $action['options'];
              foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
                // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
                $value = str_replace('#nom_personne#', $seniorcare->getName(), $value);
                $value = str_replace('#nom_capteur#', $_name, $value);
                $value = str_replace('#type_capteur#', $_type, $value);
                $value = str_replace('#valeur#', $_value, $value);
                $value = str_replace('#seuil_bas#', $_seuilBas, $value);
                switch ($_type) {
                    case 'temperature':
                        $unit = '°C';
                        break;
                    case 'humidite':
                        $unit = '%';
                        break;
                    case 'co2':
                        $unit = 'ppm';
                        break;
                    default:
                        $unit = '-';
                        break;
                }
                $value = str_replace('#unite#', $unit, $value);
                $options[$key] = str_replace('#seuil_haut#', $_seuilHaut, $value);
              }
            }
            scenarioExpression::createAndExec('action', $action['cmd'], $options);
          } catch (Exception $e) {
            log::add('seniorcare', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
          }
        }
      } else {
        log::add('seniorcare', 'debug', 'Capteurs confort :' . $_name . ' OK, dans les seuils !');
      }

    }

    public static function sensorConfort($_option) { // fct appelée par le listener des capteurs conforts (on sait pas lequel, ça serait trop simple, mais on connait l'event_id).

      log::add('seniorcare', 'debug', '################ Detection d\'un changement d\'un capteur confort ############');

    //  log::add('seniorcare', 'debug', 'Fct sensorConfort appelé par le listener : $_option[seniorcare_id] : ' . $_option['seniorcare_id'] . ' - value : ' . $_option['value'] . ' - event_id : ' . $_option['event_id']);

      $seniorcare = seniorcare::byId($_option['seniorcare_id']);
      if (is_object($seniorcare) && $seniorcare->getIsEnable() == 1 ) {
        foreach ($seniorcare->getConfiguration('confort') as $confort) { // on boucle direct dans la conf
          if ('#' . $_option['event_id'] . '#' == $confort['cmd']) { // on cherche quel est l'event qui nous a déclenché

          //  log::add('seniorcare', 'debug', 'Fct sensorConfort appelé par le listener, name : ' . $confort['name'] . ' - cmd : ' . $confort['cmd']  . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);

            if($confort['seuilBas'] != '' || $confort['seuilHaut'] != '') { // si les seuils sont definis (on set le listener de toutes facons maintenant)
              $seniorcare->checkAndActionSeuilsSensorConfort($seniorcare, $confort['name'], $_option['value'], $confort['seuilBas'], $confort['seuilHaut'], $confort['sensor_confort_type']);
            }

          }

        }
      } //*/

    }

    public static function sensorLifeSign($_option) { // fct appelée par le listener des capteurs d'activité, n'importe quel capteur arrive ici
      log::add('seniorcare', 'debug', '################ Detection d\'un capteur d\'activité ############');

      log::add('seniorcare', 'debug', 'Fct sensorLifeSign appelé par le listener, seniorcare_id : ' . $_option['seniorcare_id'] . ' - value : ' . $_option['value'] . ' - event_id : ' . $_option['event_id'] . ' - timestamp mis en cache : ' . time());

      $seniorcare = seniorcare::byId($_option['seniorcare_id']);
      $seniorcare->setCache('lastLifeSignTimestamp', time()); // on met en cache le timestamp à l'heure du dernier event. C'est le cron qui regardera toutes les min si on est hors timer

      // on recupere l'état des des warning et alertes
      $actionWarningLifeSignOngoing = $seniorcare->getCache('actionWarningLifeSignOngoing');
      $actionAlertLifeSignOngoing = $seniorcare->getCache('actionAlertLifeSignOngoing');

      if ($actionWarningLifeSignOngoing){ // si on était en phase d'avertissement, on lance les actions d'arret warning
        $seniorcare->execActions('action_desactivate_warning_life_sign');
      }

      if ($actionAlertLifeSignOngoing){ // si on était en phase d'alerte, on lance les actions d'arret alerte
        $seniorcare->execActions('action_desactivate_alert_life_sign');
      }

      // dans tous les cas on declare qu'on est pas en phase de warning ni d'alerte, puisqu'on vient de recevoir un signe de vie
      $seniorcare->setCache('actionWarningLifeSignOngoing', false);
      $seniorcare->setCache('actionAlertLifeSignOngoing', false);

    }


    public static function cron() { //executée toutes les min par Jeedom

      log::add('seniorcare', 'debug', '#################### CRON ###################');

      //pour chaque equipement (personne) declaré par l'utilisateur
      foreach (self::byType('seniorcare',true) as $seniorcare) {

        if (is_object($seniorcare) && $seniorcare->getIsEnable() == 1) { // si notre eq existe et est actif
          //TODO : c'est ici qu'il faudra gerer l'absence de la personne de son logement

          /********* Gestion de l'onglet "détection d'inactivité" ********/

          $lifeSignDetectionDelay = $seniorcare->getConfiguration('life_sign_timer') * 60; // on va lire la durée des timers dans la conf et on le met en secondes
          $lifeSignWarningDelay = $seniorcare->getConfiguration('warning_life_sign_timer') * 60;

          $lastLifeSignTimestamp = $seniorcare->getCache('lastLifeSignTimestamp'); // on va lire le timestamp du dernier trigger, en secondes
          $actionWarningStartTimestamp = $seniorcare->getCache('actionWarningStartTimestamp'); // on va lire le timestamp du lancement du warning, en secondes
          $now = time(); // timestamp courant, en s
          $secSinceLastLifeSign = $now - $lastLifeSignTimestamp; // le nb de secondes écoulées depuis le dernier event
          $secSinceWarningLifeSign = $now - $actionWarningStartTimestamp; // le nb de secondes écoulées depuis le lancement des actions de warning

          $actionWarningLifeSignOngoing = $seniorcare->getCache('actionWarningLifeSignOngoing'); // on recupere l'état des des warning et alertes
          $actionAlertLifeSignOngoing = $seniorcare->getCache('actionAlertLifeSignOngoing');

          if ($secSinceLastLifeSign > $lifeSignDetectionDelay && !$actionWarningLifeSignOngoing && !$actionAlertLifeSignOngoing){
          //= le premier timer est échu mais aucune action ni warning si alerte n'est en cours --> on va lancer les actions warning
            log::add('seniorcare', 'debug', 'Actions Warning Life Sign A lancer. Timer lu : ' . $lifeSignDetectionDelay . ', sec depuis last event : ' . $secSinceLastLifeSign);

            $seniorcare->execActions('action_warning_life_sign');

            $seniorcare->setCache('actionWarningLifeSignOngoing', true); // on memorise qu'on a lancé les actions pour ne pas repeter toutes les min
            $seniorcare->setCache('actionWarningStartTimestamp', $now); // on memorise l'heure du lancement du warning

          } else if ($secSinceLastLifeSign > $lifeSignDetectionDelay // 1er timer toujours échu
            && $actionWarningLifeSignOngoing && $secSinceWarningLifeSign > $lifeSignWarningDelay // on a deja lancé les actions warning et le timer de warning est échu aussi
            && !$actionAlertLifeSignOngoing){ // mais on a pas encore lancé d'alerte --> c'est le moment de le faire !
            log::add('seniorcare', 'debug', 'Actions Alerte Life Sign à lancer, temps depuis last event : ' . $secSinceLastLifeSign . ', sec depuis le lancement du warning : ' . $secSinceWarningLifeSign);

            // lance les actions alertes
            $seniorcare->execActions('action_alert_life_sign');

            $seniorcare->setCache('actionAlertLifeSignOngoing', true); // on memorise qu'on a lancé les actions d'alertes
            //TODO : gerer la repetition d'alerte toutes les 5min par exemple ?
          }

        } // fin if eq actif

      } // fin foreach equipement

    } //fin cron

    public function execActions($_config) { // on donne le type d'action en argument et ca nous execute toute la liste

      log::add('seniorcare', 'debug', '################ Execution des actions du type ' . $_config . ' pour ' . $this->getHumanName() .  ' ############');

      foreach ($this->getConfiguration($_config) as $action) { // on boucle pour executer toutes les actions définies
        try {
          $options = array(); // va permettre d'appeller les options de configuration des actions, par exemple un scenario un message
          if (isset($action['options'])) {
            $options = $action['options'];
            foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
              // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
              $options[$key] = str_replace('#nom_personne#', $this->getName(), $value);
            }
          }
          scenarioExpression::createAndExec('action', $action['cmd'], $options);
        } catch (Exception $e) {
          log::add('seniorcare', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
        }
      } //*/

    }

    //*
    // * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom - sert de backup si on rate un listener. A voir a l'usage si on veut garder ca et la fréquence... TODO
  /*    public static function cron15() {

        log::add('seniorcare', 'debug', '#################### CRON 15 ###################');

        //pour chaque equipement (personne) declaré par l'utilisateur
        foreach (self::byType('seniorcare',true) as $seniorcare) {

          if (is_object($seniorcare) && $seniorcare->getIsEnable() == 1) { // si notre eq existe et est actif

            foreach ($seniorcare->getConfiguration('confort') as $confort) { // on boucle direct dans la conf

          //    log::add('seniorcare', 'debug', 'Fct sensorConfort appelé par le cron, name : ' . $confort['name'] . ' - cmd : ' . $confort['cmd']  . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);

              if($confort['seuilBas'] != '' || $confort['seuilHaut'] != '') { // évalue si on a au moins 1 seuil defini (de toute facon on peut pas n'en remplir qu'1 des deux)

                $valeur = jeedom::evaluateExpression($confort['cmd']);
                $seniorcare->checkAndActionSeuilsSensorConfort($seniorcare, $confort['name'], $valeur, $confort['seuilBas'], $confort['seuilHaut'], $confort['sensor_confort_type']);

              }

            } // fin foreach tous les capteurs conforts de la conf

          } // fin if eq actif

        } // fin foreach equipement

      } //*/

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

    public function cleanAllListener() {

      log::add('seniorcare', 'debug', 'Fct cleanAllListener pour : ' . $this->getName());

      $listeners = listener::byClass('seniorcare'); // on prend tous nos listeners de ce plugin, pour toutes les personnes
      foreach ($listeners as $listener) {
        $seniorcare_id_listener = $listener->getOption()['seniorcare_id'];

    //    log::add('seniorcare', 'debug', 'cleanAllListener id lue : ' . $seniorcare_id_listener . ' et nous on est l id : ' . $this->getId());

        if($seniorcare_id_listener == $this->getId()){ // si on correspond a la bonne personne, on le vire
          $listener->remove();
        }

      }


// sinon on a la version bourin, à mettre a jour pour chaque nouveau type de listener... :
/*      $listener = listener::byClassAndFunction('seniorcare', 'sensorLifeSign', array('seniorcare_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
      $listener = listener::byClassAndFunction('seniorcare', 'buttonAlert', array('seniorcare_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
      $listener = listener::byClassAndFunction('seniorcare', 'sensorConfort', array('seniorcare_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }
      $listener = listener::byClassAndFunction('seniorcare', 'sensorSecurity', array('seniorcare_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      } //*/

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
        'alert_bt' => array(), // idem bouton d'alertes
        'confort' => array(), // idem capteurs conforts
        'security' => array(), // idem capteurs sécurité
      );

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs pour recuperer les infos
        log::add('seniorcare', 'debug', 'Boucle de $jsSensors : key : ' . $key);

        if (is_array($this->getConfiguration($key))) {
          foreach ($this->getConfiguration($key) as $sensor) {
            if ($sensor['name'] != '' && $sensor['cmd'] != '') { // si le nom et la cmd sont remplis

              $jsSensors[$key][$sensor['name']] = $sensor; // on stocke toute la conf, c'est à dire tout ce qui dans notre js avait la class "expressionAttr". Pour retrouver notre champs exact : $jsSensors[$key][$sensor['name']][data-l1key]. // attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut écrire, pas dans la variable qui le represente dans cette boucle
              log::add('seniorcare', 'debug', 'Capteurs sensor config lue : ' . $sensor['name'] . ' - ' . $sensor['cmd']);

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

              if(isset($sensor['sensor_'.$key.'_type'])){ // ce sera vrai pour les types life-sign et confort
                $cmd->setGeneric_type($sensor['sensor_'.$key.'_type']);
              }

                // commenté car jamais utilisé, on va directement chercher dans la conf. A voir si sa sera utile de le garder en DB un jour... TODO
          /*    if($key == 'confort'){ // uniquement pour confort

                $cmd->setConfiguration('seuilBas', $sensor['seuilBas']);
                $cmd->setConfiguration('seuilHaut', $sensor['seuilHaut']);
                switch ($sensor['sensor_confort_type']) {
                    case 'temperature':
                        $unit = '°C';
                        break;
                    case 'humidite':
                        $unit = '%';
                        break;
                    case 'co2':
                        $unit = 'ppm'; //Les sondes de CO2 présentent généralement une plage de mesure de 0-5000 ppm. Il faudra recommander dans la doc une alerte à partir de 1000ppm max
                        break;
                    default:
                        $unit = '-'; //TODO
                        break;
                }
                $cmd->setUnite($unit);

              } //*/

              $cmd->save();

              // va chopper la valeur de la commande puis la suivre a chaque changement
              if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
                $cmd->setCollectDate('');
                $cmd->event($cmd->execute());
              }

              unset($jsSensors[$key][$cmd->getName()]); // on a traité notre ligne, on la vire. Attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut virer notre ligne

            } else { // on a un sensor qui était dans la DB mais dont le nom n'est plus dans notre JS : on la supprime ! Attention, si on a juste changé le nom, on va le supprimer et le recreer, donc perdre l'historique éventuel. //TODO : voir si ça pose problème (est-il possible d'effectuer un transfert d'id préalable? --> la question est : comment tu sais que c'est le meme puisqu'il n'a plus le meme nom ?) Oui à améliorer, quand tout le reste sera ok ! ;-)
              $cmd->remove();
            }
          }
        } // fin foreach toutes les cmd du plugin
      } // fin foreach nos differents types de capteurs//*/

      //########## 3 - Maintenant on va creer les cmd nouvelles de notre conf (= celles qui restent dans notre tableau) #########//

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs. $key va prendre les valeurs suivantes : life_sign, alert_bt, confort puis security

        foreach ($jsSensor as $sensor) { // pour chacun des capteurs de ce type

          // ce qui identifie d'un point de vu unique notre capteur c'est son type et sa value(cmd)

          log::add('seniorcare', 'debug', 'New Capteurs config : type : ' . $key . ', sensor name : ' . $sensor['name'] . ', sensor cmd : ' . $sensor['cmd']);

          $cmd = new seniorcareCmd();
          $cmd->setEqLogic_id($this->getId());
          $cmd->setLogicalId('sensor_' . $key);
          $cmd->setName($sensor['name']);
          $cmd->setValue($sensor['cmd']);
          $cmd->setType('info');
          $cmd->setSubType('numeric');
          $cmd->setIsVisible(0);
          $cmd->setIsHistorized(1);
          $cmd->setConfiguration('historizeMode', 'none');

          if(isset($sensor['sensor_'.$key.'_type'])){ // ce sera vrai pour les types life-sign et confort
            $cmd->setGeneric_type($sensor['sensor_'.$key.'_type']);
          }

          if($key == 'confort'){ // uniquement pour les commandes de types confort

            // commenté car jamais utilisé, on va directement chercher dans la conf. A voir si sa sera utile de le garder en DB un jour... TODO
            /*
            $cmd->setConfiguration('seuilBas', $sensor['seuilBas']);
            $cmd->setConfiguration('seuilHaut', $sensor['seuilHaut']);
            switch ($sensor['sensor_confort_type']) {
                case 'temperature':
                    $unit = '°C';
                    break;
                case 'humidite':
                    $unit = '%';
                    break;
                case 'co2':
                    $unit = 'ppm'; //Les sondes de CO2 présentent généralement une plage de mesure de 0-5000 ppm. Il faudra recommander dans la doc une alerte à partir de 1000ppm max
                    break;
                default:
                    $unit = '-'; //TODO
                    break;
            }
            $cmd->setUnite($unit); //*/
            $cmd->setConfiguration('historizeMode', 'avg');
            $cmd->setConfiguration('historizeRound', 2);

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
          } else if ($cmd->getLogicalId() == 'sensor_alert_bt'){
            $listenerFunction = 'buttonAlert';
          } else if ($cmd->getLogicalId() == 'sensor_confort'){
            $listenerFunction = 'sensorConfort';
          } else if ($cmd->getLogicalId() == 'sensor_security'){
            $listenerFunction = 'sensorSecurity';
          }

          // on set le listener associée
          $listener = listener::byClassAndFunction('seniorcare', $listenerFunction, array('seniorcare_id' => intval($this->getId())));
          if (!is_object($listener)) { // s'il existe pas, on le cree, sinon on le reprend
            $listener = new listener();
            $listener->setClass('seniorcare');
            $listener->setFunction($listenerFunction); // la fct qui sera appellée a chaque evenement sur une des sources écoutée
            $listener->setOption(array('seniorcare_id' => intval($this->getId())));
          }
          $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs definis. On cherchera le trigger a l'appel de la fonction si besoin

          log::add('seniorcare', 'debug', 'sensor listener set - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

          $listener->save();

        } // fin foreach cmd du plugin
      } // fin if eq actif
      else { // notre eq n'est pas actif ou il a ete desactivé, on supprime les listeners s'ils existaient

        $this->cleanAllListener();

      }


    } // fin fct postSave

    // preUpdate ⇒ Méthode appellée avant la mise à jour de votre objet
    // ici on vérifie la présence de nos champs de config obligatoire
    public function preUpdate() {

      $sensorsType = array( // liste des types avec des champs a vérifier
        'life_sign',
        'alert_bt',
        'confort',
        'security'
      );

      foreach ($sensorsType as $type) {
        if (is_array($this->getConfiguration($type))) {
          foreach ($this->getConfiguration($type) as $sensor) { // pour tous les capteurs de tous les types, on veut un nom et une cmd
            if ($sensor['name'] == '') {
              throw new Exception(__('Le champs Nom pour les capteurs ne peut être vide',__FILE__));
            }

            if ($sensor['cmd'] == '') { // TODO on pourrait aussi ici vérifier que notre commande existe pour pas avoir de problemes apres...
              throw new Exception(__('Le champs Capteur ne peut être vide',__FILE__));
            }

            if($type == 'confort'){ // uniquement pour les capteurs conforts, vérif sur les champs seuils

              if ($confort['seuilHaut'] !='' && !is_numeric($confort['seuilHaut']) || $confort['seuilBas'] !='' && !is_numeric($confort['seuilBas'])) {
                throw new Exception(__('Capteur confort - ' . $confort['name'] . ', les valeurs des seuils doivent être numériques', __FILE__));
              }

              if ($confort['seuilBas'] > $confort['seuilHaut']) {
                throw new Exception(__('Capteur confort - ' . $confort['name'] . ', le seuil bas ne peut pas être supérieur au seuil haut', __FILE__)); // consequence : on peut pas ne definir qu'un seul seuil
              }

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

class seniorcareCmd extends cmd {
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

      log::add('seniorcare', 'debug', 'Fct execute - valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));

      return jeedom::evaluateExpression($this->getValue());

    //  $eqLogic = $this->getEqLogic();

   /*   if ($this->getLogicalId() == 'sensor_confort') {
        log::add('seniorcare', 'debug', 'Fct execute - Capteurs confort, valeur renvoyée : ' . round(jeedom::evaluateExpression($this->getValue()), 1));
        return round(jeedom::evaluateExpression($this->getValue()), 1);
      }

      if ($this->getLogicalId() == 'sensor_alert_bt' || $this->getLogicalId() == 'sensor_life_sign' || $this->getLogicalId() == 'sensor_security') {
        log::add('seniorcare', 'debug', 'Fct execute - sensor_alert_bt or sensor_life_sign or sensor_security, valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));
        return jeedom::evaluateExpression($this->getValue());
      } //*/



    }

    /*     * **********************Getteur Setteur*************************** */
}


