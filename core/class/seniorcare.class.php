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

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


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

    public function postSave() {

      //** Pour les capteurs confort **//

      $jsSensorConfort = array(); // on va stocker les sensor confort du JS ici, s'ils contiennent une valeur dans le champs cmd
      if (is_array($this->getConfiguration('confort'))) {
        foreach ($this->getConfiguration('confort') as $confort) {
          if ($confort['cmd'] != '') {
            $humanName = $confort['sensor_confort_type'] . ' - ' . cmd::byId(str_replace('#', '', $confort['cmd']))->getHumanName();

            $jsSensorConfort[$humanName] = $confort;
        //    log::add('seniorcare', 'error', 'Capteurs confort config : ' . $confort['cmd'] . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);

          }
        }
      }

/*      foreach ($this->getCmd() as $cmdSensorConfort) { // on boucle dans les cmd existantes, pour les modifier si besoin
        if ($cmdSensorConfort->getLogicalId() == 'SensorConfort') { // si c'est une cmd "SensorConfort"
          if (isset($jsSensorConfort[$cmdSensorConfort->getValues()])) { // on regarde si elle est dans le tableau qu'on vient de recuperer du JS, si oui, on actualise les infos qui pourraient avoir bougé
            // on bidouille pour avoir un nom unique et presque lisible
            $humanName = $confort['sensor_confort_type'] . ' - ' . cmd::byId(str_replace('#', '', $confort['cmd']))->getHumanName();
            $cmdSensorConfort->setName($humanName);
            $cmdSensorConfort->save();

            unset($jsSensorConfort[$cmdSensorConfort->getValues()])); // on a traité notre ligne, on la vire pour pas repasser dessus dans la boucle suivante
          } else { // on a un SensorConfort qui était dans la DB mais n'est plus dans notre JS : on la supprime !
            $cmdSensorConfort->remove();
          }
        }
      } //*/

      foreach ($jsSensorConfort as $confort) { // pour tous ceux restant (ils sont dans le tableau JS, mais n'étaient pas deja en DB), il faut les créer

        log::add('seniorcare', 'error', 'Capteurs confort config : ' . $confort['cmd'] . ' - ' . $confort['sensor_confort_type'] . ' - ' . $confort['seuilBas'] . ' - ' . $confort['seuilHaut']);


        $cmdSensorConfort = new seniorcareCmd();
        $cmdSensorConfort->setEqLogic_id($this->getId());

        // on bidouille pour avoir un nom unique et presque lisible
        $humanName = $confort['sensor_confort_type'] . ' - ' . cmd::byId(str_replace('#', '', $confort['cmd']))->getHumanName();
       // log::add('seniorcare', 'error', '$humanName : ' . $humanName);

        $cmdSensorConfort->setName($humanName);
        $cmdSensorConfort->setType('info');
        $cmdSensorConfort->setSubType('numeric');
        $cmdSensorConfort->setLogicalId('SensorConfort');
        $cmdSensorConfort->setIsVisible(0);
        $cmdSensorConfort->setIsHistorized(1);
        $cmdSensorConfort->setConfiguration('historizeMode', 'avg');
        $cmdSensorConfort->setConfiguration('historizeRound', 2);
    //    $cmdSensorConfort->setValues(str_replace('#', '', $confort['cmd'])); // on lui assigne en valeur le #xx# representant la cmd source. Vu que c'est cette meme cmd qui est utilisé comme index du tableau $jsSensorConfort, ca permet de comparer si on a deja la cmd ou pas (dans le foreach précédent), un peu tordu mais ca devrai tomber en marche... non erreur 500 ici
        $cmdSensorConfort->save();
        //*/
      } //*/


    }

    public function preUpdate() {

    }

    public function postUpdate() {

    }

    public function preRemove() {

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

    }

    /*     * **********************Getteur Setteur*************************** */
}


