// ********************************************************************************* //
var Forms = Forms ? Forms : new Object();
// ********************************************************************************* //

jQuery(document).ready(function(){

	Forms.StageWrappers = jQuery('div.FormStageWrapper');
	Forms.FormsElemTmpl = Hogan.compile(jQuery('#FormsElemTmpl').html()); // Parse the Hogan.JS Template
	Forms.ParseDBFields(); // Parse all DB Fields
	Forms.ActivateDraggable(); // Activate jQuery Draggable
	Forms.ActivateSortable(); // Activate jQuery Sortable

	jQuery('.FormSettings').find('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		changeYear: true
	});

	jQuery('.FormSettings').find('select.chosen').chosen();

	Forms.StageWrappers.find('.StageFields').delegate('.actions .del', 'click', Forms.DeleteFormElem);
	Forms.StageWrappers.find('.StageFields').delegate('.actions .settings', 'click', Forms.ToggleSettingsFormElem);
	Forms.StageWrappers.find('.StageFields').delegate('.SaveSettings', 'click', Forms.SaveElemSettings);
	Forms.StageWrappers.find('.StageFields').delegate('input.FieldRequired', 'change', Forms.ToggleFieldRequired);
	Forms.StageWrappers.find('.FormTools .abody').delegate('a', 'click', Forms.AddFieldByClick);
	Forms.StageWrappers.closest('div.Forms').find('div.FormAlerts div.formbar').delegate('input', 'click', Forms.ToggleTemplateWhich);

	Forms.StageWrappers.find('.RightPanel').containedStickyScroll({unstick:false});

	Forms.ActivatePopOver();
	Forms.SyncOrderNumbers();
	Forms.ActivateShowHides();

	Forms.ActivateChoicesEnableVal();
	Forms.StageWrappers.find('.StageFields').delegate('a.AddChoice', 'click', Forms.ChoicesAddOption);
	Forms.StageWrappers.find('.StageFields').delegate('a.RemoveChoice', 'click', Forms.ChoicesRemoveOption);
	Forms.StageWrappers.find('.StageFields').delegate('a.BulkAddChoices', 'click', Forms.ChoicesBulkAdd);
});

//********************************************************************************* //

Forms.AddField = function(FieldName, StageFields, isDBfield, AddOnly){

	// Add the new Field
	StageFields.append(Forms.FormsElemTmpl.render( ((isDBfield == true) ? Forms.JSON.DBFields[FieldName] : Forms.JSON.Fields[FieldName]) ));

	// Are we only adding? Then return!
	if (AddOnly === true) return;

	// Hide the FirstDrop
	StageFields.find('div.FirstDrop').hide();

	// Activate Tooltips
	Forms.ActivatePopOver();

	// Sync Order Numbers
	Forms.SyncOrderNumbers();

	// Trigger ShowHides!
	setTimeout(function(){
		Forms.Fields.find('input.ShowHideSubmitBtn').filter(':checked').click();
		Forms.Fields.find('input.ChoicesEnableVal').filter(':checked').click();
		Forms.Fields.find('table.FormsChoicesTable').each(function(){
			Forms.ChoicesSyncOrderNumbers(jQuery(this));
		});
	}, 300);
};

//********************************************************************************* //

Forms.ParseDBFields = function(){

	// Loop through all the existing fields
	for (var field_id_long in Forms.JSON.DBFields) {

	   // Add new Field
	   Forms.AddField(field_id_long, jQuery('#FormStageWrapper'+Forms.JSON.DBFields[field_id_long].ee_field_id).find('div.StageFields'), true, true);
	}

	// Can we hide the FirstDrop?
	Forms.StageWrappers.find('div.StageFields').each(function(){
		if (jQuery(this).find('div.FormElem').length > 0){
			jQuery(this).parent().find('div.FirstDrop').hide();
		}
	});

	// Activate Tooltips
	Forms.ActivatePopOver();

	// Sync Order Numbers
	Forms.SyncOrderNumbers();

	// Trigger ShowHides!
	setTimeout(function(){
		Forms.Fields.find('input.ShowHideSubmitBtn').filter(':checked').click();
		Forms.Fields.find('input.ChoicesEnableVal').filter(':checked').click();
		Forms.Fields.find('table.FormsChoicesTable').each(function(){
			Forms.ChoicesSyncOrderNumbers(jQuery(this));
		});
	}, 300);

	//delete Forms.JSON.DBFields;
};

//********************************************************************************* //

Forms.ActivateDraggable = function(){

	Forms.StageWrappers.each(function(index, Field){
		jQuery(Field).find('.FormTools .draggable').draggable({
			helper: function(Event){
				return $( "<div class='draggingfield' style='width:175px; height:20px;'>"+jQuery(Event.target).html()+"</div>" );
			},
			cursorAt: { top: 20, left: 150 },
			revert: "invalid",
			containment: Field,
			appendTo : Field,
			connectToSortable:'.FormStage .StageFields'
		});

	});
};

