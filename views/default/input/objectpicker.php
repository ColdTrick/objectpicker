<?php
/**
 * Object Picker.  Sends an array of object guids.
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['values'] Array of gruop guids for already selected objects or null
 * @uses $vars['limit'] Limit number of objects (default 0 = no limit)
 * @uses $vars['name'] Name of the returned data array (default "objects")
 * @uses $vars['handler'] Name of page handler used to power search (default "livesearch")
 * @uses $vars['subtype'] The subtype of the object (optional)
 * @uses $vars['container_guid'] The container_guid of the object (optional)
 *
 * Defaults to lazy load object lists in alphabetical order. User needs
 * to type two characters before seeing the object popup list.
 *
 * As objects are selected they move down to a "objects" box.
 * When this happens, a hidden input is created to return the GUID in the array with the form
 */

if (empty($vars['name'])) {
	$vars['name'] = 'objects';
}
$name = $vars['name'];
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

$guids = (array) elgg_extract('values', $vars, []);

$handler = elgg_extract('handler', $vars, 'livesearch');
$handler = htmlspecialchars($handler, ENT_QUOTES, 'UTF-8');

$limit = (int) elgg_extract('limit', $vars, 0);
$sortable = (bool) elgg_extract('sortable', $vars, false);
if ($sortable && ($limit === 1)) {
	// only one can not be sorted
	$sortable = false;
}

?>
<div class="elgg-object-picker ui-front" data-limit="<?php echo $limit ?>" data-name="<?php echo $name ?>" data-handler="<?php echo $handler ?>" data-subtype="<?php echo elgg_extract("subtype", $vars); ?>" data-container_guid="<?php echo (int) elgg_extract("container_guid", $vars); ?>">
	<input type="text" class="elgg-input-object-picker" size="30"/>
	<ul class="elgg-object-picker-list">
		<?php
		foreach ($guids as $guid) {
			$entity = get_entity($guid);
			if (empty($entity)) {
				continue;
			}

			echo elgg_view('input/objectpicker/item', [
				'entity' => $entity,
				'input_name' => $vars['name'],
				'show_delete' => true,
			]);
		}
		?>
	</ul>
</div>
<script>
require(['elgg/ObjectPicker', 'jquery.ui.autocomplete.html'], function (ObjectPicker) {
	ObjectPicker.setup('.elgg-object-picker[data-name="<?php echo $name ?>"]');

	<?php if ($sortable) { ?>
	$('.elgg-object-picker[data-name="<?php echo $name ?>"] .elgg-object-picker-list').sortable();
	<?php } ?>
});
</script>
