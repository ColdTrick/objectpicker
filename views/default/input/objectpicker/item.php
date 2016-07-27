<?php
/**
 * Object view in Object Picker
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['entity'] Object entity
 * @uses $vars['input_name'] Name of the returned data array
 * @uses $vars['show_delete'] boolean to toggle the availability of the delete action (default false)
 */

/* @var ElggEntity $entity */
$entity = elgg_extract('entity', $vars);
$input_name = elgg_extract('input_name', $vars);
$owner_guid = (int) elgg_extract('owner_guid', $vars);
$container_guid = (int) elgg_extract('container_guid', $vars);
$show_delete = (bool) elgg_extract('show_delete', $vars, false);

$show_group = true;
if (!empty($container_guid)) {
	$show_group = false;
}
if (!empty($owner_guid)) {
	// special cases (eg. static)
	$show_group = false;
}

$name = $entity->title;
if ($show_group) {
	if ($entity->getSubtype() === 'static') {
		$container = $entity->getOwnerEntity();
	} else {
		$container = $entity->getContainerEntity();
	}
	
	if ($container instanceof ElggGroup) {
		$name .= elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('river:ingroup', [$container->name]));
	}
}

$save_value = elgg_view('input/hidden', [
	'name' => "{$input_name}[]",
	'value' => $entity->getGUID(),
]);

$body = elgg_view_image_block('', $name . $save_value, [
	'image_alt' => elgg_view_icon('delete-alt', 'elgg-object-picker-remove link hidden'),
]);

$list_vars = [
	'data-guid' => $entity->getGUID(),
	'class' => (array) elgg_extract('class', $vars, []),
];

echo elgg_format_element('li', $list_vars, $body);
