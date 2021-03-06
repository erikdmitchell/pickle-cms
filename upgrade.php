<?php

/**
 * mdw_cms_updates function.
 *
 * @access public
 * @return void
 */
function mdw_cms_updates() {
	global $mdw_cms_admin;

	$stored_version=get_option('mdw_cms_version', 0);

	// we have some version milestones that require updates //
	if (version_compare($stored_version, '2.1.8', '<=')) :
		mdw_cms_v2_1_8_cleanup();
	elseif (version_compare($stored_version, '2.1.9.1', '<')) : // we are migrating our dates to a MySQL format //
		$date_fields=array();

		// cycle through metaboxes/fields and see if any are dates //
		foreach ($mdw_cms_admin->options['metaboxes'] as $metabox) :
			foreach ($metabox['fields'] as $field) :
				if ($field['field_type']=='date')
					$date_fields[]=array(
						'id' => $field['field_id'],
						'post_types' => $metabox['post_types'],
					);
			endforeach;
		endforeach;

		// cycle through date fields and update in db //
		if (!empty($date_fields)) :
			foreach ($date_fields as $date_field) :
				$post_ids=get_posts(array(
					'posts_per_page' => -1,
					'post_type' => $date_field['post_types'],
					'fields' => 'ids',
				));

				// no posts so continue //
				if (!count($post_ids))
					continue;

				// go through posts (ids) //
				foreach ($post_ids as $post_id) :
					$date=get_post_meta($post_id, $date_field['id'], true);

					// convert date //
					if (!empty($date)) :
						$new_date=date("Y-m-d H:i:s", strtotime($date));
						update_post_meta($post_id, $date_field['id'], $new_date, $date);
					endif;
				endforeach;
			endforeach;
		endif;
	endif;

	update_mdw_cms_version();
}
add_action('admin_init', 'mdw_cms_updates');

/**
 * mdw_cms_version function.
 *
 * @access public
 * @return void
 */
function mdw_cms_version() {
	echo get_mdw_cms_version();
}

/**
 * get_mdw_cms_version function.
 *
 * @access public
 * @return void
 */
function get_mdw_cms_version() {
	return MDW_CMS_VERSION;
}

/**
 * update_mdw_cms_version function.
 *
 * @access public
 * @return void
 */
function update_mdw_cms_version() {
	$stored_version=get_option('mdw_cms_version', 0);

	if ($stored_version!=MDW_CMS_VERSION || !$stored_version)
		update_option('mdw_cms_version', MDW_CMS_VERSION);
}

/**
 * mdw_cms_v2_1_8_cleanup function.
 *
 * @access public
 * @return void
 */
function mdw_cms_v2_1_8_cleanup() {
	$version_cleaned=get_option('mdw_cms_options_clean_up', false);

	if ($version_cleaned)
		return;

	$options=get_option('mdw_cms_options', array());
	$metabox_options=get_option('mdw_cms_metaboxes', array());
	$post_type_options=get_option('mdw_cms_post_types', array());
	$taxonomies_options=get_option('mdw_cms_taxonomies', array());

	// check we have metaboxes to migrate //
	if (isset($options['metaboxes']) && !empty($options['metaboxes'])) :
		foreach ($options['metaboxes'] as $key => $metabox) :
			$flag=0;

			// see if we have a match and merge //
			foreach ($metabox_options as $mb_key => $mb) :
				if ($metabox['mb_id']==$mb['mb_id']) :
					$metabox_options[$mb_key]=mdw_cms_parse_args($metabox, $mb); //merge
					$flag=1; // set flag
				endif;
			endforeach;

			// check flag and add if not set //
			if (!$flag)
				$metabox_options[]=$metabox;

			unset($options['metaboxes'][$key]); // remove old option
		endforeach;

		// update in db //
		update_option('mdw_cms_options', $options);
		update_option('mdw_cms_metaboxes', $metabox_options);
	endif;

	// check we have post types to migrate //
	if (isset($options['post_types']) && !empty($options['post_types'])) :
		foreach ($options['post_types'] as $key => $post_type) :
			$flag=0;

			// see if we have a match and merge //
			foreach ($post_type_options as $pt_key => $pt) :
				if ($post_type['name']==$pt['name']) :
					$post_type_options[$pt_key]=mdw_cms_parse_args($post_type, $pt); //merge
					$flag=1; // set flag
				endif;
			endforeach;

			// check flag and add if not set //
			if (!$flag)
				$post_type_options[]=$post_type;

			unset($options['post_types'][$key]); // remove old option
		endforeach;

		// update in db //
		update_option('mdw_cms_options', $options);
		update_option('mdw_cms_post_types', $post_type_options);
	endif;

	// check we have taxonomies to migrate //
	if (isset($options['taxonomies']) && !empty($options['taxonomies'])) :
		foreach ($options['taxonomies'] as $key => $tax) :
			$flag=0;

			// see if we have a match and merge //
			foreach ($taxonomies_options as $t_key => $t) :
				if ($tax['name']==$t['name']) :
					$taxonomies_options[$t_key]=mdw_cms_parse_args($tax, $t); //merge
					$flag=1; // set flag
				endif;
			endforeach;

			// check flag and add if not set //
			if (!$flag)
				$taxonomies_options[]=$tax;

			unset($options['taxonomies'][$key]); // remove old option
		endforeach;

		// update in db //
		update_option('mdw_cms_options', $options);
		update_option('mdw_cms_taxonomies', $taxonomies_options);
	endif;

	update_option('mdw_cms_options_clean_up', true); // no need to run again
}
?>