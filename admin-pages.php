<?php
class MDWCMSgui {

	public $options=array();
	protected $admin_notices_output=array();

	function __construct() {
		add_action('admin_menu',array($this,'build_admin_menu'));
		add_action('admin_enqueue_scripts',array($this,'scripts_styles'));
		//add_action('admin_notices',array($this,'admin_notices')); // may not be needed
		//add_filter('mdw_cms_admin_notices',array($this,'admin_notices'));

		add_action('admin_init','MDWCMSlegacy::setup_legacy_updater');
		add_action('admin_notices','MDWCMSlegacy::legacy_admin_notices');

		$this->update_mdw_cms_settings();

		$this->options['version']=get_option('mdw_cms_version');
		$this->options['options']=get_option('mdw_cms_options');
		$this->options['metaboxes']=get_option('mdw_cms_metaboxes');
		$this->options['post_types']=get_option('mdw_cms_post_types');
		$this->options['taxonomies']=get_option('mdw_cms_taxonomies');
	}

	/**
	 * build_admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	function build_admin_menu() {
		add_management_page('MDW CMS','MDW CMS','administrator','mdw-cms',array($this,'mdw_cms_page'));
	}

	/**
	 * scripts_styles function.
	 *
	 * @access public
	 * @param mixed $hook
	 * @return void
	 */
	function scripts_styles($hook) {
		$disable_bootstrap=false;

		wp_enqueue_style('mdw-cms-gui-style',plugins_url('/css/admin.css',__FILE__));

		wp_register_script('mdw-cms-admin-metaboxes-script',plugins_url('/js/admin-metaboxes.js',__FILE__),array('metabox-id-check-script'));

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('mdw-cms-gui-mb-script',plugins_url('/js/mb.js',__FILE__),array('jquery'),'1.0.0',true);
		wp_enqueue_script('namecheck-script',plugins_url('/js/jquery.namecheck.js',__FILE__),array('jquery'));
		wp_enqueue_script('metabox-id-check-script',plugins_url('/js/jquery.metabox-id-check.js',__FILE__),array('jquery'));
		wp_enqueue_script('mdw-cms-admin-custom-post-types-script',plugins_url('/js/admin-custom-post-types.js',__FILE__),array('namecheck-script'));
		wp_enqueue_script('mdw-cms-admin-custom-taxonomies-script',plugins_url('/js/admin-custom-taxonomies.js',__FILE__),array('namecheck-script'));


		if (isset($this->options['options']) && is_array($this->options['options']))
			extract($this->options['options']);

		if (!$disable_bootstrap) :
			wp_enqueue_style('mdw-cms-bootstrap-custom-script',plugins_url('admin/css/bootstrap.css',__FILE__));
			//wp_enqueue_style('mdw-cms-bootstrap-theme-custom-script',plugins_url('/css/bootstrap-theme.min.css',__FILE__));
		endif;

		$post_types=get_post_types();
		$types=array();
		foreach ($post_types as $post_type) :
			$types[]=$post_type;
		endforeach;

		$taxonomy_options=array(
			'reservedPostTypes' => $types
		);

		wp_localize_script('mdw-cms-admin-custom-taxonomies-script','wp_options',$taxonomy_options);

		$metaboxes=$this->options['metaboxes'];
		$mb_arr=array();

		if ($metaboxes && !empty($metaboxes)) :
			foreach ($metaboxes as $metabox) :
				$mb_arr[]=$metabox['mb_id'];
			endforeach;
		endif;

		$metabox_options=array(
			'reserved' => $mb_arr
		);

		wp_localize_script('mdw-cms-admin-metaboxes-script','wp_metabx_options',$metabox_options);

		wp_enqueue_script('mdw-cms-admin-metaboxes-script');
	}

