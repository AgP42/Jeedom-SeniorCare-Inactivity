
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
$("#div_action_alert_life_sign").sortable({axis: "y", cursor: "move", items: ".action_alert_life_sign", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_ar_life_sign").sortable({axis: "y", cursor: "move", items: ".action_ar_life_sign", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_cancel_life_sign").sortable({axis: "y", cursor: "move", items: ".action_cancel_life_sign", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

// le bouton "ajouter un capteur" de l'onglet détection d'inactivité
$('.addSensorLifeSign').off('click').on('click', function () {
  addSensorLifeSign({});
});

// tous les boutons d'action regroupés !
$('.addAction').off('click').on('click', function () {
  addAction({}, $(this).attr('data-type'));
});


// tous les - qui permettent de supprimer la ligne
$("body").off('click','.bt_removeAction').on('click','.bt_removeAction',function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

// permet d'afficher la liste des cmd Jeedom pour choisir sa commande de type "info" (pas les actions donc)
// TODO ce morceau de code est un copier/coller du plugin thermostat, a voir s'il n'y a pas des trucs inutiles là-dedans
$("body").off('click', '.listCmdInfoWindow').on('click', '.listCmdInfoWindow',function () {
  var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function (result) {
    el.value(result.human);
  });
});

// affiche les cmd jeedom de type action
// TODO ce morceau de code est un copier/coller du plugin thermostat, a voir s'il n'y a pas des trucs inutiles là-dedans
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

// copier/coller du core (cmd.configure.php), permet de choisir la liste des actions (scenario, attendre, ...)
$("body").undelegate(".listAction", 'click').delegate(".listAction", 'click', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

// TODO ce morceau de code est un copier/coller du plugin thermostat, a voir s'il n'y a pas des trucs inutiles là-edans
$('body').off('focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on('focusout','.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
  var type = $(this).attr('data-type');
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
  });

});

//////////////// Les fonctions CAPTEURS /////////////////////////////////

// ajoute chaque ligne de CAPTEUR de détection d'inactivité, à la demande
function addSensorLifeSign(_info) {
  var div = '<div class="life_sign">';
    div += '<div class="form-group ">';

      div += '<label class="col-sm-1 control-label">{{Nom}}</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="life_sign" title="{{Supprimer le capteur}}""><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="name" title="{{Le nom doit être unique}}"/>'; // dans la class ['name']
        div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-1 control-label">Capteur</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<input class="expressionAttr roundedLeft form-control cmdInfo" data-l1key="cmd" />'; // dans la class on retrouvera le resultat avec un ['cmd'] sous forme #10# qui represente l'id de la cmd referencé
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default listCmdInfoWindow roundedRight" title="{{Selectionner le capteur}}"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-2 control-label">{{Délai avant alerte (min)}} <sup><i class="fas fa-question-circle tooltips" title="{{Saisir le délai d\'inactivité acceptable après déclenchement de ce capteur. A l\'issue de ce délai, si aucun autre capteur n\'a été déclenché, l\'alerte sera lancée}}"></i></sup></label>';
      div += '<div class="col-sm-1">';
        div += '<input type="number" class="expressionAttr form-control cmdInfo" data-l1key="life_sign_timer"/>';
      div += '</div>';

/*      div += '<label class="col-sm-1 control-label">{{Type de capteur }}</label>';
      div += '<div class="col-sm-1">';
        div += '<select class="expressionAttr eqLogicAttr form-control tooltips" data-l1key="sensor_life_sign_type">';
        div += '<option value="other">Divers</option>';
        div += '<option value="frigo">Frigidaire</option>';
        div += '<option value="toilettes">Chasse d\'eau</option>';
        div += '<option value="lit">Présence lit</option>';
        div += '<option value="interrupteur">Interrupteur</option>';
        div += '<option value="detecteur_mvt">Détecteur de mouvement</option>';
        div += '</select>';
      div += '</div>';*/

    div += '</div>';
  div += '</div>';
  $('#div_life_sign').append(div);
  $('#div_life_sign .life_sign').last().setValues(_info, '.expressionAttr');
}

//////////////// Les fonctions ACTIONS /////////////////////////////////

// fonction générique pour ajouter chaque ligne d'action.
// _type peut etre 'action_alert_life_sign', 'action_ar_life_sign', 'action_cancel_life_sign'
function addAction(_action, _type) {
  var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';

      if(_type == 'action_alert_life_sign'){ // pour les actions d'alertes,, on ajoute un label et un timer
        div += '<label class="col-sm-1 control-label">{{Label}} <sup><i class="fas fa-question-circle tooltips" title="{{Renseigner un label si vous voulez lier des actions de désactivations à cette action}}"></i></sup></label>';
        div += '<div class="col-sm-1">';
          div += '<input type="text" class="expressionAttr form-control cmdInfo" data-l1key="action_label"/>';
        div += '</div>';

        div += '<label class="col-sm-1 control-label">{{Délai avant exécution (min)}} <sup><i class="fas fa-question-circle tooltips" title="{{Le délai doit être donné par rapport au déclenchement de l\'alerte initiale et non par rapport à l\'action précédente. Ne pas remplir ou 0 pour déclenchement immédiat}}"></i></sup></label>';
        div += '<div class="col-sm-1">';
          div += '<input type="number" class="expressionAttr form-control cmdInfo" data-l1key="action_timer"/>';
        div += '</div>';
      } else { // pour les actions à la reception d'1 AR ou d'annulation d'alerte, on ajoute le label de l'action d'alerte à lier
        div += '<label class="col-sm-2 control-label">{{Label action de référence}} <sup><i class="fas fa-question-circle tooltips" title="{{Renseigner le label de l\'action de référence. Cette action ne sera exécutée que si l\'action de référence a été précédemment exécutée. }}"></i></sup></label>';
        div += '<div class="col-sm-1">';
          div += '<input type="text" class="expressionAttr form-control cmdInfo" data-l1key="action_label_liee"/>';
        div += '</div>';
      }

      div += '<label class="col-sm-1 control-label">Action</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
            div += '<a class="btn btn-default listCmdAction roundedRight" data-type="' + _type + '" title="{{Sélectionner une commande}}"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

      div += '<div class="col-sm-5 actionOptions">';
        div += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
      div += '</div>';

    div += '</div>';
  div += '</div>';

  $('#div_' + _type).append(div);
  $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
}

// Fct core permettant de sauvegarder
function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }
  _eqLogic.configuration.life_sign = $('#div_life_sign .life_sign').getValues('.expressionAttr');
  _eqLogic.configuration.action_alert_life_sign = $('#div_action_alert_life_sign .action_alert_life_sign').getValues('.expressionAttr');
  _eqLogic.configuration.action_ar_life_sign = $('#div_action_ar_life_sign .action_ar_life_sign').getValues('.expressionAttr');
  _eqLogic.configuration.action_cancel_life_sign = $('#div_action_cancel_life_sign .action_cancel_life_sign').getValues('.expressionAttr');

  return _eqLogic;
}

