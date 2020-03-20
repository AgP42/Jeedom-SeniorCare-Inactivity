<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('seniorcare');
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

    <li role="presentation"><a href="#lifesigntab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-heartbeat"></i> {{Détection d'inactivité}}</a></li>

    <li role="presentation"><a href="#alertbttab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-toggle-on"></i> {{Bouton d'alerte}}</a></li>

    <li role="presentation"><a href="#conforttab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-spa"></i> {{Confort}}</a></li>

    <li role="presentation"><a href="#securitytab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-exclamation-triangle"></i> {{Sécurité}}</a></li>

    <li role="presentation"><a href="#alertesPerteAutonomietab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-brain"></i> {{Dérive comportementale}}</a></li>

    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Avancé - Commandes Jeedom}}</a></li>

  </ul>

  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">

    <!-- TAB GENERAL -->
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de la personne dépendante}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de la personne dépendante}}"/>
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
  	   <!--div class="form-group">
                  <label class="col-sm-3 control-label">{{Catégorie}}</label>
                  <div class="col-sm-9">
                   <?php
                    //  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    //  echo '<label class="checkbox-inline">';
                    //  echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    //  echo '</label>';
                    //  }
                    ?>
                 </div>
             </div-->
        	<div class="form-group">
        		<label class="col-sm-3 control-label"></label>
        		<div class="col-sm-9">
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
        		</div>
        	</div>
         <!--div class="form-group">
          <label class="col-sm-3 control-label">{{template param 1}}</label>
          <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="city" placeholder="param1"/>
          </div>
      </div-->
        </fieldset>
      </form>
    </div>

    <!-- TAB Capteurs absences -->
    <div class="tab-pane" id="absencestab">
      <br/>
      <div class="alert alert-info">
        {{TODO : gérer la liaison avec le plugin Agenda pour saisir les absences prévues, et ajouter les boutons ou capteurs du logement à utiliser pour détecter la présence/absence de la personne de son logement}}
      </div>

    </div>

    <!-- TAB Capteurs Détection d'inactivité (qui s'appellera life_sign dans le code !) -->

    <!-- TODO : ajouter des actions pour que l'aidant puisse prévenir la personne dépendante de la bonne prise en compte de l'alerte ? Beaucoup plus simple a faire en externe du plugin... a voir !! -->

    <div class="tab-pane" id="lifesigntab">
      <br/>
      <div class="alert alert-info">
        {{Cet onglet permet de configurer les capteurs indiquant au système une activité de la personne dépendante. Un premier niveau d'alerte permet à la personne dépendante d'être avertie d'une alerte imminente pour pouvoir la désactiver. Sans réaction de sa part, une alerte sera envoyée aux aidants.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-heartbeat"></i> {{Capteurs d'activités}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces capteurs déclencheront une alerte
            si aucun d'entre eux n'est activé pendant un certain délai}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorLifeSign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>

          <div id="div_life_sign"></div>

          <legend><i class="fas fa-stopwatch"></i> {{Délai avant avertissement d'inactivité}} <sup><i class="fas fa-question-circle tooltips" title="{{Délai au terme duquel une alerte se déclenchera si aucun des capteurs d'activités n'est activé. TODO - A paufiner selon jour/nuit, etc.}}"></i></sup>
          </legend>

          <div class="form-group">
            <label class="col-sm-2 control-label">{{Délai en minutes}}</label>
            <div class="col-sm-1">
              <input type="number" min="0" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="life_sign_timer" />
            </div>
          </div>

        </fieldset>
      </form>

      <br>

      <div class="row">
        <div class="col-lg-6">
          <form class="form-horizontal">
            <fieldset>
              <legend><i class="fas fa-user-clock"></i> {{Actions avertissement de détection d'inactivité - pour la personne dépendante}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées dès que le système ne détectera aucune activité depuis le délai consideré. La personne dépendante disposera d'un certain temps pour désactiver l'alerte avant qu'elle ne soit transmise aux aidants}}"></i></sup>
                <a class="btn btn-success btn-sm addAction" data-type="action_warning_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
              </legend>
              <div id="div_action_warning_life_sign"></div>


              <label class="col-lg-4 control-label">{{Délai avant de prévenir les aidants}} <sup><i class="fas fa-question-circle tooltips" title="{{Délai pendant laquelle la personne dépendante peut désactiver l'alerte (en activant n'importe quel capteur d'activité) avant qu'elle ne soit transmise aux aidants}}"></i></sup></label>
              <div class="col-lg-2">
                <input type="number" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="warning_life_sign_timer" title="{{}}"/>
              </div>
            </fieldset>
          </form>
        </div>

        <div class="col-lg-6">
          <form class="form-horizontal">
            <fieldset>
              <legend><i class="fas fa-user-slash"></i> {{Actions de désactivation des avertissements}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées lorsqu'un des capteurs d'activité est déclenché alors que les actions d'avertissement ont été précédemment activées. //TODO préciser dans la doc que ces actions ne sont pas activées lorsque l'on active les alertes, mais uniquement si signe de vie pendant la phase de warning}}"></i></sup>
                <a class="btn btn-danger btn-sm addAction" data-type="action_desactivate_warning_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
              </legend>
              <div id="div_action_desactivate_warning_life_sign"></div>

            </fieldset>
          </form>
        </div>
      </div>

      <br>

      <div class="row">
        <div class="col-lg-6">
          <form class="form-horizontal">
            <fieldset>
              <legend><i class="fas fa-bell"></i> {{Actions alerte de détection d'inactivité - pour les aidants}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées à l'échéance du délai de désactivation de l'alerte par la personne dépendante.}}"></i></sup>
                <a class="btn btn-success btn-sm addAction" data-type="action_alert_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
              </legend>
              <div id="div_action_alert_life_sign"></div>

            </fieldset>
          </form>
        </div>

        <div class="col-lg-6">
          <form class="form-horizontal">
            <fieldset>
              <legend><i class="fas fa-bell-slash"></i> {{Actions de désactivation des alertes}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées lorsqu'un des capteurs d'activité est déclenché alors que les actions d'alerte vers les aidants ont été précédemment activées.}}"></i></sup>
                <a class="btn btn-danger btn-sm addAction" data-type="action_desactivate_alert_life_sign" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
              </legend>
              <div id="div_action_desactivate_alert_life_sign"></div>

            </fieldset>
          </form>
        </div>
      </div>

    </div>

    <!-- TAB Capteurs Bouton alerte immédiate -->
    <div class="tab-pane" id="alertbttab">
      <br/>
      <div class="alert alert-info">
        {{Cet onglet permet de configurer un ou plusieurs boutons d'alerte immédiate pour envoyer une alerte aux aidants.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-toggle-on"></i> {{Boutons d'alerte immédiate}} <sup><i class="fas fa-question-circle tooltips" title="{{Bouton à porter par la personne pour déclencher une alerte immédiate par un simple appui}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorBtAlert" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un bouton}}</a>
          </legend>
          <div id="div_alert_bt"></div>
        </fieldset>
      </form>

      <br>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-bomb"></i> {{Actions alerte immédiate vers les aidants}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées à l'activation d'un des boutons d'alerte par la personne dépendante. Pour les messages, vous pouvez utiliser le tag #nom_personne#.}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_alert_bt" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_alert_bt"></div>

        </fieldset>
      </form>

    </div>

    <!-- TAB Capteurs Confort -->
    <div class="tab-pane" id="conforttab">
      <br/>
      <div class="alert alert-info">
        {{Cet onglet permet de configurer les capteurs du confort du logement de la personne dépendante. Vous pouvez définir les actions à réaliser lors des dépassements de seuils définis.}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-spa"></i> {{Capteurs confort}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces capteurs déclencheront une alerte si leur valeur sort des seuils définis. Des seuils vides permettent de suivre les courbes sans générer d'alertes.}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorConfort" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>
          <div id="div_confort"></div>
        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-bomb"></i> {{Action avertissement}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées simultanément dés que le système détectera qu'un capteur de confort sort des seuils prédéfinis. Vous pouvez définir des actions pour la personne dépendante et/ou pour les aidants. Vous pouvez appeler un scenario pour des actions plus complexes. Pour les messages, vous pouvez utiliser les tag suivants : #nom_personne#, #nom_capteur#, #type_capteur#, #valeur#, #seuil_bas#, #seuil_haut# et #unite#. Voir la doc pour plus de détails.}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_warning_confort" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_warning_confort"></div>

        </fieldset>
      </form>

    </div>

    <!-- TAB Capteurs Sécurité -->
    <div class="tab-pane" id="securitytab">
      <br/>
      <div class="alert alert-info">
        {{Cet onglet permet de configurer les capteurs de sécurité du logement de la personne dépendante ainsi que les actions à réaliser en cas de déclenchement}}
      </div>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-exclamation-triangle"></i> {{Capteurs Sécurité}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces capteurs déclencheront une alerte de sécurité immédiate à chaque déclenchement}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorSecurity" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>
          <div id="div_security"></div>
        </fieldset>
      </form>

      <br>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-bomb"></i> {{Actions alerte immédiate}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces actions seront réalisées à l'activation d'un des capteur de sécurité. Pour les messages, vous pouvez utiliser le tag #nom_personne#.}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_security" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_security"></div>

        </fieldset>

    </div>

    <!-- TAB Perte Autonomie -->
    <div class="tab-pane" id="alertesPerteAutonomietab">
      <br/>
      <div class="alert alert-info">
        {{TODO : en attente de la liste des critères à prendre en compte pour gèrer la perte d'autonomie}}
      </div>

    </div>

    <!-- TAB COMMANDES -->
    <div role="tabpanel" class="tab-pane" id="commandtab">
      <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
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

<?php include_file('desktop', 'seniorcare', 'js', 'seniorcare');?>
<?php include_file('core', 'plugin.template', 'js');?>
