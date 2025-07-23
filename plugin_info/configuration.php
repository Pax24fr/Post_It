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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
 }

 $configs = [
	'bouton_fermer' => (bool)config::byKey('bouton_fermer', 'post_it', true),
	'deux_notes' => (bool)config::byKey('deux_notes', 'post_it', true),
	'compteur' => (bool)config::byKey('compteur', 'post_it', true),
	'max_car' => intval(config::byKey('max_car', 'post_it', 9999)),
	'btn1' => config::byKey('btn1', 'post_it', 'URGENT'),
	'btn2' => config::byKey('btn2', 'post_it', 'Plus tard')
];
?>
<form class="form-horizontal">
    <fieldset>
        <div class="col-lg-6">
            <?php foreach ([
                'bouton_fermer' => 'Afficher le bouton Fermer',
                'deux_notes' => 'Activer la 2ème note',
                'compteur' => 'Afficher le compteur de caractères'
            ] as $key => $label): ?>
                <div class="form-group">
                    <label class="col-md-5 control-label"><?= $label ?></label>
                    <div class="col-md-4">
                        <input type="checkbox" class="configKey" data-l1key="<?= $key ?>" <?= $configs[$key] ? 'checked' : '' ?>>
                    </div>
                </div>
            <?php endforeach; ?>
			<!-- Champs select pour les caractères max -->
			<div class="form-group">
				<label class="col-md-5 control-label">Caractères max par post It
				<sup><i class="fas fa-question-circle tooltips" title="4 999: {{5ko, 2 pages A4, très léger}}<br>9 999: {{10ko, 5 pages A4, assez léger (défaut)}}<br>49 999: {{50ko, 20 pages A4, peut provoquer des ralentissements lors de l'enregistrement}}<br>99 999: {{100ko, 50 pages A4, peut provoquer de gros ralentissements lors de l'enregistrement}}"></i></sup>
			</label>
			<div class="col-md-4">
					<select class="configKey form-control" data-l1key="max_car">
						<option value="4999"<?= $configs['max_car'] == 4999 ? ' selected' : '' ?>>4 999 (très léger)</option>
						<option value="9999"<?= $configs['max_car'] == 9999 ? ' selected' : '' ?>>9 999 (défaut)</option>
						<option value="49999"<?= $configs['max_car'] == 49999 ? ' selected' : '' ?>>49 999 (lent)</option>
						<option value="99999"<?= $configs['max_car'] == 99999 ? ' selected' : '' ?>>99 999 (très lent)</option>
					</select>
				</div>
			</div>
            <!-- Champs texte pour les boutons -->
            <div class="form-group">
                <label class="col-md-5 control-label">Texte bouton1</label>
                <div class="col-md-4">
                    <input type="text" class="configKey form-control" data-l1key="btn1" value="<?= $configs['btn1'] ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-5 control-label">Texte bouton2</label>
                <div class="col-md-4">
                    <input type="text" class="configKey form-control" data-l1key="btn2" value="<?= $configs['btn2'] ?>">
                </div>
            </div>
        </div>

		<style>
			#preview-container {
				position: relative; width: 220px; padding: 6px; margin-top: 10px; font-family: Roboto, sans-serif;
				background: #fffad0; 
				border: 2px solid #ddd; 
				box-shadow: 0 5px 10px rgba(0,0,0,0.1);
			}
			#preview-area {
				width: 100%; height: 138px; margin-top: 8px; margin-bottom: 5px;
				background: transparent !important;
			}
			#preview-counter {position: absolute; top: 0px; right: 4px; font-size: 9px;}
			#preview-close {display: <?= $configs['bouton_fermer'] ? 'unset' : 'none' ?>;}
			.preview-btn {display: <?= $configs['deux_notes'] ? 'initial' : 'none' ?>;}
		</style>        
        <!-- Aperçu dynamique -->
        <div class="col-lg-6">
            <div class="form-group preview">
                <label class="col-md-2 control-label">{{Aperçu}}</label>
                <div class="col-md-6">
                    <div id="preview-container">
                        <div id="preview-counter" style="display: <?= $configs['compteur'] ? 'block' : 'none' ?>">852/<?= $configs['max_car'] ?></div>
                        <textarea id="preview-area">Pour le texte des boutons vérifiez que ça ne saute pas une ligne ! Un mot de 8 à 12 car. est correct. En enlevant le bouton X vous gagnez de la place (on peut fermer la note avec ESC ou clic sur icône).</textarea>
                        <div class="preview-btns">
                            <button id="preview-close" class="btn btn-xs btn-success">X</button>
                            <button id="preview-btn1" class="preview-btn btn btn-xs btn-success"><?= $configs['btn1'] ?></button>
                            <button id="preview-btn2" class="preview-btn btn btn-xs"><?= $configs['btn2'] ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
</form>
?>
<script>
$(function() {
    $('.configKey').on('change input', function() {
        const key = $(this).data('l1key');
        const value = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
        switch(key) {
            case 'bouton_fermer':
                $('#preview-close').toggle(value);
                break;
            case 'deux_notes':
				$('.preview-btn').toggle(value);
                break;
            case 'compteur':
                $('#preview-counter').toggle(value);
                break;
            case 'btn1':
                $('#preview-btn1').text(value);
                break;
            case 'btn2':
                $('#preview-btn2').text(value);
                break;
        }
    });
});
</script>