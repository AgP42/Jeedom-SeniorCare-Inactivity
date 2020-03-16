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

    public static function buttonAlerte($_option) { // fct appelée par le listener des buttons d'alerte, n'importe quel bouton arrive ici
      log::add('seniorcare', 'debug', '################ Detection d\'un trigger d\'un bouton d\'alerte ############');

    //  log::add('seniorcare', 'debug', 'Fct sensorConfort appelé par le listener : $_option[seniorcare_id] : ' . $_option['seniorcare_id'] . ' - value : ' . $_option['value'] . ' - event_id : ' . $_option['event_id']);

      $seniorcare = seniorcare::byId($_option['seniorcare_id']);

      foreach ($seniorcare->getConfiguration('action_alert_bt') as $action) { // on boucle pour executer toutes les actions définies
      log::add('seniorcare', 'debug', 'Un bouton d\'alerte a été activé, on va executer l action : ' . $action['cmd']);
        try {
          $options = array(); // va permettre d'appeller les options de configuration des actions, par exemple un scenario un message
          if (isset($action['options'])) {
            $options = $action['options'];
          }
          scenarioExpression::createAndExec('action', $action['cmd'], $options);
        } catch (Exception $e) {
          log::add('seniorcare', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
        }
      }

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

    public static function sensorConfort($_option) { // fct appelée par le listener des capteurs conforts (on sait pas lequel, ça serait trop simple, mais on connait l'event_id). Le listener n'est setté que si les seuils sont définis dans la conf, donc on revérifie pas ici que nos seuils sont non vides
// Question probablement bête,  Le Id du capteur ne pourrait-il pas être passé en paramètre de la fonction?
      log::add('seniorcare', 'debug', '################ Detection d\'un changement d\'un capteur confort ############');

    //  log::add('seniorcare', 'debug', 'Fct sensorConfort appelé par le listener : $_option[seniorcare_id] : ' . $_option['seniorcare_id'] . ' - value : ' . $_option['value'] . ' - event_id : ' . $_option['event_id']);

      $seniorcare = seniorcare::byId($_option['seniorcare_id']);
      if (is_object($seniorcare) && $seniorcare->getIsEnable() == 1 ) {
        foreach ($seniorcare->getConfiguration('confort') as $confort) { // on boucle direct dans la conf, on pourrait aussi boucler dans les cmd saved en DB et chercher nos infos vu qu'on les a enregistrés. TODO Si on en a jamais besoin, voir pour virer l'enregistrement des datas dans la DB (seuil haut et bas, ...)
          if ('#' . $_option['event_id'] . '#' == $confort['cmd']) { // on cherche quel est l'event qui nous a déclenché (vu qu'on a fait le choix d'un listener par groupe)

            //  log::add('seniorcare', 'debug', 'Fct sensorConfort appelé par le listener, name : ' . $confort['name'] . ' - cmd : ' . $confort['cmd']  . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);

            $seniorcare->checkAndActionSeuilsSensorConfort($seniorcare, $confort['name'], $_option['value'], $confort['seuilBas'], $confort['seuilHaut'], $confort['sensor_confort_type']);

          }

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

    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    // fct appellée par Jeedom aprés l'enregistrement de la configuration
    public function postSave() {


      //########## 1 - On va lire la configuration dans le JS et on la stocke dans des tableaux #########//

      //********** Pour les boutons d'alerte immediate ***********//

      $jsButtonAlerte = array();
      if (is_array($this->getConfiguration('alert_bt'))) {
        foreach ($this->getConfiguration('alert_bt') as $alert_bt) {
          if ($alert_bt['name'] != '' && $alert_bt['cmd'] != '') {

            $jsButtonAlerte[$alert_bt['name']] = $alert_bt;
            log::add('seniorcare', 'debug', 'Capteurs alert_bt config lue : ' . $alert_bt['name'] . ' - ' . $alert_bt['cmd']);

          }
        }
      } //*/

      //********** Pour les capteurs confort ***********//

      $jsSensorConfort = array();
      if (is_array($this->getConfiguration('confort'))) {
        foreach ($this->getConfiguration('confort') as $confort) {
          if ($confort['name'] != '' && $confort['cmd'] != '') { //s'ils contiennent une valeur dans le champs cmd et un nom

            $jsSensorConfort[$confort['name']] = $confort;

            log::add('seniorcare', 'debug', 'Capteurs confort config lue : ' . $confort['name'] . ' - '. $confort['cmd'] . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);
          }
        }
      }

      //########## 2 - On boucle dans toutes les cmd existantes, pour les modifier si besoin #########//

      foreach ($this->getCmd() as $cmd) {

        //********** Pour les boutons d'alerte immediate ***********//

        if ($cmd->getLogicalId() == 'ButtonAlerte') { // si c'est une cmd "ButtonAlerte"
          if (isset($jsButtonAlerte[$cmd->getName()])) { // on regarde si le nom correspond a un nom dans le tableau qu'on vient de recuperer du JS, si oui, on actualise les infos qui pourraient avoir bougé

            $buttonAlerte = $jsButtonAlerte[$cmd->getName()];

          //  log::add('seniorcare', 'error', 'Dans la boucle des cmd existantes pour : ' . $cmd->getName() . ' - ' . $buttonAlerte['cmd']);

            $cmd->setValue($buttonAlerte['cmd']);
            $cmd->save();

            // va chopper la valeur de la commande puis la suivre a chaque changement
            if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
              $cmd->setCollectDate('');
              $cmd->event($cmd->execute());
            }

            unset($jsButtonAlerte[$cmd->getName()]); // on a traité notre ligne, on la vire

          } else { // on a un SensorConfort qui était dans la DB mais dont le nom n'est plus dans notre JS : on la supprime ! Attention, si on a juste changé le nom, on va le supprimer et le recreer, donc perdre l'historique éventuel. //TODO : voir si ca pose probleme
            $cmd->remove();
          }
        } // fin bouton alerte

        //********** Pour les capteurs confort ***********//

        if ($cmd->getLogicalId() == 'SensorConfort') { // si c'est une cmd "SensorConfort"
          if (isset($jsSensorConfort[$cmd->getName()])) { // on regarde si le nom correspond a un nom dans le tableau qu'on vient de recuperer du JS, si oui, on actualise les infos qui pourraient avoir bougé

            $confort = $jsSensorConfort[$cmd->getName()];

        //    log::add('seniorcare', 'error', 'Dans la boucle des cmd existantes pour : ' . $cmd->getName() . ' - ' . $confort['cmd'] . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);

            $cmd->setValue($confort['cmd']);
            $cmd->setConfiguration('seuilBas', $confort['seuilBas']);
            $cmd->setConfiguration('seuilHaut', $confort['seuilHaut']);
            $cmd->setGeneric_type($confort['sensor_confort_type']);
            switch ($confort['sensor_confort_type']) {
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

            $cmd->save();

            // va choper la valeur de la commande puis la suivre a chaque changement
            if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
              $cmd->setCollectDate('');
              $cmd->event($cmd->execute());
            }

            unset($jsSensorConfort[$cmd->getName()]); // on a traité notre ligne, on la vire pour pas repasser dessus

          } else { // on a un SensorConfort qui était dans la DB mais dont le nom n'est plus dans notre JS : on la supprime ! Attention, si on a juste changé le nom, on va le supprimer et le recréer, donc perdre l'historique éventuel. //TODO : voir si ça pose problème (est-il possible d'effectuer un transfert d'id préalable? --> la question est : comment tu sais que c'est le meme puisqu'il n'a plus le meme nom ?)
            $cmdSensorConfort->remove();
          }
        } // fin capteurs confort

      } // fin foreach toutes les cmd du plugin

      //########## 3 - Maintenant on va creer les cmd nouvelles de notre conf (= celles qui restent dans nos tableaux) #########//

      //********** Pour les boutons d'alerte immediate ***********//

      foreach ($jsButtonAlerte as $bt_alerte) {

      //    log::add('seniorcare', 'debug', 'Capteurs bt_alerte config : ' . $bt_alerte['cmd'] . ' - ' . $bt_alerte['sensor_bt_alerte_type'] . ' - ' . $bt_alerte['seuilBas'] . ' - ' . $bt_alerte['seuilHaut']);

        $cmdButtonAlerte = new seniorcareCmd();
        $cmdButtonAlerte->setEqLogic_id($this->getId());
        $cmdButtonAlerte->setLogicalId('ButtonAlerte');
        $cmdButtonAlerte->setName($bt_alerte['name']);
        $cmdButtonAlerte->setValue($bt_alerte['cmd']);
        $cmdButtonAlerte->setType('info');
        $cmdButtonAlerte->setSubType('numeric');
        $cmdButtonAlerte->setIsVisible(0);
        $cmdButtonAlerte->setIsHistorized(1);
        $cmdButtonAlerte->setConfiguration('historizeMode', 'none');
        $cmdButtonAlerte->save();

        // va chopper la valeur de la commande puis la suivre a chaque changement
        if (is_nan($cmdButtonAlerte->execCmd()) || $cmdButtonAlerte->execCmd() == '') {
          $cmdButtonAlerte->setCollectDate('');
          $cmdButtonAlerte->event($cmdButtonAlerte->execute());
        }

      } //*/ // fin foreach restant. A partir de maintenant on a des cmd bouton d'alerte qui refletent notre config lue en JS


      //********** Pour les capteurs confort ***********//

      foreach ($jsSensorConfort as $confort) {

    //    log::add('seniorcare', 'debug', 'Capteurs confort config : ' . $confort['cmd'] . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);

        $cmdSensorConfort = new seniorcareCmd();
        $cmdSensorConfort->setEqLogic_id($this->getId());
        $cmdSensorConfort->setLogicalId('SensorConfort');
        $cmdSensorConfort->setName($confort['name']);
        $cmdSensorConfort->setValue($confort['cmd']);
        $cmdSensorConfort->setConfiguration('seuilBas', $confort['seuilBas']);
        $cmdSensorConfort->setConfiguration('seuilHaut', $confort['seuilHaut']);
        $cmdSensorConfort->setGeneric_type($confort['sensor_confort_type']);
        $cmdSensorConfort->setType('info');
        $cmdSensorConfort->setSubType('numeric');
        switch ($confort['sensor_confort_type']) {
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
                $unit = '-';  //TODO
                break;
        }
        $cmdSensorConfort->setUnite($unit);
        $cmdSensorConfort->setIsVisible(0);
        $cmdSensorConfort->setIsHistorized(1);
        $cmdSensorConfort->setConfiguration('historizeMode', 'avg');
        $cmdSensorConfort->setConfiguration('historizeRound', 2);
        $cmdSensorConfort->save();

        // va choper la valeur de la commande puis la suivre à chaque changement
        if (is_nan($cmdSensorConfort->execCmd()) || $cmdSensorConfort->execCmd() == '') {
          $cmdSensorConfort->setCollectDate('');
          $cmdSensorConfort->event($cmdSensorConfort->execute());
        }

      } // fin foreach restant. A partir de maintenant on a des cmd confort qui reflètent notre config lue en JS

      //########## 4 - Mise en place des listeners de capteurs pour réagir aux events #########//

      if ($this->getIsEnable() == 1) { // si notre eq est actif, on va lui definir nos listeners de capteurs

        // on boucle dans toutes les cmd existantes
        foreach ($this->getCmd() as $cmd) {

          if ($cmd->getLogicalId() == 'ButtonAlerte') { // si c'est une cmd "ButtonAlerte"

            $listener = listener::byClassAndFunction('seniorcare', 'buttonAlerte', array('seniorcare_id' => intval($this->getId())));
            if (!is_object($listener)) { // s'il existe pas, on le cree, sinon on le reprend
              $listener = new listener();
              $listener->setClass('seniorcare');
              $listener->setFunction('buttonAlerte'); // la fct qui sera appellée a chaque evenement sur une des sources écoutée
              $listener->setOption(array('seniorcare_id' => intval($this->getId())));
            }
            //  $listener->emptyEvent();
       //       $listener->setOption(array('cmd_id' => intval($cmd->getId()))); // si on met ici les valeurs, ca va nous creer un nouveau listener par capteur confort. C'est un choix a faire : un seul listener pour tout le monde et apres on cherche les infos selon qui l'a declanché, ou un listener chacun avec les details des infos dans les $_option. Choix aujourd'hui : on va faire 1 seul listener par type (signe de vie, confort, securité, ...), ca sera probablement plus lisible et 1 seule ligne pour le remove dans le preRemove()
            $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs conforts definis, quelque soit son type. On cherchera le trigger a l'appel de la fonction.

            log::add('seniorcare', 'debug', 'Button Alerte set listener - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

            $listener->save();

          } // fin cmd "ButtonAlerte"

          if ($cmd->getLogicalId() == 'SensorConfort') { // si c'est une cmd "SensorConfort"
          // TODO a-t-on vraiment besoin d'un listener par sensor confort ? un cron5 ou cron15 ne serait-il pas suffisant ? => actuellement j'ai codé cron15 et listener, à voir a l'usage -> d'acord avec toi, un cron15 au maximum devrait suffire ... TODO

            if($cmd->getConfiguration('seuilBas') != '' || $cmd->getConfiguration('seuilHaut') != '') { // si on a au moins 1 seuil défini, sinon sert a rien de traquer

              $listener = listener::byClassAndFunction('seniorcare', 'sensorConfort', array('seniorcare_id' => intval($this->getId())));
              if (!is_object($listener)) { // s'il existe pas, on le cree, sinon on le reprend
                $listener = new listener();
                $listener->setClass('seniorcare');
                $listener->setFunction('sensorConfort'); // la fct qui sera appellée a chaque evenement sur une des sources écoutée
                $listener->setOption(array('seniorcare_id' => intval($this->getId())));
              }
            //  $listener->emptyEvent();
       //       $listener->setOption(array('cmd_id' => intval($cmd->getId()))); // si on met ici les valeurs, ca va nous creer un nouveau listener par capteur confort. C'est un choix a faire : un seul listener pour tout le monde et apres on cherche les infos selon qui l'a déclenché, ou un listener chacun avec les details des infos dans les $_option. Choix aujourd'hui : on va faire 1 seul listener par type (signe de vie, confort, securité, ...), ca sera probablement plus lisible et 1 seule ligne pour le remove dans le preRemove()
       //       Pourquoi pas plutôt un listener par groupe de capteurs confort ? Sinon, on risque d'avoir des problèmes de détection de seuils différents en fonction du type de capteur.
              $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs conforts définis, quelque soit son type. On cherchera le trigger à l'appel de la fonction.

              log::add('seniorcare', 'debug', 'Capteurs confort set listener - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

              $listener->save();
            }
          } // fin cmd "SensorConfort"
        } // fin foreach cmd du plugin
      } // fin if eq actif


    } // fin fct postSave

    // preUpdate ⇒ Méthode appellée avant la mise à jour de votre objet
    // ici on vérifie la présence de nos champs de config obligatoire
    public function preUpdate() {

      /************ Pour les boutons d'alerte, il faut un nom et une cmd ***********/
      if (is_array($this->getConfiguration('alert_bt'))) {
        foreach ($this->getConfiguration('alert_bt') as $alert_bt) {
          if ($alert_bt['name'] == '') {
            throw new Exception(__('Le champs Nom pour les boutons d\'alertes ne peut être vide',__FILE__));
          }

          if ($alert_bt['cmd'] == '') {
            throw new Exception(__('Le champs Capteur pour les boutons d\'alertes ne peut être vide',__FILE__));
          }

        }
      } //*/

      /************ Pour les capteurs de confort, il faut un nom, une cmd, des seuils vide ou numerique et que le seuil haut soit sup au seuil bas ***********/
      if (is_array($this->getConfiguration('confort'))) {
        foreach ($this->getConfiguration('confort') as $confort) {
          if ($confort['name'] == '') {
            throw new Exception(__('Le champ Nom pour les capteurs de confort ne peut être vide',__FILE__));
          }

          if ($confort['cmd'] == '') {
            throw new Exception(__('Le champ Capteur pour les capteurs de confort ne peut être vide',__FILE__));
          }

          if ($confort['seuilHaut'] !='' && !is_numeric($confort['seuilHaut']) || $confort['seuilBas'] !='' && !is_numeric($confort['seuilBas'])) {
            throw new Exception(__('Capteur confort - ' . $confort['name'] . ', les valeurs des seuils doivent être numériques', __FILE__));
          }

          if ($confort['seuilBas'] > $confort['seuilHaut']) {
            throw new Exception(__('Capteur confort - ' . $confort['name'] . ', le seuil bas ne peut pas être supérieur au seuil haut', __FILE__)); // consequence : on peut pas ne definir qu'un seul seuil
          }

        }
      }

    }

    public function postUpdate() {

    }

    public function preRemove() {

      // quand on supprime notre eqLogic, on vire nos listeners associés
      $listener = listener::byClassAndFunction('seniorcare', 'sensorConfort', array('seniorcare_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
      }

      $listener = listener::byClassAndFunction('seniorcare', 'buttonAlerte', array('seniorcare_id' => intval($this->getId())));
      if (is_object($listener)) {
        $listener->remove();
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

    //  $eqLogic = $this->getEqLogic();

      if ($this->getLogicalId() == 'SensorConfort') {
        log::add('seniorcare', 'debug', 'Fct execute - Capteurs confort, valeur renvoyée : ' . round(jeedom::evaluateExpression($this->getValue()), 1));
        return round(jeedom::evaluateExpression($this->getValue()), 1);
      }

      if ($this->getLogicalId() == 'ButtonAlerte') {
        log::add('seniorcare', 'debug', 'Fct execute - ButtonAlerte, valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));
        return jeedom::evaluateExpression($this->getValue());
      } //*/ // TODO erreur ici

    }

    /*     * **********************Getteur Setteur*************************** */
}


