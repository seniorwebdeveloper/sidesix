// ********************************************************************************* //

var ChannelImages = ChannelImages ? ChannelImages : new Object();
ChannelImages.prototype = {};

// ********************************************************************************* //

ChannelImages.Init = function(){

	ChannelImages.CIField = jQuery('.ChannelImagesField');
	ChannelImages.CIField.tabs();
	ChannelImages.SyncOrderNumbers();
	ChannelImages.ActivateJeditable();
	ChannelImages.ActivateSortable();

	ChannelImages.CIField.find('.ActionGroup tfoot select').live('change', ChannelImages.AddNewAction);
	ChannelImages.CIField.find('.ActionGroup .DelAction').live('click', function(){
		jQuery(this).closest('tr').fadeOut('slow', function(){
			jQuery(this).remove();
			setTimeout(function(){
				ChannelImages.SyncOrderNumbers();
			}, 100);
		});

		return false;
	});

	ChannelImages.CIField.find('.ci_upload_type').live('change', ChannelImages.ToggleLocation);
	ChannelImages.CIField.find('.ci_upload_type').trigger('change');

	ChannelImages.CIField.find('.AddActionGroup').click(ChannelImages.AddActionGroup);
	ChannelImages.CIField.find('.DelActionGroup').live('click', function(Event){
		jQuery(Event.target).closest('.ActionGroup').fadeOut('slow', function(){ jQuery(this).remove();  ChannelImages.SyncOrderNumbers(); })
		return false;
	});

	ChannelImages.CIField.find('.TestLocation').click(ChannelImages.TestLocation);

	// Kill Tablesorter on our inner tables
	setTimeout(function(){ChannelImages.CIField.find('thead th').unbind('click')}, 500);
};

//********************************************************************************* //

ChannelImages.SyncOrderNumbers = function(){
	ChannelImages.CIField.find('.ActionGroup').each(function(index, ActionGroup){

		jQuery(ActionGroup).find('> tbody > tr').each(function(trindex, TR){
			jQuery(TR).find('td:first').html(trindex+1);
			jQuery(TR).find('.action_step').attr('value', trindex+1);
		});

		jQuery(ActionGroup).find('.small_preview, .big_preview').attr('value', index+1);

		jQuery(ActionGroup).find('input, textarea, select').each(function(elemindex, InputElem){
			if (typeof(jQuery(InputElem).attr('name')) == 'undefined') return;
			attr = jQuery(InputElem).attr('name').replace(/\[action_groups\]\[.*?\]/, '[action_groups][' + (index+1) + ']');
			jQuery(InputElem).attr('name', attr);
		});

	});
};

//********************************************************************************* //

ChannelImages.ActivateJeditable = function(){
	ChannelImages.CIField.find('.ActionGroup .group_name h4').editable(function(value, settings){
		jQuery(this).closest('.group_name').find('.gname').attr('value', value);
		return value;
	},{
		type: 'text',
		onblur: 'submit',
		event: 'mouseenter',
		onedit: function(settings, elem){ jQuery(elem).closest('.group_name').find('small').hide() },
		onsubmit: function(settings, elem){ jQuery(elem).closest('.group_name').find('small').show() }
	});
};

//********************************************************************************* //

ChannelImages.ActivateSortable = function(){
	ChannelImages.CIField.find('.CIActions .ActionGroup').each(function(index, Group){

		jQuery(Group).find('tbody').sortable({
			handle: '.MoveAction',
			axis: 'y',
			containment: Group,
			helper: function(e, ui) {
				ui.children().each(function() {
					$(this).width($(this).width());
				});

				return ui;
			},
			stop: function(Event, UI){
				ChannelImages.SyncOrderNumbers();
			}
		});

	});
};

//********************************************************************************* //

ChannelImages.AddNewAction = function(Event){
	var Type = $(this).val();
	if (Type == false) return;

	var Content = jQuery.base64Decode( jQuery(this).closest('.CIActions').find('.default_actions .'+Type).text() ); // Decode Text
	jQuery(this).closest('table').find('tbody:first').append(Content);

	setTimeout(function(){
		ChannelImages.SyncOrderNumbers();
	}, 100);

	return;
};

//********************************************************************************* //

ChannelImages.ToggleLocation = function(Event){

	Value = jQuery(Event.target).val();

	ChannelImages.CIField.find('.CILocSettings').find('.CIUpload_local,.CIUpload_s3,.CIUpload_cloudfiles').hide();
	ChannelImages.CIField.find('.CILocSettings .CIUpload_' + Value).show();

};

//********************************************************************************* //

ChannelImages.AddActionGroup = function(Event){

	var Cloned = ChannelImages.CIField.find('.ActionGroup:last').clone();

	Cloned.hide().find('tbody tr').remove();
	Cloned.find('.group_name h4').text('Untitled').parent().find('> input').attr('large', 'untitled');
	Cloned.find('.small_preview, .big_preview').attr('checked', false);
	Cloned.find('.wysiwyg').attr('checked', true);
	ChannelImages.CIField.find('.ActionGroup:last').after(Cloned).execute(function(){
		ChannelImages.CIField.find('.ActionGroup:last').fadeIn('slow');
		ChannelImages.ActivateJeditable();
	});

	return false;
};

//********************************************************************************* //

ChannelImages.TestLocation = function(Event){
	Event.preventDefault();

	var Params = {};
	Params.ajax_method = 'test_location';

	ChannelImages.CIField.find('.CILocSettings').find('input,select').each(function(index, elem){
		Params[$(elem).attr('name')] = $(elem).val();
	});

	$.colorbox({
		href: ChannelImages.AJAX_URL,
		data: Params
	});
};

//********************************************************************************* //