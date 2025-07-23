<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function post_it_install() {
    config::save('bouton_fermer', true, 'post_it');
    config::save('deux_notes', true, 'post_it');
    config::save('compteur', true, 'post_it');
    config::save('max_car', "9999", 'post_it');
	config::save('btn1', 'URGENT', 'post_it');
	config::save('btn2', 'Plus tard', 'post_it');
}

function post_it_update() {
}

function post_it_remove() {
    cache::delete('post_it');
    log::add('post_it', 'info', 'Désinstallation : Cache post_it supprimé');
}
?>