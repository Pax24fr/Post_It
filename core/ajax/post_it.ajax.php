<?php
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (init('action') == 'loadPostIt') {
		ajax::success([
			'note1' => cache::byKey('post_it1')->getValue(''),
			'note2' => cache::byKey('post_it2')->getValue('')
		]);
	}

	if (init('action') == 'savePostIt') {
		$noteId = init('noteId'); $notes = init('notes');
		$max_car = (int)config::byKey('max_car', 'post_it', 9999);
		if (mb_strlen($notes) > $max_car) ajax::error('La note dépasse la taille maximale autorisée ('.$max_car.' caractères)');
		cache::set('post_it'.$noteId, $notes, 0);
		ajax::success();
	}

	if (init('action') == 'getConfig') {
		ajax::success([
			'bouton_fermer' => (bool)config::byKey('bouton_fermer', 'post_it', true),
			'deux_notes' => (bool)config::byKey('deux_notes', 'post_it', true),
			'compteur' => (bool)config::byKey('compteur', 'post_it', true),
			'max_car' => (int)config::byKey('max_car', 'post_it', 9999),
			'btn1' => config::byKey('btn1', 'post_it', 'URGENT'),
			'btn2' => config::byKey('btn2', 'post_it', 'Plus tard')
		]);
	}
	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
} catch (Exception $e) {
	log::add('post_it', 'error', 'Exception capturée : ' . $e->getMessage() . ' - Code : ' . $e->getCode());
	ajax::error(displayException($e), $e->getCode());
}
?>