//********************************************************************************* //

Forms.ActivateSortable = function(){

	Forms.StageWrappers.each(function(index, Field){

		jQuery(Field).find('div.StageFields').sortable({
			//handle: '.move',
			cancel: 'div.ElemSettings',
			axis: 'y',
			revert: true,
			stop: function(Event, UI){

				// Is it a draggable?
				if (UI.item.hasClass('draggable') ==  true){

					// Hide forst drop!
					jQuery(Field).find('div.FirstDrop').hide();

					// Replace with the new item
					UI.item.replaceWith(Forms.FormsElemTmpl.render( Forms.JSON.Fields[jQuery(UI.item).attr('rel')] ));
				}

				// Wait a bit before syncing numbers
				setTimeout(function(){
					Forms.SyncOrderNumbers();
					Forms.Fields.find('input.ShowHideSubmitBtn').filter(':checked').click();
					Forms.Fields.find('input.ChoicesEnableVal').filter(':checked').click();
					Forms.Fields.find('table.FormsChoicesTable').each(function(){
						Forms.ChoicesSyncOrderNumbers(jQuery(this));
					});
				}, 300);
			}
		});

	});
};

//********************************************************************************* //

Forms.AddFieldByClick = function(Event){
	Event.preventDefault();

	// Find the firstdrop and hide it
	jQuery(Event.target).closest('div.FormStageWrapper').find('div.FirstDrop').hide();

	var StageFields = jQuery(Event.target).closest('div.FormStageWrapper').find('.StageFields');

	// Add the new Field
	Forms.AddField(jQuery(Event.target).attr('rel'), StageFields);

	// Scroll the page to the newly created page..slowlyyyy
	jQuery('html,body').animate({scrollTop: StageFields.find('div.FormElem:last').offset().top - 40}, 900);

	// Wait and animate the background
	setTimeout(function(){
		StageFields.find('div.FormElem:last').stop().css('background-color', '#FFF6A9').animate({ backgroundColor: 'transparent'}, 2000, null, function(){
			jQuery(this).css({'background-color' : ''});
		});
	}, 300);

	delete StageFields;
};

//********************************************************************************* //

Forms.ActivatePopOver = function(){

	Forms.StageWrappers.closest('div.Forms').find('span.ToolTip').each(function(){
		jQuery(this).popover({
			content: Forms.JSON.Help[jQuery(this).attr('rel')],
			animation: true
		});
	});

};

//********************************************************************************* //

Forms.SyncOrderNumbers = function(){
	Forms.StageWrappers.find('div.StageFields').each(function(){
		jQuery(this).find('.FormElem').each(function(index,elem){
			jQuery(elem).find('input, textarea, select').each(function(){
				var Elem = jQuery(this);
				if (typeof(Elem.attr('name')) == 'undefined') return;
				attr = Elem.attr('name').replace(/\[fields\]\[.*?\]/, '[fields][' + (index+1) + ']');
				Elem.attr('name', attr);
			});
		});

	});
};

//********************************************************************************* //

Forms.DeleteFormElem = function(Event){
	Event.preventDefault();

	jQuery(Event.target).closest('div.FormElem').slideUp('800', function(){

		if ( jQuery(Event.target).closest('.StageFields').find('div.FormElem').length == 1) {
			jQuery(Event.target).closest('.StageFields').find('.FirstDrop').show();
		}

		jQuery(this).remove();

	});

};

//********************************************************************************* //

Forms.ToggleSettingsFormElem = function(Event){
	Event.preventDefault();
	var ElemSettings = jQuery(Event.target).closest('div.FormElem').find('.ElemSettings');
	ElemSettings.slideToggle(600);
	ElemSettings.find('select.multiselect').chosen();
	setTimeout(function(){Forms.SyncOrderNumbers();}, 400);
};

//********************************************************************************* //

Forms.ToggleFieldRequired = function(Event){

	// What is the current value?
	var CurrentlyChecked = jQuery(Event.target).is(':checked');

	// Find the parent and remove or add the "req" CSS Class based on Value
	if (CurrentlyChecked == true){
		jQuery(Event.target).closest('div.FormElem').find('div.inner div.FieldContent').addClass('req');
	}
	else {
		jQuery(Event.target).closest('div.FormElem').find('div.inner div.FieldContent').removeClass('req');
	}

};

//********************************************************************************* //

