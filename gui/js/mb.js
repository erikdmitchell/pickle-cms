$j=jQuery.noConflict();

$j(document).ready(function() {
	
	// display field data on load //
	$j('.fields-wrapper').each(function() {
		var ddValue=$j(this).find('.field_type').val();
		
		$j(this).find('.type').each(function() {
			if ($j(this).data('field-type')==ddValue) {
				$j(this).show();							
			} else {
				$j(this).hide();	
			}	
		});	
	});
	
	// display field data on change //
	$j('.add-fields .field_type').on('change',function() {
		var ddValue=$j(this).val();
		
		$j(this).parent().parent().find('.type').each(function() {
			if ($j(this).data('field-type')==ddValue) {
				$j(this).show();							
			} else {
				$j(this).hide();	
			}		
		});
	});
	
	// adds a new field to our admin area //
	$j('#add-field-btn').on('click', function() {
		var newID=0;
		var lastFieldID='';
		var $clonedElement=$j('#fields-wrapper-default').clone();
		
		$j('.fields-wrapper').each(function(i) {
			newID=i+1;
			lastFieldID=$j(this).attr('id');
		});		
		
		// clean up and configure our cloned element (classes, ids, etc)
		var cloneID='fields-wrapper-'+newID;
		
		$clonedElement.removeClass('default');
		$clonedElement.attr('id',cloneID);

		$clonedElement.insertAfter('#'+lastFieldID);
		
		$j('#'+cloneID+' .name-item').each(function() {
			var attrName=$j(this).attr('name');
			var attrNewName=attrName.replace('default',newID);
			$j(this).attr('name',attrNewName);
		});
	});
	
	$j('a.remove-field').on('click',function(e) {
		e.preventDefault();
		var elemID=$j(this).data('id');
		$j('#'+elemID).remove();
	});
	
	// handles our checkbox issue for forms //
	$j('input[type="checkbox"]').on('change', function(e){
		if ($j(this).prop('checked')) {
			$j(this).val(1);
		} else {
			$j(this).val(0);	
			$j(this).removeAttr('checked');
		}
	});
	
});