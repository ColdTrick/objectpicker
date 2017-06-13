<?php
/**
 * Label in Object Picker (shown in autocomplete dropdown)
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['entity'] Object entity
 * @uses $vars['text'] (optional) The text to show as the option (defaults to $entity->getDisplayName())
 */

/* @var ElggEntity $entity */
$entity = elgg_extract('entity', $vars);
$owner_guid = (int) elgg_extract('owner_guid', $vars);
$container_guid = (int) elgg_extract('container_guid', $vars);

$show_group = true;
if (!empty($container_guid)) {
	$show_group = false;
}
if (!empty($owner_guid)) {
	// special cases (eg. static)
	$show_group = false;
}

$name = elgg_extract('text', $vars, $entity->getDisplayName());
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

echo elgg_view_image_block('', $name, []);
