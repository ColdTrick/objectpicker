<?php

/**
 * Init function for this plugin
 *
 * @return void
 */
function objectpicker_init() {
	elgg_register_plugin_hook_handler('route', 'livesearch', '\ColdTrick\ObjectPicker\Router::livesearch');
}

// register default elgg events
elgg_register_event_handler('init', 'system', 'objectpicker_init');
