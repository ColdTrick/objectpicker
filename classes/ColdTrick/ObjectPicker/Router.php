<?php

namespace ColdTrick\ObjectPicker;

class Router {
	
	/**
	 * listen to the livesearch in order to provide the objects picker
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void
	 */
	public static function livesearch($hook, $type, $return_value, $params) {
		
		// only return results to logged in users.
		$user = elgg_get_logged_in_user_entity();
		if (empty($user)) {
			return;
		}
		
		$q = get_input('term', get_input('q'));
		if (empty($q)) {
			return;
		}
		
		$input_name = get_input('name', 'objects');
		
		$q = sanitise_string($q);
		
		// replace mysql vars with escaped strings
		$q = str_replace(['_', '%'], ['\_', '\%'], $q);
		
		$match_on = get_input('match_on', 'all');
		
		if (!is_array($match_on)) {
			$match_on = [$match_on];
		}
		
		// only take over groups search
		if (count($match_on) > 1 || !in_array('objects', $match_on)) {
			return;
		}
		
		$owner_guid = ELGG_ENTITIES_ANY_VALUE;
		if (get_input('match_owner', false)) {
			$owner_guid = $user->getGUID();
		}
		
		$subtype = get_input('subtype', ELGG_ENTITIES_ANY_VALUE);
		$limit = sanitise_int(get_input('limit', 10), false);
		$container_guid = sanitise_int(get_input('container_guid'), false);
		if (empty($container_guid)) {
			$container_guid = ELGG_ENTITIES_ANY_VALUE;
		}
		
		if (($subtype === 'static') && $container_guid) {
			$owner_guid = $container_guid;
			$container_guid = ELGG_ENTITIES_ANY_VALUE;
		}
		
		// grab a list of entities and send them in json.
		$results = [];
		
		$options = [
			'type' => 'object',
			'subtype' => $subtype,
			'limit' => $limit,
			'owner_guid' => $owner_guid,
			'container_guid' => $container_guid,
			'joins' => [
				'JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid',
			],
			'wheres' => [
				"(oe.title LIKE '%{$q}%' OR oe.description LIKE '%{$q}%')",
			],
		];
		
		$entities = elgg_get_entities($options);
		if (!empty($entities)) {
			foreach ($entities as $entity) {
				$label = elgg_view('input/objectpicker/item_label', [
					'entity' => $entity,
					'owner_guid' => $owner_guid,
					'container_guid' => $container_guid,
				]);
				
				$output = elgg_view('input/objectpicker/item', [
					'entity' => $entity,
					'input_name' => $input_name,
					'owner_guid' => $owner_guid,
					'container_guid' => $container_guid,
				]);
				
				$result = [
					'type' => 'object',
					'name' => $entity->title,
					'desc' => $entity->description,
					'guid' => $entity->getGUID(),
					'label' => $label,
					'value' => $entity->getGUID(),
					'url' => $entity->getURL(),
					'html' => $output,
				];
				
				$results[] = $result;
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode($results);
		exit;
	}
}