<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('seniorcareinactivity');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">

  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
        <div class="cursor eqLogicAction logoPrimary" data-action="add">
          <i class="fas fa-plus-circle"></i>
          <br>
          <span>{{Ajouter}}</span>
      </div>
        <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
      <br>
      <span>{{Configuration}}</span>
    </div>
    </div>
    <legend><i class="fas fa-user-plus"></i> {{Personne dépendante}}</legend>
  	   <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
  <div class="eqLogicThumbnailContainer">
      <?php
  foreach ($eqLogics as $eqLogic) {
  	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
  	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
  	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
  	echo '<br>';
  	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
  	echo '</div>';
  }
  ?>
  </div>
  </div>

<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Général}}</a></li>

    <li role="presentation"><a href="#absencestab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-calendar-alt"></i> {{Gestion absences}}</a></li>

    <li role="presentation"><a href="#daynighttab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-calendar-alt"></i> {{Périodes jour/nuit}}</a></li>

    <li role="presentation"><a href="#sensorlifesigntab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-heartbeat"></i> {{Capteurs d'activité}}</a></li>

    <li role="presentation"><a href="#actionalertlifesigntab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-users"></i> {{Actions d'alerte}}</a></li>

    <li role="presentation"><a href="#arlifesigntab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-check-square"></i> {{Accusé de réception}}</a></li>

    <li role="presentation"><a href="#cancellifesigntab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-bell-slash"></i> {{Annulation d'alerte}}</a></li>

    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Avancé - Commandes Jeedom}}</a></li>

  </ul>

  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">

    <!-- TAB GENERAL -->
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-tachometer-alt"></i> {{Informations Jeedom}} </legend>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom Jeedom}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom }}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
            <div class="col-sm-3">
              <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
                  foreach (jeeObject::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                ?>
              </select>
            </div>
          </div>

        	<div class="form-group">
        		<label class="col-sm-3 control-label"></label>
        		<div class="col-sm-9">
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
        		</div>
        	</div>

        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-user-edit"></i> {{Informations concernant la personne dépendante}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces informations seront utilisées uniquement pour la saisie de tags dans les messages d'alertes, tous ces champs sont facultatifs.}}"></i></sup></legend>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="senior_name" placeholder="{{Nom de la personne dépendante}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#senior_name#</strong>}}</div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Téléphone }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="senior_phone" placeholder="{{Numéro de téléphone de la personne dépendante}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#senior_phone#</strong>}}</div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse ou n° logement }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="senior_address" placeholder="{{Adresse ou n° logement de la personne dépendante}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#senior_address#</strong>}}</div>
          </div>

          <br>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom personne de confiance }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="trusted_person_name" placeholder="{{Nom de la personne de confiance}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#trusted_person_name#</strong>}}</div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Téléphone personne de confiance }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="trusted_person_phone" placeholder="{{Numéro de téléphone de la personne de confiance}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#trusted_person_phone#</strong>}}</div>
          </div>
        </fieldset>
      </form>

    </div>

    <!-- TAB absences -->
    <div class="tab-pane" id="absencestab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration d'absence du logement. Absence initiée via plugin Agenda, boutons, scénarios ou appels via API. Présence automatiquement réactivée sur déclenchement d'un capteur d'activité.}}
      </div>

      <?php
      try {
        $plugin = plugin::byId('calendar');
        if (is_object($plugin)) {
          ?>

          <legend><i class="fas fa-clock"></i> {{Utiliser le plugin Agenda pour la gestion des absences}}<sup><i class="fas fa-question-circle tooltips" title="{{Le début des plages d'absences est à configurer dans le plugin Agenda. La programmation réalisée s'affichera ici. Tout capteur d'activité détecté relancera la surveillance.}}"></i></sup></legend>
          <form class="form-horizontal">
            <fieldset>
              <div id="div_schedule_absence"></div>
            </fieldset>
          </form>

          <?php
        }
      } catch (Exception $e) {

      }
      ?>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-toggle-off"></i> {{Utiliser un capteur pour déclarer l'absence}}<sup><i class="fas fa-question-circle tooltips" title="{{Après déclenchement de ce capteur, le plugin déclarera le début de l'absence à l'issue du délai configuré. Une fois l'absence déclarée, tout capteur d'activité détecté relancera la surveillance.}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorAbsence" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>

          <div id="div_absence"></div>

          <br>

          <div class="form-group">
            <label class="col-sm-2 control-label"><i class="fas fa-stopwatch"></i> {{Délai avant absence effective (min)}} <sup><i class="fas fa-question-circle tooltips" title="{{Délai maximum avant déclaration de l'absence. Les absences sont déclarés en début de minute.}}"></i></sup></label>
            <div class="col-sm-1">
              <input type="number" min="0" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="absence_timer" />
            </div>
          </div>

        </fieldset>
      </form>

      <legend><i class="fas fa-external-link-alt"></i> {{Via un scenario, un autre plugin ou un appel extérieur}}<sup><i class="fas fa-question-circle tooltips" title="{{Réglages/Système/Configuration/Réseaux doit être correctement renseigné !}}"></i></sup></legend>

      <div class="col-sm-12">
        <?php
        if(init('id') != ''){
          $eqLogic = eqLogic::byId(init('id'));
          if(is_object($eqLogic)){
            $cmd = $eqLogic->getCmd(null, 'life_sign_absence');
            if(is_object($cmd)){
              echo '<p>N\'importe où dans Jeedom, appelez cette commande : <i class="fas fa-code-branch"></i><b>  '. $cmd->getHumanName() . '</b><br>Où via l\'extérieur : <a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a></p>';
            } else {
              echo 'Hum... vous n\'auriez pas supprimé manuellement la commande "Déclarer absence" par hasard ? Il vous reste plus qu\'à supprimer cet équipement et recommencer !';
            }
          } else {
            echo 'Erreur : cet eqLogic n\'existe pas';
          }
        } else {
          echo 'Sauvegarder ou rafraichir la page pour afficher les infos';
        }
        ?>
      </div>

    </div>

    <!-- TAB jour/nuit -->
    <div class="tab-pane" id="daynighttab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des périodes jour et nuit. Les états jour/nuit servent à utiliser le délai correspondant pour les capteurs d'activités. Vous pouvez déclarer ces périodes via la plugin agenda ou via des scenarios notamment.}}
      </div>

      <?php
      try {
        $plugin = plugin::byId('calendar');
        if (is_object($plugin)) {
          ?>

          <legend><i class="fas fa-clock"></i> {{Utiliser le plugin Agenda pour la gestion jour/nuit}}<sup><i class="fas fa-question-circle tooltips" title="{{Le début des plages jour et nuit est à configurer dans le plugin Agenda. La programmation réalisée s'affichera ici. Nous pouvez définir autant de période 'nuit' que souhaité. En l'absence de programmation, le délai utilisé sera celui de 'jour'.}}"></i></sup></legend>
          <form class="form-horizontal">
            <fieldset>
              <div id="div_schedule_daynight"></div>
            </fieldset>
          </form>

          <?php
        }
      } catch (Exception $e) {

      }
      ?>

      <legend><i class="fas fa-external-link-alt"></i> {{Via un scenario, un autre plugin ou un appel extérieur}}<sup><i class="fas fa-question-circle tooltips" title="{{Réglages/Système/Configuration/Réseaux doit être correctement renseigné !}}"></i></sup></legend>

      <div class="col-sm-12">
        <label class="col-sm-1 control-label">{{Déclarer jour }}</label>
        <?php
        if(init('id') != ''){
          $eqLogic = eqLogic::byId(init('id'));
          if(is_object($eqLogic)){
            $cmd = $eqLogic->getCmd(null, 'life_sign_jour');
            if(is_object($cmd)){
              echo '<p>N\'importe où dans Jeedom, appelez cette commande : <i class="fas fa-code-branch"></i><b>  '. $cmd->getHumanName() . '</b><br>Où via l\'extérieur : <a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a></p>';
            } else {
              echo 'Hum... vous n\'auriez pas supprimé manuellement la commande "Déclarer jour" par hasard ? Il vous reste plus qu\'à supprimer cet équipement et recommencer !';
            }
          } else {
            echo 'Erreur : cet eqLogic n\'existe pas';
          }
        } else {
          echo 'Sauvegarder ou rafraichir la page pour afficher les infos';
        }
        ?>
      </div>

      <div class="col-sm-12">
        <label class="col-sm-1 control-label">{{Déclarer nuit }}</label>
        <?php
        if(init('id') != ''){
          $eqLogic = eqLogic::byId(init('id'));
          if(is_object($eqLogic)){
            $cmd = $eqLogic->getCmd(null, 'life_sign_nuit');
            if(is_object($cmd)){
              echo '<p>N\'importe où dans Jeedom, appelez cette commande : <i class="fas fa-code-branch"></i><b>  '. $cmd->getHumanName() . '</b><br>Où via l\'extérieur : <a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a></p>';
            } else {
              echo 'Hum... vous n\'auriez pas supprimé manuellement la commande "Déclarer nuit" par hasard ? Il vous reste plus qu\'à supprimer cet équipement et recommencer !';
            }
          } else {
            echo 'Erreur : cet eqLogic n\'existe pas';
          }
        } else {
          echo 'Sauvegarder ou rafraichir la page pour afficher les infos';
        }
        ?>
      </div>

    </div>

    <!-- TAB Capteurs Détection d'inactivité (qui s'appellera life_sign dans le code !) -->
    <div class="tab-pane" id="sensorlifesigntab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des capteurs indiquant une activité dans le logement. A l'issue du délai applicable (selon état du capteur et période de la journée), si aucun autre capteur n'a été déclenché, l'alerte sera lancée}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-heartbeat"></i> {{Capteurs d'activités}} <sup><i class="fas fa-question-circle tooltips" title="{{Capteurs déclenchant une alerte en cas d'inactivation pendant un certain délai}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorLifeSign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>

          <div id="div_life_sign"></div>

        </fieldset>
      </form>

    </div>

    <!-- TAB actions alerte -->
    <div class="tab-pane" id="actionalertlifesigntab">

      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des actions d'alerte pour avertir la personne puis prévenir les aidants. Vous pouvez choisir plusieurs actions et un délai d'attente pour chacune. Les actions en attente ne seront pas exécutées si un accusé de reception est reçu entre-temps, ou en cas d'annulation de l'alerte.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-child"></i> {{Actions alerte (pour alerter, je dois ?)}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions réalisées à l'échéance des délais de détection d'activité}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_alert_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_alert_life_sign"></div>

        </fieldset>
      </form>

      <br>

    </div>

    <!-- TAB AR alerte -->
    <div class="tab-pane" id="arlifesigntab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des actions d'accusé de réception de l'alerte par un aidant extérieur.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-check-square"></i> {{Commande à appeler depuis l'extérieur pour accuser réception de l'alerte}} <sup><i class="fas fa-question-circle tooltips" title="{{Réglages/Système/Configuration/Réseaux doit être correctement renseigné !}}"></i></sup>
          </legend>
          <div class="form-group">
            <label class="col-sm-1 control-label">{{URL }}</label>
            <div class="col-sm-6">
              <?php
              if(init('id') != ''){
                $eqLogic = eqLogic::byId(init('id'));
                if(is_object($eqLogic)){
                  $cmd = $eqLogic->getCmd(null, 'life_sign_ar');
                  if(is_object($cmd)){
                    echo '<p>N\'importe où dans Jeedom, appelez cette commande : <i class="fas fa-code-branch"></i><b>  '. $cmd->getHumanName() . '</b><br>Où via l\'extérieur : <a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a></p>';
                  } else {
                    echo 'Hum... vous n\'auriez pas supprimé manuellement la commande "Accuser Réception Alerte" par hasard ? Il vous reste plus qu\'à supprimer cet équipement et recommencer !';
                  }
                } else {
                  echo 'Erreur : cet eqLogic n\'existe pas';
                }
              } else {
                echo 'Sauvegarder ou rafraichir la page pour afficher les infos';
              }
              ?>
            </div>
          </div>
        </fieldset>
      </form>

      <br>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-stopwatch"></i> {{Comportement des actions d'alerte restantes, à la réception d'un accusé de réception}} <sup><i class="fas fa-question-circle tooltips" title="{{Que faut-il faire des actions d'alerte non encore exécutées lors de la réception d'un accusé de réception}}"></i></sup>
          </legend>
          <div class="col-sm-4 col-md-2">
            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="comportement_actions_alerte_reception_AR">
            <option value="remove">{{les annuler}}</option>
            <option value="delay">{{les reporter}}</option>
            <option value="keep">{{garder la programmation prévue}}</option>
            </select>
          </div>

          <div class="delay">
            <label class="col-sm-2 control-label">{{délai supplémentaire}} <sup><i class="fas fa-question-circle tooltips" title="{{Délai en minutes}}"></i></sup></label>
            <div class="col-sm-3 col-md-1">
              <input type="number" min="1" class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="delai_ar" placeholder="minutes" />
            </div>
          </div>

        </fieldset>
      </form>
      <br>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hands-helping"></i> {{Actions à la réception d'un accusé de réception}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions pour prévenir la personne qu'un aidant arrive, ou, pour les autres aidants, que l'alerte est prise en compte}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_ar_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_ar_life_sign"></div>

        </fieldset>
      </form>

    </div>

    <!-- TAB Action annulation -->
    <div class="tab-pane" id="cancellifesigntab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des actions d'annulation d'alerte.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hand-paper"></i> {{Actions pour arrêter l'alerte en cours}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions réalisées si un capteur d'activité est activé alors qu'une alerte était en cours.}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_cancel_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_cancel_life_sign"></div>

        </fieldset>
      </form>

    </div>

    <!-- TAB COMMANDES -->
    <div role="tabpanel" class="tab-pane" id="commandtab">
      <!-- <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/> -->
      <table id="table_cmd" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>


  </div> <!-- fin DIV contenant toutes les tab -->

</div>
</div>

<?php include_file('desktop', 'seniorcareinactivity', 'js', 'seniorcareinactivity');?>
<?php include_file('core', 'plugin.template', 'js');?>
