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

    <li role="presentation"><a href="#capteursSdVtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-heartbeat"></i> {{Capteurs Signes de vie}}</a></li>
    <!-- <li role="presentation"><a href="#alertesSdVtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-heartbeat"></i> {{Alertes Signes de vie}}</a></li> -->

    <li role="presentation"><a href="#capteursConforttab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-spa"></i> {{Capteurs Confort}}</a></li>
    <!-- <li role="presentation"><a href="#alertesConforttab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-spa"></i> {{Alertes Confort}}</a></li> -->

    <li role="presentation"><a href="#capteursSecuritetab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-exclamation-triangle"></i> {{Capteurs Sécurité}}</a></li>
    <!-- <li role="presentation"><a href="#alertesSecuritetab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-exclamation-triangle"></i> {{Alertes Sécurité}}</a></li> -->

    <!--li role="presentation"><a href="#alertesPerteAutonomietab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Alertes Perte d'autonomie}}</a></li-->

    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>

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

    <!-- TAB Capteurs Signes de vie -->
    <div class="tab-pane" id="capteursSdVtab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-heartbeat"></i> {{Capteurs de signe de vie}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces capteurs déclancheront une alerte si aucun d'entre eux n'est activé pendant une certaine durée}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorLifeSign" style="margin-bottom:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>
          <div id="div_life_sign">
          </div>
        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-exclamation-circle"></i> {{Boutons d'alerte}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces capteurs déclancheront une alerte dès qu'ils seront activés}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorBtAlert" style="margin-bottom:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un bouton}}</a>
          </legend>
          <div id="div_alert_bt">

          </div>
        </fieldset>
      </form>
    </div>

    <!-- TAB Capteurs Confort -->
    <div class="tab-pane" id="capteursConforttab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-spa"></i> {{Capteurs confort}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces capteurs déclancheront une alerte si leur valeur sort des seuils paramétrés}}"></i></sup>
            <a class="btn btn-success btn-sm addSensorConfort" style="margin-bottom:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un capteur}}</a>
          </legend>
          <div id="div_confort">
          </div>
        </fieldset>
      </form>

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
