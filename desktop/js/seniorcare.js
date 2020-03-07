
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

// permet de reorganiser les elements de la div en les cliquant/deplacant
$("#div_life_sign").sortable({axis: "y", cursor: "move", items: ".life_sign", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_alert_bt").sortable({axis: "y", cursor: "move", items: ".alert_bt", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_confort").sortable({axis: "y", cursor: "move", items: ".confort", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_security").sortable({axis: "y", cursor: "move", items: ".security", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_warning_life_sign").sortable({axis: "y", cursor: "move", items: ".action_warning_life_sign", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});



// le bouton "ajouter un capteur" de l'onglet signe de vie
$('.addSensorLifeSign').off('click').on('click', function () {
  addSensorLifeSign({});
});

// le bouton "ajouter un bt d'alerte" de l'onglet signe de vie
$('.addSensorBtAlert').off('click').on('click', function () {
  addSensorBtAlert({});
});

// le bouton "ajouter un capteur" de l'onglet confort
$('.addSensorConfort').off('click').on('click', function () {
  addSensorConfort({});
});

// le bouton "ajouter un capteur" de l'onglet security
$('.addSensorSecurity').off('click').on('click', function () {
  addSensorSecurity({});
});

// le bouton "ajouter un capteur" de l'onglet security
$('.addActionWarningLifeSign').off('click').on('click', function () {
  addActionWarningLifeSign({});
});


// tous les - qui permettent de supprimer la ligne
$("body").off('click','.bt_removeAction').on('click','.bt_removeAction',function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

// permet d'afficher la liste des cmd Jeedom pour choisir sa commande de type "info" (pas les actions donc)
$("body").off('click', '.listCmdInfoWindow').on('click', '.listCmdInfoWindow',function () {
  var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function (result) {
    el.value(result.human);
  });
});

// affiche les cmd jeedom de type action
$("body").off('click','.listCmdAction').on('click','.listCmdAction', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
    });

  });
});

$('body').off('focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on('focusout','.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
  var type = $(this).attr('data-type');
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
  });

});

// ajoute chaque ligne de capteur signe de vie à la demande
function addSensorLifeSign(_info) {
  var div = '<div class="life_sign">';
  div += '<div class="form-group ">';
  div += '<label class="col-sm-1 control-label">Capteur</label>';
  div += '<div class="col-sm-4">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="life_sign"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="expressionAttr form-control cmdInfo" data-l1key="cmd" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default listCmdInfoWindow roundedRight"><i class="fas fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '<label class="col-sm-2 control-label">{{Type de capteur }}</label>';
  div += '<div class="col-sm-2">';
  div += '<select class="expressionAttr eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="type_life_sign">';
  div += '<option value="other">Divers</option>';
  div += '<option value="frigo">Frigidaire</option>';
  div += '<option value="toilettes">Chasse d\'eau</option>';
  div += '<option value="lit">Mouvement lit</option>';
  div += '<option value="interupteur">Interupteur</option>';
  div += '<option value="detecteur_mvt">Détecteur de mouvement</option>';
  div += '</select>';

  div += '</div>';
  div += '</div>';
  div += '</div>';
  $('#div_life_sign').append(div);
  $('#div_life_sign .life_sign').last().setValues(_info, '.expressionAttr');
}

// ajoute chaque ligne de bt alerte à la demande
function addSensorBtAlert(_info) {
  var div = '<div class="alert_bt">';
  div += '<div class="form-group ">';
  div += '<label class="col-sm-1 control-label">Bouton</label>';
  div += '<div class="col-sm-4">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="alert_bt"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="expressionAttr form-control cmdInfo" data-l1key="cmd" />'; //comment on va recuperer les infos si elles s'appellent toutes cmd ?? todo later...
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default listCmdInfoWindow roundedRight"><i class="fas fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  $('#div_alert_bt').append(div);
  $('#div_alert_bt .alert_bt').last().setValues(_info, '.expressionAttr');
}

// ajoute chaque ligne de capteur confort à la demande
// tout ce qui a la class expressionAttr sera enregistré et retrouvable dans la class.php
function addSensorConfort(_info) {
  var div = '<div class="confort">';
    div += '<div class="form-group ">';

      div += '<label class="col-sm-1 control-label">{{Nom (unique)}}</label>';
      div += '<div class="col-sm-2">';
      div += '<div class="input-group">';
        div += '<span class="input-group-btn">';
        div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="confort"><i class="fas fa-minus-circle"></i></a>';
        div += '</span>';
        div += '<input class="expressionAttr form-control cmdInfo" data-l1key="name" />'; // dans la class ['name']
      div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-1 control-label">Capteur</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<input class="expressionAttr roundedLeft form-control cmdInfo" data-l1key="cmd" />'; // dans la class on retrouvera le resultat avec un ['cmd'] sous forme #10# qui represente l'id de la cmd referencé
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default listCmdInfoWindow roundedRight"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-1 control-label">{{Type de capteur }}</label>';
      div += '<div class="col-sm-1">';
        div += '<select class="expressionAttr eqLogicAttr form-control tooltips" data-l1key="sensor_confort_type">'; // dans la class : ['sensor_confort_type']
        div += '<option value="temperature">Température</option>';
        div += '<option value="humidite">Humidité</option>';
        div += '<option value="co2">CO2</option>';
        div += '<option value="pollution">Pollution</option>';
        div += '<option value="other">Divers</option>';
        div += '</select>';
      div += '</div>';

      div += '<label class="col-sm-1 control-label">{{Seuil bas}}</label>';
      div += '<div class="col-sm-1">';
        div += '<input class="expressionAttr form-control cmdInfo" data-l1key="seuilBas" />'; // dans la class ['seuilBas']
      div += '</div>';
      div += '<label class="col-sm-1 control-label">{{Seuil haut}}</label>';
      div += '<div class="col-sm-1">';
        div += '<input class="expressionAttr form-control cmdInfo" data-l1key="seuilHaut"/>'; // dans la class ['seuilHaut']
      div += '</div>';

    div += '</div>';
  div += '</div>';
  $('#div_confort').append(div);
  $('#div_confort .confort').last().setValues(_info, '.expressionAttr');
}

