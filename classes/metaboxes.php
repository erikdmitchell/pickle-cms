<?php
global $advancedMetaboxes;

/**
 * advancedCMSMetaboxes class.
 */
class advancedCMSMetaboxes {

	private $nonce = 'wp_upm_media_nonce'; // Represents the nonce value used to save the post media //
	private $option_name='advanced_meta_box_duped_boxes';

	protected $options=array();
	protected $post_types=array();

	public $fields=array();

	/**
	 * constructs our function, setups our scripts and styles, attaches meta box to wp actions
	 */
	function __construct() {
		$config=get_option('advanced_cms_metaboxes');

		$this->fields=array(
			'address' => array(
				'repeatable' => 1,
				'options' => 0,
				'format' => 0,
			),
			'button' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			),
			'checkbox' => array(
				'repeatable' => 1,
				'options' => 1,
				'format' => 0,
			),
			'colorpicker' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			),
			'date' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 1,
			),
			'gallery' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			),
			'email' => array(
				'repeatable' => 1,
				'options' => 0,
				'format' => 0,
			),
			'media' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			),
			'media_images' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			),
			'phone' => array(
				'repeatable' => 1,
				'options' => 0,
				'format' => 0,
			),
			'radio' => array(
				'repeatable' => 1,
				'options' => 1,
				'format' => 0,
			),
			'select' => array(
				'repeatable' => 0,
				'options' => 1,
				'format' => 0,
			),
			'text' => array(
				'repeatable' => 1,
				'options' => 0,
				'format' => 0,
			),
			'textarea' => array(
				'repeatable' => 1,
				'options' => 0,
				'format' => 0,
			),
			'timepicker' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			),
			'url'	 => array(
				'repeatable' => 1,
				'options' => 0,
				'format' => 0,
			),
			'wysiwyg' => array(
				'repeatable' => 0,
				'options' => 0,
				'format' => 0,
			)
		);
		$this->config=$this->setup_config($config); // set our config

		add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts_styles'));
		add_action('wp_enqueue_scripts', array($this, 'register_scripts_styles'));
		add_action('save_post', array($this, 'save_custom_meta_data'));
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

		add_action('wp_ajax_duplicate_metabox_field', array($this, 'ajax_duplicate_metabox_field'));
		add_action('wp_ajax_remove_duplicate_metabox_field' ,array($this, 'ajax_remove_duplicate_metabox_field'));
		add_action('wp_ajax_advanced_cms_gallery_update', array($this, 'ajax_advanced_cms_gallery_update'));

		add_filter('media_view_settings', array($this, 'media_view_settings'), 10, 2);

		add_action('admin_init', array($this, 'add_metaboxes_to_global'));
	}

	/**
	 * register_admin_scripts_styles function.
	 *
	 * @access public
	 * @param mixed $hook
	 * @return void
	 */
	public function register_admin_scripts_styles($hook) {
		global $post;

		wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css', array(), '1.10.4');
		wp_enqueue_style('colpick-css',  ADVANCED_CMS_URL.'/css/colpick.css');

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('colpick-js', ADVANCED_CMS_URL.'/js/colpick.js');
		wp_enqueue_script('jq-timepicker', ADVANCED_CMS_URL.'/js/jquery.ui.timepicker.js');
		wp_enqueue_script('jquery-maskedinput-script', ADVANCED_CMS_URL.'/js/jquery.maskedinput.min.js', array('jquery'), '1.3.1', true);
		wp_enqueue_script('jq-validator-script', ADVANCED_CMS_URL.'/js/jquery.validator.js', array('jquery'), '1.0.0', true);		
		wp_enqueue_script('duplicate-metabox-fields', ADVANCED_CMS_URL.'js/duplicate-metabox-fields.js', array('jquery'), '1.0.2', true);
		wp_enqueue_script('jquery-mediauploader', ADVANCED_CMS_URL.'js/jquery.mediauploader.js', array('jquery'), '0.1.0', true);
		wp_enqueue_script('advanced-cms-js', ADVANCED_CMS_URL.'/js/functions.js', array('jquery-mediauploader'), '1.0.0', true);

		if (isset($post->ID)) :
			$post_id=$post->ID;
		else :
			$post_id=false;
		endif;

		$datepicker=$this->jquery_datepicker_setup($post_id);

		$advancedcmsjs=array(
			'datepicker' => $datepicker,
		);

		wp_localize_script('advanced-cms-js', 'advancedCMSjs', $advancedcmsjs);
	}

	/**
	 * jquery_datepicker_setup function.
	 *
	 * @access public
	 * @param int $post_id (default: 0)
	 * @param string $format (default: 'mm/dd/yy')
	 * @return void
	 */
	public function jquery_datepicker_setup($post_id=0, $format='mm/dd/yy') {
		$value='';

		$arr=array(
			'format' => $format,
			'value' => $value,
			'id' => '',
		);

		if (empty($this->config) || !$post_id)
			return $arr;

		foreach ($this->config as $config) :
			if (!in_array(get_post_type($post_id), $config['post_types']))
				continue;

			foreach ($config['fields'] as $field) :
				if ($field['field_type']=='date') :
					if (!empty($field['format']['value']))
						$arr['format']=$field['format']['value'];

					$arr['value']=get_post_meta($post_id, $field['field_id'], true);
					$arr['id']=$field['field_id'];
				endif;
			endforeach;

		endforeach;

		return $arr;
	}

	/**
	 * register_scripts_styles function.
	 *
	 * @access public
	 * @return void
	 */
	function register_scripts_styles() {
		wp_enqueue_style('custom-video-js_css',plugins_url('/css/custom-video-js.css',__FILE__));

		wp_enqueue_script('video-js_js','//vjs.zencdn.net/4.2/video.js',array(),'4.2', true);
		wp_enqueue_style('video-js_css','//vjs.zencdn.net/4.2/video-js.css',array(),'4.2');
	}

	/**
	 * check_config_prefix function.
	 *
	 * makes sure our prefix starts with '_'
	 *
	 * @access public
	 * @param bool $config (default: false)
	 * @param bool $prefix (default: false)
	 * @return $prefix
	 */
	function check_config_prefix($prefix=false) {
		if (!$prefix)
			return false;

		if (substr($prefix,0,1)!='_')
			$prefix='_'.$prefix;

		return $prefix;
	}

	/**
	 * autoloads our helper classes and functions
	 * @param string $class_name - the name of the field/class to include
	**/
	private function autoload_class($filename) {
		require_once(plugin_dir_path(__FILE__).$filename.'.php');

		return new $filename;
	}

	/**
	 * add_meta_boxes function.
	 *
	 * creates the actual metabox itself using the id and title from the config file and attaches it to the post type
	 *
	 * @access public
	 * @return void
	 */
	public function add_meta_boxes() {
		global $config_id, $post;

		if (empty($this->config))
			return false;

		$this->add_custom_fields(); // method for adding custom metabox fields outside the cms //

		foreach ($this->config as $key => $config) :
			$config_id=$config['mb_id']; // for use in our classes function

			foreach ($config['post_types'] as $post_type) :
				add_meta_box(
					$config['mb_id'],
					__($config['title'], 'Upload_Meta_Box'),
					array($this, 'generate_meta_box_fields'),
					$post_type,
					apply_filters("advanced_cms_add_metabox_context_{$config['mb_id']}", 'normal'), // normal, advanced, side
					apply_filters("advanced_cms_add_metabox_priority_{$config['mb_id']}", 'high'), // high, core, default, low (prority)
					array(
						'config_key' => $key,
						'meta_box_id' => $config['mb_id'],
						'post_id' => $post->ID
					)
				);
			endforeach;
		endforeach;
	}

	/**
	 * generate_meta_box_fields function.
	 *
	 * @access public
	 * @param mixed $post
	 * @param mixed $metabox
	 * @return void
	 */
	function generate_meta_box_fields($post, $metabox) {
		$html=null;
		$this->fields=null; // this needs to be adjusted for legacy v 1.1.8
		$row_counter=1;

		wp_enqueue_script('advanced-cms-metabox-media-uploader', ADVANCED_CMS_URL.'/js/metabox-media-uploader.js', array('jquery'));

		wp_nonce_field(plugin_basename( __FILE__ ), $this->nonce);

		// because our legacy and current setups can be different, we need this function to do our post fields //
		$this->add_post_fields($this, $metabox, $post->ID);

		$html.='<div class="advanced-cms-meta-box">';

			foreach ($this->config as $config) :

				if ($metabox['args']['meta_box_id']==$config['mb_id']) :

					if (!empty($config['fields'])) :
						$this->add_fields_array($config['fields'],$config['mb_id']);
					endif;

				endif;

			endforeach;

			// output all of our fields //
			if (isset($this->fields)) :

				// sort fields by order //
				usort($this->fields, function ($a, $b) {
					if (function_exists('bccomp')) :
						return bccomp($a['order'], $b['order']);
					else :
						return strcmp($a['order'], $b['order']);
					endif;
				});

				foreach ($this->fields as $field) :
					$classes=$field['id'].' type-'.$field['type'];

					if ($field['duplicate'])
						$classes.=' clone';

					$html.='<div id="meta-row-'.$row_counter.'" class="meta-row '.$classes.'" data-input-id="'.$field['id'].'" data-field-type="'.$field['type'].'" data-field-order="'.$field['order'].'">';
						$html.='<label for="'.$field['id'].'">'.$field['label'].'</label>';

						$html.='<div class="fields-wrap">';
							$html.=$this->generate_field($field);

							if ($field['duplicate'])
								$html.='<button type="button" class="ajaxmb-field-btn delete">Delete Field</button>'; // add delete btn
						$html.='</div>';

					$html.='</div>';
					$row_counter++;
				endforeach;
			endif;

			$html.='<input type="hidden" id="advanced-cms-metabox-id" name="advanced-cms-metabox-id" value="'.$metabox['args']['meta_box_id'].'" />';
			$html.='<input type="hidden" id="advanced-cms-config-key" name="advanced-cms-config-key" value="'.$metabox['args']['config_key'].'" />';
			$html.='<input type="hidden" id="advanced-cms-post-id" name="advanced-cms-post-id" value="'.$metabox['args']['post_id'].'" />';
		$html.='</div>';

		echo $html;
	}

	/**
	 * generate_field function.
	 *
	 * generates the input box of each meta field
	 * uses a switch case to determine which field to output (default is text)
	 *
	 * @access public
	 * @param mixed $args
	 * @return void
	 */
	function generate_field($args) {
		global $post;

		$html=null;
		$values=get_post_custom($post->ID);
		$classes='field-input';
		$description=null;
		$description_visible=false;
		$format=false;
		$gallery_init=true;
		$value=null;

		if (isset($values[$args['id']][0]))
			$value=$values[$args['id']][0];

		if (!empty($args['field_description']))
			$description=$args['field_description'];

		if (!empty($args['format']))
			$format=$args['format'];

		switch ($args['type']) :
			case 'address':
				if (isset($value))
					$value=unserialize($value);
					
				$atts=array(
					'id' => $args['id'],
				);
				$defaults=array(
					'line1' => null,
					'line2' => null,
					'city' => null,
					'state' => null,
					'zip' => null,
					'country' => 'US'
				);
				$value=wp_parse_args($value, $defaults);

				$html.=advanced_cms_get_field_template('address', $atts, $value);
				break;
			case 'button' :
				$html.=advanced_cms_get_field_template('button', $args, $value);
				break;
			case 'checkbox':
				if (isset($args['options']) && !empty($args['options'])) :
					foreach ($args['options'] as $option) :
						$atts=array(
							'id' => $args['id'],
							'name' => $option['name'],
							'value' => $option['value'],
						);

						$html.=advanced_cms_get_field_template('checkbox', $atts, $value);
					endforeach;
				endif;
				break;
			case 'colorpicker' :
				$html.=advanced_cms_get_field_template('colorpicker', $args, $value);
				break;
			case 'date':
				$html.=advanced_cms_get_field_template('datepicker', $args, $value);
				break;
			case 'email' :
				$html.=advanced_cms_get_field_template('email', $args, $value);				
				break;
			case 'gallery' :
				$html.=advanced_cms_get_field_template('gallery', $args, $value);
				break;
			case 'media':
				$atts=array(
					'id' => $args['id'],
					'description_visible' => $description_visible,
					'description' => $description,
				);

				$html.=advanced_cms_get_field_template('media', $atts, $value);
				break;
			case 'media_images' :
				$html.=advanced_cms_get_field_template('media-images', '', unserialize($value));

				break;				
			case 'phone':
				$html.=advanced_cms_get_field_template('phone', $args, $value);				
				break;
			case 'radio':
				if (isset($args['options']) && !empty($args['options'])) :
					foreach ($args['options'] as $option) :
						$atts=array(
							'id' => $args['id'],
							'name' => $option['name'],
							'value' => $option['value'],
						);

						$html.=advanced_cms_get_field_template('radio', $atts, $value);
					endforeach;
				endif;
				break;
			case 'select' :
				$html.=advanced_cms_get_field_template('select', $args, $value);
				break;
			case 'text' :
				$html.=advanced_cms_get_field_template('text', $args, $value);	
				break;
			case 'textarea':
				$html.=advanced_cms_get_field_template('textarea', $args, $value);	
				break;
			case 'timepicker' :
				$html.=advanced_cms_get_field_template('timepicker', $args, $value);	
				break;
			case 'url':
				$html.=advanced_cms_get_field_template('url', $args, $value);	
				break;
			case 'wysiwyg':
				$settings=array(
					//'media_buttons' => false,
					//'textarea_rows' => 10,
					//'quicktags' => false
				);

				$html.=$this->advancedm_wp_editor($value,$args['id'],$settings);
				break;
			case 'custom' :
				if (is_serialized($value)) :
					$values=unserialize($value);
				else :
					$values=$value;
				endif;

				if (!is_array($values))
					$values=array($values);

				$html.=apply_filters('add_advanced_cms_metabox_custom_input-'.$args['id'],$args['id'],$values);
				break;
			default:
				$html.=advanced_cms_get_field_template('text', $args, $value);
		endswitch;

		if ($format)
			$html.='<span class="format">('.$format.')</span>';

		if ($args['repeatable'])
			$html.='<button type="button" class="ajaxmb-field-btn duplicate">Duplicate Field</button>';

		if (!$description_visible)
			$html.='<div class="description field_description">'.$description.'</div>';

		return $html;
	}

	/**
	 * a public function that allows the user to add a field to the meta box
	 * @param array $args
	 							id (field id)
	 							type (type of input field)
	 							label (for field)
	 							value (of field)
	 * because we allow multiple configs now, we must use legacy support (1 config) and expand to allow for multi configs (pre 1.1.8)
	**/
	public function add_field($args, $meta_id=false) {
		if (count($this->config)==1) :
			$prefix=$this->config[0]['prefix'];
		else :
			if ($meta_id) :
				foreach ($this->config as $config) :
					if (isset($config['id']) && $config['id']==$meta_id) :
						$prefix=$config['prefix'];
					elseif (isset($config['mb_id']) && $config['mb_id']==$meta_id) :
						$prefix=$config['prefix'];
					endif;
				endforeach;
			endif;
		endif;

		$new_field=array('id' => '', 'type' => 'text', 'label' => 'Text Box', 'value' => '', 'order' => 0);
		$new_field=array_merge($new_field,$args);

		if (is_numeric($new_field['id']))
			$new_field['id']=$this->generate_field_id($prefix,$new_field['label'],$new_field['id']);

		$this->fields[$new_field['id']]=$new_field;
	}

	/**
	 * generate_field_id function.
	 *
	 * @access public
	 * @param bool $prefix (default: false)
	 * @param bool $label (default: false)
	 * @param bool $field_id (default: false)
	 * @return $id
	 */
	function generate_field_id($prefix=false, $label=false, $field_id=false) {
		$id=null;

		if (!$prefix || !$label)
			return false;

		$prefix=$this->check_config_prefix($prefix);

		if (empty($label)) :
			$id=$prefix.'_'.$field_id;
		else :
			$id=$prefix.'_'.strtolower($this->clean_special_chars($label));
		endif;

		return $id;
	}

	/**
	 * a variation of the add_fields function
	 * this allows us to generate our fields with a passed array
	 * added 1.1.8
	**/
	function add_fields_array($arr, $meta_id) {
		$fields_counter=0;

		foreach ($arr as $id => $values) :
			$options=false;
			$repeatable=0;
			$order=0;
			$description=null;
			$format=null;

			if (isset($values['options']))
				$options=$values['options'];

			if (isset($values['repeatable']))
				$repeatable=1;

			if (isset($values['order']))
				$order=$values['order'];

			if (isset($values['field_description']))
				$description=$values['field_description'];

			if (isset($values['format']['value']))
				$format=$values['format']['value'];

			$args=array(
				'id' => $id,
				'type' => $values['field_type'],
				'label' => $values['field_label'],
				'order' => $order,
				'options' => $options,
				'repeatable' => $repeatable,
				'duplicate' => 0,
				'format' => $format,
				'field_description' => $description
			);

			$this->add_field($args, $meta_id);
			$fields_counter++;
		endforeach;
	}

	/**
	 * add_post_fields function.
	 *
	 * @access public
	 * @param bool $arr (default: false)
	 * @param bool $metabox (default: false)
	 * @param bool $post_id (default: false)
	 * @return void
	 */
	function add_post_fields($arr=false,$metabox=false,$post_id=false) {
		if (!$arr || empty($arr) || !$metabox || !$post_id)
			return false;

		foreach ($this->config as $config) :
			if ($metabox['args']['meta_box_id']==$config['mb_id']) :
				if (isset($config['post_fields']) && !empty($config['post_fields'])) :
					foreach ($config['post_fields'] as $post_field) :
						if ($post_field['post_id']==$post_id) :
							$args=array(
								'id' => $post_field['field_id'],
								'type' => $post_field['field_type'],
								'label' => $post_field['field_label'],
								'order' => $post_field['order'],
								'options' => 0,
								'repeatable' => 0,
								'duplicate' => 1
							);
							$this->add_field($args,$post_field['metabox_id']);
						endif;
					endforeach;
				endif;
			endif;
		endforeach;
	}

	/**
	 * save_custom_meta_data function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	public function save_custom_meta_data($post_id) {
		// Bail if we're doing an auto save
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		// if our nonce isn't there, or we can't verify it, bail
		if (!isset($_POST[$this->nonce]) || !wp_verify_nonce($_POST[$this->nonce],plugin_basename(__FILE__))) return;

		// if our current user can't edit this post, bail
		if (!current_user_can('edit_post',$post_id)) return;

		$this->add_custom_fields(); // method for adding custom metabox fields outside the cms // -- this is added here as well for proper saving

		// cycle through config fields and find matches //
		$_post_type=get_post_type($post_id);
		foreach ($this->config as $config) :
			$data=null;
			$prefix=$config['prefix'];

			// skip things that are not affiliated with this post type //
			if (!in_array($_post_type,$config['post_types']))
				continue;

			foreach ($config['fields'] as $id => $field) :
				$data=null;
				$field_id=$prefix.'_'.$id;

				if (isset($field['field_id']))
					$field_id=$field['field_id'];

				if (isset($_POST[$field_id]))
					$data=$_POST[$field_id]; // submitted value //

				// format date properly for db storage //
				if ($field['field_type']=='date')
					$data=date("Y-m-d H:i:s", strtotime($data));

				// fix notices on unchecked check boxes //
				//if (get_post_meta($post_id, $field['id']) == "") :
				//	add_post_meta($post_id, $field['id'], $data, true);
				//elseif ($data != get_post_meta($post_id, $field['id'], true)) :

				if ($data=='') :
					delete_post_meta($post_id, $field_id, get_post_meta($post_id, $field_id, true));
				else :
					update_post_meta($post_id, $field_id, $data);
				endif;

			endforeach;

			// if there's specific post fields, look for those //
			if (isset($config['post_fields']) && !empty($config['post_fields'])) :
				foreach ($config['post_fields'] as $post_field) :
					if ($post_field['post_id']==$post_id) :
						$field_id=$post_field['field_id'];

						if (isset($_POST[$field_id])):
							$data=$_POST[$field_id]; // submitted value //
						endif;

						if ($data=='') :
							delete_post_meta($post_id, $field_id, get_post_meta($post_id, $field_id, true));
						else :
							update_post_meta($post_id, $field_id, $data);
						endif;

					endif;
				endforeach;
			endif;

		endforeach;
	}

	/**
	 * setup our config with defaults and adjusments
	**/
	function setup_config($configs=array()) {
		$ran_string=substr(substr("abcdefghijklmnopqrstuvwxyz",mt_rand(0,25),1).substr(md5(time()),1),0,5);
		$default_config=array(
			'mb_id' => 'advancedmb_'.$ran_string,
			'title' => 'Default Meta Box',
			'prefix' => '_advancedmb',
			'post_types' => 'post,page',
			'fields' => array(), // for legacy support (pre 1.1.8)
			'post_fields' => array()
		);

		if (empty($configs))
			return false;

		foreach ($configs as $key => $config) :
			$config=array_merge($default_config,$config);

			if (!is_array($config['post_types'])) :
				$config['post_types']=explode(",",$config['post_types']);
			endif;

			$config['prefix']=$this->check_config_prefix($config['prefix']); // makes sure our prefix starts with '_'

			$configs[$key]=$config;
		endforeach;

		return $configs;
	}

	/**
	 * clean_special_chars function.
	 *
	 * removes all special chars from a string
	 *
	 * @access public
	 * @param mixed $string
	 * @return $string
	 */
	function clean_special_chars($string) {
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

		return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
	}

	/**
	 * ajax_duplicate_metabox_field function.
	 *
	 * adds specific fields to indvidual posts/pages via our duplicate field button
	 * because we use a config array, when the field is duplicated via js, it needs to be added
	 * however, the dup fields apply to individual posts only, so we have an array for them
	 *
	 * @access public
	 * @return void
	 */
	function ajax_duplicate_metabox_field() {
// todo: check empty
		$arr=array(
			'post_id' => $_POST['post_id'],
			'config_key' => $_POST['config_key'], // may not be needed
			'metabox_id' => $_POST['metabox_id'],
			'field_type' => $_POST['field_type'],
			'field_label' => $_POST['field_label'],
			'options' => array(),
			'field_id' => $_POST['field_id'],
			'order' => $_POST['order']
		);

		// check for dups and replace if found //
		$dup_flag=false;
		if (isset($this->config[$_POST['config_key']]['post_fields']) && !empty($this->config[$_POST['config_key']]['post_fields'])) :
			foreach ($this->config[$_POST['config_key']]['post_fields'] as $key => $post_field) :
				if ($post_field['field_id']==$arr['field_id']) :
					$this->config[$_POST['config_key']]['post_fields'][$key]=$arr;
					$dup_flag=true;
				endif;
			endforeach;
		endif;

		// insert if no dup found //
		if (!$dup_flag)
			$this->config[$_POST['config_key']]['post_fields'][]=$arr;

		if (update_option('advanced_cms_metaboxes',$this->config)) :
			echo true;
		else :
			echo false;
		endif;

		wp_die();
	}

	function ajax_remove_duplicate_metabox_field() {
// todo: check empty
		foreach ($this->config[$_POST['config_key']]['post_fields'] as $key => $post_field) :
			if ($post_field['field_id']==$_POST['field_id'])
				unset($this->config[$_POST['config_key']]['post_fields'][$key]);
		endforeach;

		$this->config[$_POST['config_key']]['post_fields']=array_values($this->config[$_POST['config_key']]['post_fields']);

		if (update_option('advanced_cms_metaboxes',$this->config)) :
			echo 'option updated';
		else :
			echo 'option failed to update (may be due to no fields being reomved)';
		endif;

		wp_die();
	}

	/**
	 * get_all_media_images function.
	 *
	 * @access public
	 * @return void
	 */
	function get_all_media_images() {
		$query_images_args = array(
			'posts_per_page' => -1,
			'post_type' => 'attachment',
			'post_mime_type' =>'image',
			'post_status' => 'inherit',
		);
		$query_images = new WP_Query( $query_images_args );

		$images = array();
		foreach ( $query_images->posts as $image) {
			$images[]=$image;
		}

		return $images;
	}

	/**
	 * get_attachment_id_from_url function.
	 *
	 * @access public
	 * @param string $attachment_url (default: '')
	 * @return void
	 */
	function get_attachment_id_from_url( $attachment_url = '' ) {

		global $wpdb;
		$attachment_id = false;

		// If there is no url, return.
		if ( '' == $attachment_url )
			return;

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();

		// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
		if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

			// Finally, run a custom database query to get the attachment ID from the modified attachment URL
			$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

		}

		return $attachment_id;
	}

	/**
	 * add_custom_fields function.
	 *
	 * method for adding custom metabox fields outside the cms
	 *
	 * @access public
	 * @return void
	 */
	function add_custom_fields() {
		foreach ($this->config as $key => $config) :

			$extra_fields=array();
			$extra_fields=apply_filters('add_advanced_cms_metabox_custom_fields-'.$config['mb_id'],$extra_fields,$config['prefix']);
			$extra_field_defaults=array(
				'field_type' => 'custom',
				'field_label' => 'Extra Field',
				'options' => array(),
				'field_description' => null,
				'order' => 99999,
				'field_id' => $this->generate_field_id($config['prefix'],'Extra Field')
			);

			if (empty($extra_fields))
				continue;

			foreach ($extra_fields as $extra_field) :
				$args=array_replace_recursive($extra_field_defaults,$extra_field);

				$this->config[$key]['fields'][]=$args;
			endforeach;

		endforeach;
	}

	/**
	 * advancedm_wp_editor function.
	 *
	 * @access protected
	 * @param mixed $content
	 * @param mixed $editor_id
	 * @param mixed $settings
	 * @return void
	 */
	protected function advancedm_wp_editor($content,$editor_id,$settings) {
		ob_start(); // Turn on the output buffer
		wp_editor($content,$editor_id,$settings); // Echo the editor to the buffer
		$editor_contents = ob_get_clean(); // Store the contents of the buffer in a variable

		return $editor_contents;
	}

	/**
	 * get_post_meta_advanced function.
	 *
	 * generates a preformatted get post meta field
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param string $key (default: '')
	 * @param bool $single (default: false)
	 * @param string $type (default: '')
	 * @return void
	 *
	 * Not Used v2.0.9
	 *
	 */
	public function get_post_meta_advanced($post_id,$key='',$single=false,$type='') {
		$metadata=get_metadata('post', $post_id, $key, $single);

		switch($type) :
			case 'phone' :
				$raw_phone=str_replace(' ','',$metadata);
				$raw_phone=str_replace('-','',$raw_phone);
				$raw_phone=str_replace('(','',$raw_phone);
				$raw_phone=str_replace(')','',$raw_phone);
				return '<a href="tel:'.$raw_phone.'">'.$metadata.'</a>';
				break;
			case 'fax' :
				$raw_fax=str_replace(' ','',$metadata);
				$raw_fax=str_replace('-','',$raw_fax);
				$raw_fax=str_replace('(','',$raw_fax);
				$raw_fax=str_replace(')','',$raw_fax);
				return '<a href="fax:'.$raw_fax.'">'.$metadata.'</a>';
				break;
			case 'url' :
				return '<a href="'.$metadata.'" target="_blank">'.$metadata.'</a>';
				break;
			default:
				return $metadata;
				break;
		endswitch;

		return $metadata;
	}

	/**
	 * get_countries_dropdown function.
	 *
	 * @access public
	 * @param string $name (default: 'country')
	 * @param string $selected (default: 'US')
	 * @return void
	 */
	public function get_countries_dropdown($name='country', $selected='US') {
		global $advanced_cms_countries;

		$html=null;

		$html.='<select name="'.$name.'">';
			$html.='<option value="0">Select Country</option>';
			foreach ($advanced_cms_countries as $id => $country) :
				$html.='<option value="'.$id.'" '.selected($selected,$id,false).'>'.$country.'</option>';
			endforeach;
		$html.='</select>';

		return $html;
	}

	/**
	 * get_gallery_images function.
	 *
	 * @access public
	 * @param array $ids (default: array())
	 * @return void
	 */
	public function get_gallery_images($ids=array()) {
		global $post;

		$images=false;

		if (!is_array($ids))
			$ids=explode(',',$ids);

		$ids=apply_filters('advanced_cms_get_gallery_images',$ids,$post);

		if (empty($ids))
			return false;

		foreach ($ids as $attachment_id) :
			$images.=wp_get_attachment_image($attachment_id,'thumbnail',false,array('class' => 'img-responsive advanced-cms-gallery-image'));
		endforeach;

		return $images;
	}

	/**
	 * get_gallery_image_ids function.
	 *
	 * @access public
	 * @param bool $ids (default: false)
	 * @return void
	 */
	public function get_gallery_image_ids($ids=false) {
		global $post;

		if ($ids && !is_array($ids))
			$ids=explode(',',$ids);

		$ids=apply_filters("advanced_cms_get_gallery_image_ids",$ids,$post);

		if (is_array($ids))
			$ids=implode(',',$ids);

		return $ids;
	}

	/**
	 * media_view_settings function.
	 *
	 * this is a helper function to load existing gallery images into the wp edit gallery screen
	 *
	 * @access public
	 * @param mixed $settings
	 * @param mixed $post
	 * @return void
	 */
	public function media_view_settings($settings,$post) {
		$gallery_field_ids=array();
		$post_type=get_post_type($post);

		// cycle through all fields and find our gallery field ids //
		if (!empty($this->config)) :
			foreach ($this->config as $config) :
				if (!in_array($post_type,$config['post_types']))
					continue;

				foreach ($config['fields'] as $fields) :
					if ($fields['field_type']=='gallery') :
						$gallery_field_ids[]=$fields['field_id'];
					endif;
				endforeach;
			endforeach;
		endif;

		// build our shortcodes // NEEDS WORK FOR MULTIPILE GALLERIES
		foreach ($gallery_field_ids as $field_id) :
			$images=null;

			if (get_post_meta($post->ID,$field_id,true))
				$images=get_post_meta($post->ID,$field_id,true);

			$shortcode=apply_filters('advanced_cms_media_settings_gallery_shortcode','[gallery ids="'.$images.'"]',$images,$post);

			$settings['advanced_cms_gallery']=array('shortcode' => $shortcode);
		endforeach;

		return $settings;
	}

	/**
	 * ajax_advanced_cms_gallery_update function.
	 *
	 * @access public
	 * @return void
	 */
	public function ajax_advanced_cms_gallery_update() {
		$images=null;
		$counter=0;

		if (!isset($_POST['ids']) || empty($_POST['ids']))
			return false;

		foreach ($_POST['ids'] as $attachment_id) :
			$images.=wp_get_attachment_image($attachment_id,'thumbnail',false,array('class' => 'img-responsive advanced-bg-image'));
		endforeach;

		echo json_encode($images);

		exit;
	}

	/**
	 * add_metaboxes_to_global function.
	 *
	 * appends our metaboxes to the $wp_meta_boxes global variable
	 *
	 * @access public
	 * @return void
	 */
	public function add_metaboxes_to_global() {
		global $wp_meta_boxes;

		// cycle through metaboxes //
		if (!$this->config || empty($this->config))
			return false;

		foreach ($this->config as $metabox) :
			$callback=array();

			// grab mb fields (callback) //
			foreach ($metabox['fields'] as $field) :
				if (isset($field['field_id']))
					$callback[]=$field['field_id'];
			endforeach;

			// setup for each post type //
			foreach ($metabox['post_types'] as $post_type) :
				$arr=array(
					$metabox['mb_id'] => array(
						'id' => $metabox['mb_id'],
						'title' => $metabox['title'],
						'callback' => $callback,
						'args' => ''
					)
				);
				$wp_meta_boxes[$post_type]['normal']['high']=$arr;
			endforeach;
		endforeach;
	}

} // end class

$advancedMetaboxes = new advancedCMSMetaboxes();
?>
