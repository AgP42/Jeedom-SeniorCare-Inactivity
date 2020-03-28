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
    <legend><i class="fas fa-table"></i> {{Personne dépendante}}</legend>
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
            <label class="col-sm-3 control-label">{{Nom personne de référence }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="trusted_person_name" placeholder="{{Nom de la personne de référence}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#trusted_person_name#</strong>}}</div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Téléphone personne de référence }}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="trusted_person_phone" placeholder="{{Numéro de téléphone de la personne de référence}}"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#trusted_person_phone#</strong>}}</div>
          </div>
        </fieldset>
      </form>

    </div>

    <!-- TAB Capteurs absences -->
    <div class="tab-pane" id="absencestab">
      <br/>
      <div class="alert alert-info">
        {{TODO : gérer la liaison avec le plugin Agenda pour saisir les absences prévues, et ajouter les boutons ou capteurs du logement à utiliser pour détecter la présence/absence de la personne de son logement}}
      </div>

      <?php
      try {
        $plugin = plugin::byId('calendar');
        if (is_object($plugin)) {
          ?>

          <legend><i class="fas fa-clock"></i> {{Utiliser le plugin Agenda pour la gestion des absences}}<sup><i class="fas fa-question-circle tooltips" title="{{Les plages d'absences sont à configurer directement avec le plugin Agenda. La programmation réalisée s'affichera ici}}"></i></sup></legend>
          <form class="form-horizontal">
            <fieldset>
              <div id="div_schedule"></div>
            </fieldset>
          </form>

          <?php
        }
      } catch (Exception $e) {

      }
      ?>


    </div>

    <!-- TAB Capteurs Détection d'inactivité (qui s'appellera life_sign dans le code !) -->

    <!-- TODO : ajouter des actions pour que l'aidant puisse prévenir la personne dépendante de la bonne prise en compte de l'alerte ? Beaucoup plus simple a faire en externe du plugin... a voir !! -->

    <div class="tab-pane" id="sensorlifesigntab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des capteurs indiquant une activité de la personne dépendante.
        Un premier niveau d'alerte permet de prévenir la personne dépendante d'une alerte imminente afin de la désactiver.
        Sans réaction de sa part, une alerte sera envoyée aux aidants.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-heartbeat"></i> {{Capteurs d'activités}} <sup><i class="fas fa-question-circle tooltips" title="{{Capteurs déclenchant une alerte en cas d'inactivation pendant un certain délai}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorLifeSign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>

          <div id="div_life_sign"></div>

<!--           <legend><i class="fas fa-stopwatch"></i> {{Délai par défaut avant avertissement d'inactivité}} <sup><i class="fas fa-question-circle tooltips" title="{{Délai au terme duquel une alerte se déclenchera si aucun capteur d'activités n'est activé. Cette valeur sera utilisée si vous ne définissez pas de valeur spécifique pour certains capteurs}}"></i></sup>
          </legend>

          <div class="form-group">
            <label class="col-sm-2 control-label">{{Délai en minutes}}</label>
            <div class="col-sm-1">
              <input type="number" min="0" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="life_sign_timer" />
            </div>
          </div> -->

        </fieldset>
      </form>

    </div>

    <!-- TAB actions alerte -->
    <div class="tab-pane" id="actionalertlifesigntab">

      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des actions d'alerte pour prévenir les aidants. Vous pouvez choisir plusieurs actions et un délai d'attente pour chacune. Les actions en attente ne seront pas exécutées si un accusé de reception est reçu entre-temps, ou en cas d'annulation de l'alerte.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-bomb"></i> {{Actions alerte vers les aidants (pour alerter, je dois ?)}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions réalisées à l'activation d'un bouton d'alerte par la personne dépendante}}"></i></sup>
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
        {{Onglet de configuration des actions d'accusé de réception de l'alerte par un aidant extérieur}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-check-square"></i> {{Commande à appeler depuis l'extérieur pour accuser réception de l'alerte}} <sup><i class="fas fa-question-circle tooltips" title="{{Réglages/Système/Configuration/Réseaux doit être correctement renseigné !}}"></i></sup>
          </legend>
          <div class="form-group">
            <label class="col-sm-1 control-label">{{URL }}</label>
            <div class="col-sm-6" id="div_cmd_api_AR">
              <?php
              if(init('id') != ''){
                $eqLogic = eqLogic::byId(init('id'));
                $cmd = $eqLogic->getCmd(null, 'life_sign_ar');
                echo '<a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a>';
              } else {
                echo 'Sauvegarder ou rafraichir la page pour afficher l\'URL';
              }
              ?>
            </div>
          </div>
        </fieldset>
      </form>

      <br>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hands-helping"></i> {{Actions à la réception d'un accusé de réception}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions pour prévenir la personne qu'un aidant arrive, ou prévenir les autres aidants que l'alerte est prise en compte}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_ar_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_ar_life_sign"></div>

        </fieldset>
      </form>

    </div>

    <!-- TAB Capteurs Bouton alerte -->
    <div class="tab-pane" id="cancellifesigntab">
      <br/>
      <div class="alert alert-info">
        {{Onglet de configuration des boutons et actions d'annulation d'alerte.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hand-paper"></i> {{Actions pour arrêter l'alerte vers les aidants (pour annuler l'alerte, je dois ?)}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions réalisées sur activation d'un bouton d'annulation d'alerte.
          Tag utilisable : #senior_name#.}}"></i></sup>
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