Forms.SaveElemSettings = function(Event){
	Event.preventDefault();
	var Target = jQuery(Event.target).closest('.FormElem');
	Target.find('.SavingSettings').show();

	var Params = Target.find('.ElemSettings :input, .HiddenVal :input').serializeArray();
	Params.push({name:'ajax_method', value:'save_field_settings'});

	// Add form Settings
	var FormSettings = Target.closest('div.Forms').find('div.FormSettings').find(':input').serializeArray();

	for (key in FormSettings){
		Params.push({name:FormSettings[key]['name'], value:FormSettings[key]['value']});
	}

	jQuery.post(Forms.AJAX_URL, Params, function(rData){
		Target.find('.FieldContent').html(rData);
		Target.find('.SavingSettings').hide();
		Target.find('.ElemSettings').slideUp(600);
	});
};


//********************************************************************************* //

Forms.ToggleTemplateWhich = function(Event){
	var Parent = jQuery(Event.target).closest('.formbar').next();

	Parent.find('fieldset').slideUp().filter('.'+Event.target.value).delay(450).slideDown();
};

//********************************************************************************* //

Forms.ActivateShowHides = function(){

	Forms.Fields.delegate('input.ShowHideSubmitBtn', 'click', function(){
		var Parent = jQuery(this).parent();
		Parent.find('p').hide();
		Parent.find('p.btn_'+jQuery(this).attr('rel')).show();
	});

	setTimeout(function(){
		Forms.Fields.find('input.ShowHideSubmitBtn').filter(':checked').click();
	}, 300);
};

//********************************************************************************* //

Forms.ActivateChoicesEnableVal = function(){

	Forms.Fields.delegate('input.ChoicesEnableVal', 'click', function(){
		var Parent = jQuery(this).closest('tbody');
		Parent.find('.choices_values').hide();

		if (jQuery(this).val() == 'yes') Parent.find('.choices_values').show();
	});

};

//********************************************************************************* //

Forms.ChoicesAddOption = function(Event){
	Event.preventDefault();

	// Make the clone and clear all fields
	var Clone = jQuery(Event.target).closest('tr').clone();
	Clone.find('input[type=text]').val('');
	Clone.find('input[type=radio]').removeAttr('checked');

	// Add it
	jQuery(Event.target).closest('tr').after(Clone);
	Forms.ChoicesSyncOrderNumbers(jQuery(Event.target).closest('table.FormsChoicesTable'));
};

//********************************************************************************* //

Forms.ChoicesRemoveOption = function(Event){
	Event.preventDefault();
	var Parent = jQuery(Event.target).closest('table');

	// Last field? We can't delete it!
	if (Parent.find('tbody tr').length == 1) return false;

	// Kill, with animation
	jQuery(Event.target).closest('tr').fadeOut('fast', function(){
		jQuery(this).remove();
	});
};

//********************************************************************************* //

Forms.ChoicesBulkAdd = function(Event){
	Event.preventDefault();

	jQuery.colorbox({
		href:Forms.AJAX_URL + '&ajax_method=choices_ajax_ui',
		onComplete: function(){

			var Wrapper = jQuery('#FormsChoices');

			// Fill in the Textarea
			Wrapper.find('.left a').click(function(E){
				E.preventDefault();
				jQuery('#FormsChoicesText').html( jQuery(E.target).find('span').html() );
			});

			// Insert Event
			Wrapper.find('.FormsBtn').click(function(E){
				E.preventDefault();

				var Lines = jQuery('#FormsChoicesText').html().split("\n");
				var Choices = {};

				if (Lines.length == 0) {
					return false;
				}

				for (i in Lines) {
					Lines[i] = Lines[i].split(' : ');
					if (typeof(Lines[i][1]) != 'undefined') {
						Choices[ Lines[i][0] ] = Lines[i][1];
					} else Choices[ Lines[i][0] ] = Lines[i][0];
				}

				var Tbody = jQuery(Event.target).closest('table').find('tbody');


				for (Val in Choices){
					Tbody.find('tr:first').clone()
					.find('input[type=radio]').removeAttr('checked').closest('tr')
					.find('td:eq(2)').find('input').val(Val).closest('tr')
					.find('td:eq(1)').find('input').val(Choices[Val]).closest('tr')
					.appendTo(Tbody);
				}

				jQuery.colorbox.close();

				setTimeout(function(){
					Forms.ChoicesSyncOrderNumbers(Tbody.parent());
				}, 500);

			});
		}
	});
};

//********************************************************************************* //

Forms.ChoicesSyncOrderNumbers = function(Wrapper){

	Wrapper.find('tbody tr').each(function(index, TR){

		jQuery(TR).find('input, textarea, select').each(function(i, elem){
			attr = jQuery(elem).attr('name').replace(/\[choices\]\[.*?\]/, '[choices][' + index + ']');
			jQuery(elem).attr('name', attr);
		});

		jQuery(TR).find('tr:first').find('input').attr('value', index);

	});
};

//********************************************************************************* //