// ajoute chaque ligne de capteur sécurite à la demande
function addSensorSecurity(_info) {
  var div = '<div class="security">';
  div += '<div class="form-group ">';
  div += '<label class="col-sm-1 control-label">Capteur</label>';
  div += '<div class="col-sm-4">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="security"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="expressionAttr form-control cmdInfo" data-l1key="cmd" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default listCmdInfoWindow roundedRight"><i class="fas fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '<label class="col-sm-2 control-label">{{Type de capteur }}</label>';
  div += '<div class="col-sm-2">';
  div += '<select class="expressionAttr eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="type_security">';
  div += '<option value="CO2">CO2</option>';
  div += '<option value="smoke">Fumées</option>';
  div += '<option value="gaz">gaz</option>';
  div += '<option value="other">Divers</option>';
  div += '</select>';

  div += '</div>';
  div += '</div>';
  div += '</div>';
  $('#div_security').append(div);
  $('#div_security .security').last().setValues(_info, '.expressionAttr');
}

// ajoute chaque ligne de capteur sécurite à la demande
function addActionWarningLifeSign(_info) {
  var div = '<div class="action_warning_life_sign">';
  div += '<div class="form-group ">';
  div += '<label class="col-sm-1 control-label">Action</label>';
  div += '<div class="col-sm-4">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="action_warning_life_sign"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';

  div += '<input class="expressionAttr form-control cmdAction" data-l1key="cmd" data-type="action_warning_life_sign" />';

  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default listCmdAction roundedRight" data-type="action_warning_life_sign" ><i class="fas fa-list-alt"></i></a>';

  div += '</span>';
  div += '</div>';
  div += '</div>';

  div += '<div class="col-sm-7 actionOptions">';
  div += jeedom.cmd.displayActionOption(init(_info.cmd, ''), _info.options);

  div += '</div>';
  div += '</div>';
  $('#div_action_warning_life_sign').append(div);
  $('#div_action_warning_life_sign .action_warning_life_sign').last().setValues(_info, '.expressionAttr');
}

// Fct core permettant de sauvegarder
function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }
  _eqLogic.configuration.life_sign = $('#div_life_sign .life_sign').getValues('.expressionAttr');
  _eqLogic.configuration.alert_bt = $('#div_alert_bt .alert_bt').getValues('.expressionAttr');
  _eqLogic.configuration.confort = $('#div_confort .confort').getValues('.expressionAttr');
  _eqLogic.configuration.security = $('#div_security .security').getValues('.expressionAttr');
  _eqLogic.configuration.action_warning_life_sign = $('#div_action_warning_life_sign .action_warning_life_sign').getValues('.expressionAttr');

  return _eqLogic;
}

// fct core permettant de restituer les cmd declarées
function printEqLogic(_eqLogic) {

  $('#div_life_sign').empty();
  $('#div_alert_bt').empty();
  $('#div_confort').empty();
  $('#div_security').empty();
  $('#div_action_warning_life_sign').empty();

  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.life_sign)) {
      for (var i in _eqLogic.configuration.life_sign) {
        addSensorLifeSign(_eqLogic.configuration.life_sign[i]);
      }
    }
    if (isset(_eqLogic.configuration.alert_bt)) {
      for (var i in _eqLogic.configuration.alert_bt) {
        addSensorBtAlert(_eqLogic.configuration.alert_bt[i]);
      }
    }
    if (isset(_eqLogic.configuration.confort)) {
      for (var i in _eqLogic.configuration.confort) {
        addSensorConfort(_eqLogic.configuration.confort[i]);
      }
    }
    if (isset(_eqLogic.configuration.security)) {
      for (var i in _eqLogic.configuration.security) {
        addSensorSecurity(_eqLogic.configuration.security[i]);
      }
    }
    if (isset(_eqLogic.configuration.action_warning_life_sign)) {
      for (var i in _eqLogic.configuration.action_warning_life_sign) {
        addActionWarningLifeSign(_eqLogic.configuration.action_warning_life_sign[i]);
      }
    }
  }
}


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