	/**
	 * mdw_cms_page function.
	 *
	 * our primary admin page, utlaizes tabs for internal navigation
	 *
	 * @access public
	 * @return void
	 */
	function mdw_cms_page() {
		$html=null;
		$tabs=array(
			'cms-main' => 'Main',
			'mdw-cms-cpt' => 'Custom Post Types',
			'mdw-cms-metaboxes' => 'Metaboxes',
			'mdw-cms-tax' => 'Custom Taxonomies'
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'cms-main';
		?>
		<div class="wrap mdw-cms-wrap">

			<h1>MDW CMS</h1>

			<?php echo implode('',$this->admin_notices_output); ?>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ($tabs as $tab => $name) :
					if ($active_tab==$tab) :
						$class='nav-tab-active';
					else :
						$class=null;
					endif;
					?>
					<a href="?page=mdw-cms&tab=<?php echo $tab; ?>" class="nav-tab <?php echo $class; ?>"><?php echo $name; ?></a>
				<?php endforeach; ?>
			</h2>

			<?php
			switch ($active_tab) :
				case 'cms-main':
					$html.=mdw_cms_get_template('main');
					break;
				case 'mdw-cms-cpt':
					echo mdw_cms_get_template('custom-post-types');
					break;
				case 'mdw-cms-metaboxes':
					echo mdw_cms_get_template('metaboxes');
					break;
				case 'mdw-cms-tax':
					echo mdw_cms_get_template('custom-taxonomies');
					break;
				default:
					echo mdw_cms_get_template('main');
					break;
			endswitch;
			?>

		</div><!-- /.wrap -->
		<?php
	}

	/**
	 *
	 */
	function build_field_rows($field_id,$field,$order=0,$classes='') {
		global $MDWMetaboxes;

		$html=null;
		$field_description=null;
		$prefix=null;

		$label_class='col-md-3';
		$input_class='col-md-3';
		$description_class='col-md-6';
		//$description_ext_class='col-md-9 col-md-offset-3';
		//$error_class='col-md-12';
		$select_class='col-md-3';
		//$existing_label_class='col-md-5';
		//$edit_class='col-md-2';
		//$delete_class='col-md-2';

		if (isset($_GET['edit']) && $_GET['edit']=='mb') :
			foreach ($this->options['metaboxes'] as $key => $mb) :
				if ($mb['mb_id']==$_GET['mb_id']) :
					extract($this->options['metaboxes'][$key]);
				endif;
			endforeach;
		endif;

		if (isset($field['repeatable']) && $field['repeatable']) :
			$repeatable_checked='checked="checked"';
		else :
			$repeatable_checked=null;
		endif;

		if (isset($field['format']['value'])) :
			$format=$field['format']['value'];
		else :
			$format=null;
		endif;

		if (isset($field['field_description']) && !empty($field['field_description']))
			$field_description=$field['field_description'];

		$html.='<div class="row sortable fields-wrapper '.$classes.'" id="fields-wrapper-'.$field_id.'">';
			$html.='<div class="fields-wrapper-border">';
				$html.='<div class="col-md-1">';
					$html.='<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>';
				$html.='</div>';

				$html.='<div class="col-md-11">';
					$html.='<div class="row">';
						$html.='<div class="col-md-3 field-type-label">';
							$html.='<label for="field_type">Field Type</label>';
						$html.='</div>';
						$html.='<div class="col-md-9">';
							$html.='<select class="field_type name-item" name="fields['.$field_id.'][field_type]">';
								$html.='<option value=0>Select One</option>';
								foreach ($MDWMetaboxes->fields as $field_type => $setup) :
									$html.='<option value="'.$field_type.'" '.selected($field['field_type'],$field_type,false).'>'.$field_type.'</option>';
								endforeach;
							$html.='</select>';
						$html.='</div>';
					$html.='</div><!-- .row -->';
				$html.='</div>';

				$html.='<div class="field-label col-md-11 col-md-offset-1">';
					$html.='<div class="row">';
						$html.='<div class="col-md-3 field-label-label">';
							$html.='<label for="field_label">Label</label>';
						$html.='</div>';
						$html.='<div class="col-md-9 label-input">';
							$html.='<input type="text" name="fields['.$field_id.'][field_label]" class="field_label name-item" value="'.$field['field_label'].'" />';
						$html.='</div>';
					$html.='</div>';
				$html.='</div>';

				$html.='<div class="field-options col-md-11 col-md-offset-1" id="">';
					foreach ($MDWMetaboxes->fields as $field_type => $setup) :
						$html.='<div class="type" data-field-type="'.$field_type.'">';
							if ($setup['repeatable']) :
								$html.='<div class="field repeatable row">';
									$html.='<div class="col-md-3 field-repeatable-label">';
										$html.='<label for="repeatable">Repeatable</label>';
									$html.='</div>';
									$html.='<div class="col-md-9 field-repeatable-check">';
										$html.='<input type="checkbox" name="fields['.$field_id.'][repeatable]" value="1" class="repeatable-box name-item" '.$repeatable_checked.' />';
									$html.='</div>';
								$html.='</div>';
							endif;

							if ($setup['options']) :
								$html.='<div class="field options" id="field-options-'.$field_id.'">';
									$html.='<label for="options">Options</label>';
									// get options //
									if (isset($field['options']) && !empty($field['options'])) :
										foreach ($field['options'] as $key => $option) :
											$html.='<div class="option-row" id="option-row-'.$key.'">';
												$html.='<label for="options-default-name">Name</label>';
												$html.='<input type="text" name="fields['.$field_id.'][options]['.$key.'][name]" class="options-item name" value="'.$option['name'].'" />';
												$html.='<label for="options-default-value">Value</label>';
												$html.='<input type="text" name="fields['.$field_id.'][options]['.$key.'][value]" class="options-item value" value="'.$option['value'].'" />';
											$html.='</div><!-- .option-row -->';
										endforeach;
									endif;

									// blank option //
									$html.='<div class="option-row default" id="option-row-default">';
										$html.='<label for="options-default-name">Name</label>';
										$html.='<input type="text" name="fields['.$field_id.'][options][default][name]" class="options-item name" value="" />';
										$html.='<label for="options-default-value">Value</label>';
										$html.='<input type="text" name="fields['.$field_id.'][options][default][value]" class="options-item value" value="" />';
									$html.='</div><!-- .option-row -->';

									$html.='<div class="add-option-field"><input type="button" name="add-option-field" class="add-option-field-btn button button-primary" value="Add Option"></div>';
								$html.='</div>';
							endif;

							if ($setup['format']) :
								$html.='<div class="field format row">';
									$html.='<div class="col-md-3 field-format-label">';
										$html.='<label for="format">Format</label>';
									$html.='</div>';
									$html.='<div class="col-md-9 field-format-check">';
										$html.='<input type="text" name="fields['.$field_id.'][format][value]" class="options-item value" value="'.$format.'" />';
									$html.='</div>';
								$html.='</div>';
							endif;
						$html.='</div>';
					endforeach;
				$html.='</div><!-- .field-options -->';

				$html.='<div class="description col-md-11 col-md-offset-1">';
					$html.='<div class="row">';
						$html.='<div class="col-md-3 file-description-label">';
							$html.='<label for="field_description">Field Description</label>';
						$html.='</div>';
						$html.='<div class="col-md-9 fd">';
							$html.='<input type="text" name="fields['.$field_id.'][field_description]" class="field_description name-item" value="'.$field_description.'" />';
						$html.='</div>';
					$html.='</div>';
				$html.='</div><!-- .description -->';

				$html.='<div class="field-id col-md-11 col-md-offset-1">';
					$html.='<div class="row">';
						$html.='<div class="col-md-3 field-id-label">';
							$html.='<label for="field_id">Field ID</label>';
						$html.='</div>';
						$html.='<div class="col-md-9 field-id-id">';
							$html.='<div class="gen-field-id"><input type="text" readonly="readonly" value="'.$MDWMetaboxes->generate_field_id($prefix,$field['field_label'],$field_id).'" /> <span class="description">(use as meta key)</span></div>';
						$html.='</div>';
					$html.='</div>';
				$html.='</div><!-- .description -->';

				$html.='<div class="remove col-md-11 col-md-offset-1">';
					$html.='<input type="button" name="remove-field" id="remove-field-btn" class="button button-primary remove-field" data-id="fields-wrapper-'.$field_id.'" value="Remove">';
				$html.='</div>';

				$html.='<input type="hidden" name="fields['.$field_id.'][order]" class="order name-item" value="'.$order.'" />';
			$html.='</div>';
		$html.='</div><!-- .fields-wrapper -->';

		return $html;
	}

	function update_options($options) {
		if (!$options['update'])
			return false;

		$new_options=$options;
		unset($new_options['update']); // a temp var passed, remove it

		update_option('mdw_cms_options',$new_options);

		return get_option('mdw_cms_options');
	}

	/**
	 * runs all of our update and edit functions
	 * called in __construct and run before everything so that our options are updated before page load
	 */
	function update_mdw_cms_settings() {
		$post_types=get_option('mdw_cms_post_types');
		$metaboxes=get_option('mdw_cms_metaboxes');
		$taxonomies=get_option('mdw_cms_taxonomies');

		// create custom post type //
		if (isset($_POST['add-cpt']) && $_POST['add-cpt']=='Create') :
			if ($this->update_custom_post_types($_POST)) :
				$this->admin_notices('updated','Post type has been created.');
			else :
				$this->admin_notices('error','There was an issue creating the post type.');
			endif;
		endif;

		// update/edit custom post type //
		if (isset($_POST['add-cpt']) && $_POST['add-cpt']=='Update') :
			if ($this->update_custom_post_types($_POST)) :
				$this->admin_notices('updated','Post type has been updated.');
			else :
				$this->admin_notices('error','There was an issue updating the post type.');
			endif;
		endif;

		// remove custom post type //
		if (isset($_GET['delete']) && $_GET['delete']=='cpt') :
			foreach ($post_types as $key => $cpt) :
				if ($cpt['name']==$_GET['slug']) :
					unset($post_types[$key]);
					$this->admin_notices('updated','Post type has been deleted.');
				endif;
			endforeach;

			$post_types=array_values($post_types);

			update_option('mdw_cms_post_types',$post_types);
		endif;

		// add metabox //
		if (isset($_POST['update-metabox']) && $_POST['update-metabox']=='Create') :
			if ($this->update_metaboxes($_POST)) :
				$this->admin_notices('updated','Metabox has been created.');
				// redirect ?? //
				if (!function_exists('wp_get_current_user')) :
					include(ABSPATH . "wp-includes/pluggable.php");
				endif;

				wp_redirect(admin_url('tools.php?page=mdw-cms&tab=mdw-cms-metaboxes&edit=mb&mb_id='.$_POST['mb_id']));
				exit;
			else :
				$this->admin_notices('error','There was an issue creating the metabox.');
			endif;
		endif;

		// update/edit metabox //
		if (isset($_POST['update-metabox']) && $_POST['update-metabox']=='Update') :
			if ($this->update_metaboxes($_POST)) :
				$this->admin_notices('updated','Metabox has been updated.');
			else :
				$this->admin_notices('error','There was an issue updating the metabox.');
			endif;
		endif;

		// remove metabox //
		if (isset($_GET['delete']) && $_GET['delete']=='mb') :
			foreach ($metaboxes as $key => $mb) :
				if ($mb['mb_id']==$_GET['mb_id']) :
					unset($metaboxes[$key]);
					$this->admin_notices('updated','Metabox has been removed.');
				endif;
			endforeach;

			$metaboxes=array_values($metaboxes);

			update_option('mdw_cms_metaboxes',$metaboxes);
		endif;

		// create custom taxonomy //
		if (isset($_POST['add-tax']) && $_POST['add-tax']=='Create') :
			if ($this->update_taxonomies($_POST)) :
				$this->admin_notices('updated','Taxonomy has been created.');
			else :
				$this->admin_notices('error','There was an issue creating the taxonomy.');
			endif;
		endif;

		// update/edit taxonomy //
		if (isset($_POST['add-tax']) && $_POST['add-tax']=='Update') :
			if ($this->update_taxonomies($_POST)) :
				$this->admin_notices('updated','Taxonomy has been updated.');
			else :
				$this->admin_notices('error','There was an issue updating the taxonomy.');
			endif;
		endif;

		// remove taxonomy //
		if (isset($_GET['delete']) && $_GET['delete']=='tax') :
			foreach ($taxonomies as $key => $tax) :
				if ($tax['name']==$_GET['slug']) :
					unset($taxonomies[$key]);
					$this->admin_notices('updated','Taxonomy has been deleted.');
				endif;
			endforeach;

			$taxonomies=array_values($taxonomies);

			update_option('mdw_cms_taxonomies',$taxonomies);
		endif;
	}

	/**
	 * update_custom_post_types function.
	 *
	 * @access public
	 * @static
	 * @param array $data (default: array())
	 * @return void
	 */
	public static function update_custom_post_types($data=array()) {
		$post_types=get_option('mdw_cms_post_types');
		$post_types_s=serialize($post_types);

		if (!isset($data['name']) || $data['name']=='')
			return false;

		$arr=array(
			'name' => $data['name'],
			'label' => $data['label'],
			'singular_label' => $data['singular_label'],
			'description' => $data['description'],
			'title' => $data['title'],
			'thumbnail' => $data['thumbnail'],
			'editor' => $data['editor'],
			'revisions' => $data['revisions'],
			'hierarchical' => $data['hierarchical'],
			'page_attributes' => $data['page_attributes'],
			'comments' => $data['comments']
		);

		if ($data['cpt-id']!=-1) :
			$post_types[$data['cpt-id']]=$arr;
		else :
			if (!empty($post_types)) :
				foreach ($post_types as $cpt) :
					if ($cpt['name']==$data['name'])
						return false;
				endforeach;
			endif;
			$post_types[]=$arr;
		endif;

		// we are simply updating the same info -- force true //
		if ($post_types_s==serialize($post_types))
			return true;

		return update_option('mdw_cms_post_types',$post_types);
	}

	/**
	 * update_metaboxes function.
	 *
	 * updates our metabox settings and its fields
	 *
	 * @access public
	 * @static
	 * @param array $data (default: array())
	 * @return void
	 */
	public static function update_metaboxes($data=array()) {
		global $MDWMetaboxes;

		$metaboxes=get_option('mdw_cms_metaboxes');
		$edit_key=-1;

		if (!isset($data['mb_id']) || $data['mb_id']=='')
			return false;

		// check for prefix //
		if (empty($data['prefix'])) :
			$prefix='_'.$data['mb_id'];
		else :
			$prefix=$data['prefix'];
		endif;

		if (empty($data['post_types']))
			$data['post_types'][]='post';

		$arr=array(
			'mb_id' => $data['mb_id'],
			'title' => $data['title'],
			'prefix' => $prefix,
			'post_types' => $data['post_types'],
		);

		// clean fields, if any //
		if (isset($data['fields'])) :
			foreach ($data['fields'] as $key => $field) :
				if (!$field['field_type']) :
					unset($data['fields'][$key]);
				else :
					$data['fields'][$key]['field_id']=$MDWMetaboxes->generate_field_id($prefix,$field['field_label']); // add id
					// remove empty options fields //
					if (isset($field['options'])) :
						unset($data['fields'][$key]['options']['default']);
						$data['fields'][$key]['options']=array_values($data['fields'][$key]['options']);
					endif;
				endif;
			endforeach;
		endif;

		if (isset($data['fields']))
			$arr['fields']=array_values($data['fields']);

		if (!empty($metaboxes)) :
			foreach ($metaboxes as $key => $mb) :
				if ($mb['mb_id']==$data['mb_id']) :
					if (isset($data['update-metabox']) && $data['update-metabox']=='Update') :
						$edit_key=$key;
						if (isset($arr['post_fields'])) :
							$arr['post_fields']=$mb['post_fields'];
						endif;
					else :
						return false;
					endif;
				endif;
			endforeach;
		endif;

		if ($edit_key!=-1) :
			$metaboxes[$edit_key]=$arr;
		else :
			$metaboxes[]=$arr;
		endif;

		return update_option('mdw_cms_metaboxes',$metaboxes);
	}

	/**
	 * update_taxonomies function.
	 *
	 * @access public
	 * @param array $data (default: array())
	 * @return void
	 */
	function update_taxonomies($data=array()) {
		$option_exists=false;
		$taxonomies=get_option('mdw_cms_taxonomies');

		if (!isset($data['name']) || $data['name']=='')
			return false;

		$arr=array(
			'name' => $data['name'],
			'object_type' => $data['post_types'],
			'args' => array(
				'hierarchical' => true,
				'label' => $data['label'],
				'query_var' => true,
				'rewrite' => true
			)
		);

		if ($data['tax-id']!=-1) :
			$taxonomies[$data['tax-id']]=$arr;
		else :
			if (!empty($taxonomies)) :
				foreach ($taxonomies as $tax) :
					if ($tax['name']==$data['name'])
						return false;
				endforeach;
			endif;
			$taxonomies[]=$arr;
		endif;

		if (get_option('mdw_cms_taxonomies'))
			$option_exists=true;

		$update=update_option('mdw_cms_taxonomies',$taxonomies);

		if ($update) :
			return true;
		elseif ($option_exists) :
			return true;
		else :
			return false;
		endif;
	}

	/**
	 * admin_notices function.
	 *
	 * @access public
	 * @param string $class (default: 'error')
	 * @param string $message (default: '')
	 * @return void
	 */
	function admin_notices($class='error',$message='') {
		$this->admin_notices_output[]='<div class="'.$class.'"><p>'.$message.'</p></div>';
	}

	/**
	 * get_post_types_list function.
	 *
	 * @access public
	 * @param bool $selected_pt (default: false)
	 * @param string $output (default: 'checkbox')
	 * @return void
	 */
	public function get_post_types_list($selected_pt=false, $output='checkbox') {
		$html=null;
		$args=array(
			'public' => true
		);
		$post_types_arr=get_post_types($args);

		$label_class='col-md-3';
		$input_class='col-md-3';

		$html.='<div class="form-row row post-type-list-admin">';
			$html.='<label for="post_type" class="'.$label_class.'">Post Type</label>';
			$html.='<div class="post-types-cbs '.$input_class.'">';
				$counter=0;
				foreach ($post_types_arr as $type) :
					if ($counter==0) :
						$class='first';
					else :
						$class='';
					endif;

					if ($selected_pt && in_array($type,$selected_pt)) :
						$checked='checked=checked';
					else :
						$checked=null;
					endif;


					$html.='<div class="col-md-12">';
						$html.='<input type="checkbox" name="post_types[]" value="'.$type.'" '.$checked.'>'.$type.'<br />';
					$html.='</div>';

					$counter++;
				endforeach;
			$html.='</div>';
		$html.='</div>';

		return $html;
	}

}

$mdw_cms_admin=new MDWCMSgui();
?>