// fct core permettant de restituer les cmd declarées
function printEqLogic(_eqLogic) {

  $('#div_life_sign').empty();
  $('#div_action_alert_life_sign').empty();
  $('#div_action_ar_life_sign').empty();
  $('#div_action_cancel_life_sign').empty();

  printScheduling(_eqLogic); // va chercher les infos du plugin agenda pour les afficher dans l'onglet presence/absence

  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.life_sign)) {
      for (var i in _eqLogic.configuration.life_sign) {
        addSensorLifeSign(_eqLogic.configuration.life_sign[i]);
      }
    }
    if (isset(_eqLogic.configuration.action_alert_life_sign)) {
      for (var i in _eqLogic.configuration.action_alert_life_sign) {
        addAction(_eqLogic.configuration.action_alert_life_sign[i], 'action_alert_life_sign');
      }
    }
    if (isset(_eqLogic.configuration.action_ar_life_sign)) {
      for (var i in _eqLogic.configuration.action_ar_life_sign) {
        addAction(_eqLogic.configuration.action_ar_life_sign[i], 'action_ar_life_sign');
      }
    }
    if (isset(_eqLogic.configuration.action_cancel_life_sign)) {
      for (var i in _eqLogic.configuration.action_cancel_life_sign) {
        addAction(_eqLogic.configuration.action_cancel_life_sign[i], 'action_cancel_life_sign');
      }
    }
  }
}

function printScheduling(_eqLogic){
  $.ajax({
    type: 'POST',
    url: 'plugins/seniorcareinactivity/core/ajax/seniorcareinactivity.ajax.php',
    data: {
      action: 'getLinkCalendar',
      id: _eqLogic.id,
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_schedule').empty();
      if(data.result.length == 0){
        $('#div_schedule').append("<center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez encore aucune programmation. Veuillez cliquer <a href='index.php?v=d&m=calendar&p=calendar'>ici</a> pour programmer votre thermostat à l'aide du plugin agenda}}</span></center>");
      }else{
        var html = '<legend>{{Liste des programmations du plugin Agenda liées au Thermostat}}</legend>';
        for (var i in data.result) {
          var color = init(data.result[i].cmd_param.color, '#2980b9');
          if(data.result[i].cmd_param.transparent == 1){
            color = 'transparent';
          }
          html += '<span class="label label-info cursor" style="font-size:1.2em;background-color : ' + color + ';color : ' + init(data.result[i].cmd_param.text_color, 'black') + '">';
          html += '<a href="index.php?v=d&m=calendar&p=calendar&id='+data.result[i].eqLogic_id+'&event_id='+data.result[i].id+'" style="color : ' + init(data.result[i].cmd_param.text_color, 'black') + '">'

          if (data.result[i].cmd_param.eventName != '') {
            html += data.result[i].cmd_param.icon + ' ' + data.result[i].cmd_param.eventName;
          } else {
            html += data.result[i].cmd_param.icon + ' ' + data.result[i].cmd_param.name;
          }
          html += '</a></span><br\><br\>';
        }
        $('#div_schedule').empty().append(html);
      }
    }
  });

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
