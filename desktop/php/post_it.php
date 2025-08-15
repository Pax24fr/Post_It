<?php
	if (!isConnect('admin')) {throw new Exception(__('401 - Accès non autorisé', __FILE__));}
	$plugin = plugin::byId('post_it');
	sendVarToJS('eqType', $plugin->getId());
	$eqLogics = eqLogic::byType($plugin->getId());
?>
<div class="row row-overflow">
	<div class="col-xs-3" style="text-align:right;">
		<i class="fas fa-sticky-note icon_yellow" style="font-size: 70px;"></i>
		<br><div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
			<i class="fas fa-wrench"></i><br><span>Configuration</span></div>
	</div>
	<div class="col-xs-9" style="right:0;">
		<h3>Documentation du plugin Post-it</h3>
		<br>Ce plugin ajoute un pense-bête au bout de la barre de navigation de Jeedom.
		<h4>Utilisation</h4>
		Cliquez sur l'icône dans la barre de navigation pour ouvrir/fermer le post-it.
		<br>Saisissez vos notes dans le champ texte. Elles sont sauvegardées automatiquement 2 secondes après la dernière saisie.
		<br><br>Les notes sont synchronisées via localStorage en temps réel entre les différents onglets/fenêtres d'un même appareil.
		<br>Elles sont également synchronisées entre différents appareils (par ex: mobile et PC) mais uniquement sur rafraîchissement de la page.
		<br><br>Elles seront effacées si vous désactivez/désinstallez le plugin.
		<h4>Options</h4>
		<ul>
			<li>1 ou 2 notes : Vous pouvez ajouter une seconde note pour des choses moins urgentes par exemple.</li>
			<li>Compteur : Affiche le nombre de caractères de la note.</li>
			<li>Bouton fermer : Permet de fermer le post-it en plus de la touche ESC ou du clic sur l'icône Post It.</li>
			<li>Taille max des notes : Taille maximale des notes en caractères pour ne pas surcharger votre interface.</li>
			<li>Texte des bouton d'onglets personnalisable.</li>
		</ul>
		<h4>Support</h4>
		En cas de problème, vérifiez votre connexion réseau ou consultez les logs du plugin.
		<br>Pour toute question, consultez la documentation officielle ou contactez le support.
	</div>
</div>
<script>
	$('.eqLogicAction[data-action=gotoPluginConf]').off('click').on('click', function() {
		jeeDialog.dialog({id: 'jee_modal',title: '{{Configuration du plugin}}',height: '85%',contentUrl: 'index.php?v=d&p=plugin&ajax=1&id=' + eqType});
		return;
	});
</script>
