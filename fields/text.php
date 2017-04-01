<?php
class acmsField_Text extends acmsField {

	function __construct() {	
		// vars
		$this->name = 'text';
		$this->label = __('Text', '');
		$this->defaults = array(
			'default_value'	=>	'',
			'formatting' 	=>	'html',
			'maxlength'		=>	'',
			'placeholder'	=>	'',
			'description' => '',
		);
		$this->options=0;
		$this->format=0;
		
		// do not delete!
    	parent::__construct();
	}
	
	function create_field($field) {
		// vars
		$o=array( 'class', 'name', 'id', 'value', 'placeholder', 'maxlength');
		$e = '';
		
		// maxlength
		if ($field['maxlength'] !== "" )
			$o[] = 'maxlength';
		
		$e .= '<div class="input-wrap">';
		$e .= '<input type="text"';
		
		foreach( $o as $k ) {
			$e .= ' ' . $k . '="' . esc_attr( $field[ $k ] ) . '"';	
		}
		
		$e .= ' />';
		$e .= '</div>';
		
		
		// return
		return $e;
	}
	
	function create_options_field($field) {		
		// vars
		$html='';
		
		$html.='<div class="input-wrap">';
		
			switch ($field['type']) :
				case 'select' :
					$html.='<select name="'.$field['name'].'">';
						foreach ($field['choices'] as $value => $display) :
							$html.='<option value="'.$value.'" '.selected($field['value'], $value, false).'>'.$display.'</option>';
						endforeach;
					$html.='</select>';
					break;
				case 'textarea' :
					$html.='<textarea name="'.$field['name'].'">'.$field['value'].'</textarea>';
					break;
				default:
					$html.='<input type="'.$field['type'].'" name="'.$field['name'].'" value="'.$field['value'].'" />';
			endswitch;
		
		$html.='</div>';		
		
		echo $html;
	}
	
	
	/*
	*  create_options()
	*
	*  Create exdiva options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save exdiva data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_options( $field ) {
		// vars
		$key = $field['name'];	
		?>
		<div class="field-row field_option_<?php echo $this->name; ?>">
			<label for="">Default Value</label>
			<?php 
			do_action('create_options_field_text', array(
				'type' => 'text',
				'name'	=>	'fields[' .$key.'][default_value]',
				'value'	=>	$field['default_value'],
			));
			?>
		</div>

		<div class="field-row field_option_<?php echo $this->name; ?>">
			<label>Placeholder Text</label>
		
			<?php 
			do_action('create_options_field_text', array(
				'type'	=>	'text',
				'name'	=>	'fields[' .$key.'][placeholder]',
				'value'	=>	$field['placeholder'],
			));
			?>
		</div>

		<div class="field-row field_option_<?php echo $this->name; ?>">
			<label>Formatting</label>
		
			<?php 
			do_action('create_options_field_text', array(
				'type' => 'select',
				'name' => 'fields[' .$key.'][formatting]',
				'value'	=> $field['formatting'],
				'choices' => array(
					'none'	=>	__("No formatting",'acf'),
					'html'	=>	__("Convert HTML into tags",'acf')
				)
			));
			?>
		</div>
		
		<div class="field-row field_option_<?php echo $this->name; ?>">
			<label>Character Limit</label>
		
			<?php 
			do_action('create_options_field_text', array(
				'type'	=>	'number',
				'name'	=>	'fields[' .$key.'][maxlength]',
				'value'	=>	$field['maxlength'],
			));
			?>
		</div>

		<div class="field-row field_option_<?php echo $this->name; ?>">
			<label>Description</label>
		
			<?php 
			do_action('create_options_field_text', array(
				'type'	=>	'textarea',
				'name'	=>	'fields[' .$key.'][description]',
				'value'	=>	$field['description'],
			));
			?>
		</div>

		<?php
		
	}
	
	//
	function format_value( $value, $post_id, $field ) {
		$value = htmlspecialchars($value, ENT_QUOTES);
		
		return $value;
	}
	
}

function register_amcs_text_field() {
	acms_register_field(new acmsField_Text());	
}
add_action('acms_register_field', 'register_amcs_text_field');
?>