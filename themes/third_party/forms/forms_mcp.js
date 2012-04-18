// ********************************************************************************* //
var Forms = Forms ? Forms : new Object();
// ********************************************************************************* //
var testinput = document.createElement('input');
$.extend($.support, { placeholder: !!('placeholder' in testinput) });
//********************************************************************************* //

jQuery(document).ready(function(){

	Forms.fBody = jQuery('#fbody');
	Forms.PlaceHolderFix();
	Forms.fBody.find("select.chzn-select").chosen();
	Forms.fBody.find('.tooltips').tooltip();

	//----------------------------------------
	// Activate Alert
	//----------------------------------------
	Forms.fBody.delegate('a.dd-alert', 'click', Forms.ActivateAlert);

	//----------------------------------------
	// Submissions Page
	//----------------------------------------
	if (document.getElementById('SubmissionsDT') != null){
		Forms.SubmissionsDT();

		Forms.fBody.find('.leftmenu .datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			onSelect: function(){
				Forms.SubmissionsDatatable.fnDraw();
			},
			changeMonth: true,
			changeYear: true
		});

		Forms.fBody.find('.leftmenu .formfilter select').change(function(){ Forms.SubmissionsDatatable.fnDraw(); });
		Forms.fBody.find('.leftmenu .countryfilter select').change(function(){ Forms.SubmissionsDatatable.fnDraw(); });
		Forms.fBody.find('select.chzn-select').chosen().change(function(){
			Forms.SubmissionsDatatable.fnDraw();
		});
	}

	//----------------------------------------
	// Forms Page
	//----------------------------------------
	if (document.getElementById('FormsDT') != null){
		Forms.FormsDT();
	}

	//----------------------------------------
	// New Forms Page
	//----------------------------------------
	if (document.getElementById('NewForm') != null){
		Forms.Fields = jQuery('.Forms');
		Forms.Fields.tabs();
	}


	//----------------------------------------
	// Templates Page
	//----------------------------------------
	if (document.getElementById('TemplatesDT') != null){
		Forms.TemplatesDT();
	}

	if (document.getElementById('TemplatesForm') != null){

		var TTSelect = jQuery('#TemplatesForm').find('.template_type select');
		TTSelect.change(function(){
			var val = TTSelect.val();
			jQuery('#TemplatesForm').find('.admin_only, .user_only').hide('fast', function(){
				jQuery('#TemplatesForm').find('.'+val+'_only').show();
			});
		});
		TTSelect.trigger('change');
	}

	//----------------------------------------
	// Form Entries
	//----------------------------------------
	if (document.getElementById('EntriesDT') != null){
		// Activate DataTable
		Forms.FormsEntriesDT();

		// Activate Column Toggler
		Forms.fBody.find('.FieldWrapper').delegate('button', 'click', Forms.ToggleColVisFEntriesDT);

		// Activate DisplayLength Select
		Forms.fBody.find('.DisplaySelect select').change(function(){
			var oSettings = Forms.FormsEntriesDatatable.fnSettings();
	        oSettings._iDisplayLength = jQuery(this).val();
	        Forms.FormsEntriesDatatable.fnDraw();
		});

		// Activate All Multiselect
		Forms.fBody.find('.FilterWrapper select[multiple]').each(function(index, elem){

			jQuery(elem).change(function(){
				Forms.FormsEntriesDatatable.fnDraw();
			});

		});

		// Activate Datepickers
		Forms.fBody.find('.FilterWrapper .datepicker').datepicker({
			dateFormat: 'yy-mm-dd',
			onSelect: function(){
				Forms.FormsEntriesDatatable.fnDraw();
			},
			changeMonth: true,
			changeYear: true
		});

		// Activate Export Buttons
		Forms.fBody.find('.ExportWrapper button').click(Forms.OpenEntriesExport);
	}

	// Open Form Entry
	Forms.fBody.find('.OpenFentry').live('click', Forms.OpenFormEntry);


});

//********************************************************************************* //

Forms.ActivateAlert = function(Event){

	var answer = confirm( Forms.JSON.Alerts[ jQuery(Event.target).data('alert') ] )
	if (! answer) return false;
};

//********************************************************************************* //

Forms.PlaceHolderFix = function(){

	if(!jQuery.support.placeholder) {
		Forms.fBody.find('input[placeholder]').each(function() {
	        var placeholder = $(this).attr("placeholder");

	        $(this).val(placeholder).focus(function() {
	            if($(this).val() == placeholder) {
	                $(this).val("")
	            }
	        }).blur(function() {
	            if($(this).val() == "") {
	                $(this).val(placeholder)
	            }
	        });
	    });
	}
};

//********************************************************************************* //

Forms.SubmissionsDT = function(){
	Forms.SubmissionsDatatable = jQuery('#SubmissionsDT table').dataTable({
		sPaginationType: 'full_numbers',
		sDom: '<"toptable"l>t<"bottomtable" ip>',
		sAjaxSource: Forms.AJAX_URL,
		fnServerData: function ( sSource, aoData, fnCallback ) {
			aoData.push( {name: 'ajax_method', value: 'submissions_dt' } );
			aoData.push( {name: 'ee_base', value: EE.BASE} );
			aoData.push( {name: 'mcp_base', value: Forms.MCP_BASE} );

			var Filters = jQuery('#fbody .leftmenu').find(':input').serializeArray();
			for (var attrname in Filters) {
				aoData.push( {name: Filters[attrname]['name'], value:Filters[attrname]['value'], } );
			}

			jQuery.ajax({dataType:'json', type:'POST', url:sSource, data:aoData, success:fnCallback});
		},
		bServerSide: true
	});
};

//********************************************************************************* //

Forms.FormsDT = function(){
	Forms.FormsDatatable = jQuery('#FormsDT table').dataTable({
		sPaginationType: 'full_numbers',
		sDom: '<"toptable"l>t<"bottomtable" ip>',
		sAjaxSource: Forms.AJAX_URL,
		fnServerData: function ( sSource, aoData, fnCallback ) {
			aoData.push( {name: 'ajax_method', value: 'forms_dt' } );
			aoData.push( {name: 'ee_base', value: EE.BASE} );
			aoData.push( {name: 'mcp_base', value: Forms.MCP_BASE} );

			jQuery.ajax({dataType:'json', type:'POST', url:sSource, data:aoData, success:function(rData){
				fnCallback(rData);
				Forms.FormsDatatable.fnAdjustColumnSizing(false);
				jQuery('#FormsDT').find('.tooltips').tooltip();
			}});


		},
		bServerSide: true
	});
};

//********************************************************************************* //

Forms.TemplatesDT = function(){
	jQuery('#TemplatesDT table').dataTable({
		sPaginationType: 'full_numbers',
		sDom: '<"toptable"l>t<"bottomtable" ip>',
		sAjaxSource: Forms.AJAX_URL,
		fnServerData: function ( sSource, aoData, fnCallback ) {
			aoData.push( {name: 'ajax_method', value: 'email_templates_dt' } );
			aoData.push( {name: 'ee_base', value: EE.BASE} );
			aoData.push( {name: 'mcp_base', value: Forms.MCP_BASE} );

			jQuery.ajax({dataType:'json', type:'POST', url:sSource, data:aoData, success:fnCallback});
		},
		bServerSide: true
	});
};

//********************************************************************************* //

Forms.FormsEntriesDT = function(){
	Forms.FormsEntriesDatatable = jQuery('#EntriesDT table').dataTable({
		//bStateSave: true,
		sPaginationType: 'full_numbers',
		sDom: 'Rt<"bottomtable" ip>',
		sAjaxSource: Forms.AJAX_URL,
		fnServerData: function ( sSource, aoData, fnCallback ) {
			jQuery('#LoadingDT').show();
			aoData.push( {name: 'ajax_method', value: 'forms_entries_dt' } );
			aoData.push( {name: 'ee_base', value: EE.BASE} );
			aoData.push( {name: 'mcp_base', value: Forms.MCP_BASE} );
			aoData.push( {name: 'form_id', value: FormsDTData.form_id} );

			// Loop over all rows to check the already checked ones
			jQuery.each(jQuery('#EntriesDT table').dataTable().fnSettings().aoColumns, function(index, elem){
				if (elem.bVisible == true) aoData.push( {name: 'visible_cols[]', value: elem.sName} );
			});

			// Add all filters to the POST
			var Filters = Forms.fBody.find('.FilterWrapper').find(':input').serializeArray();
			for (var attrname in Filters) {
				aoData.push( {name: Filters[attrname]['name'], value:Filters[attrname]['value'], } );
			}

			jQuery.ajax({dataType:'json', type:'POST', url:sSource, data:aoData, success:fnCallback});
		},
		fnDrawCallback: function(){
			jQuery('#LoadingDT').hide();
			// Remove all classes
			var FieldWrapper = jQuery('#fbody').find('.FieldWrapper');
			FieldWrapper.find('button').removeClass('active');

			// Loop over all rows to check the already checked ones
			if ( typeof(Forms.FormsEntriesDatatable.fnSettings().aoData[0]) != 'undefined'){
				jQuery.each(Forms.FormsEntriesDatatable.fnSettings().aoColumns, function(index, elem){
					if (elem.bVisible == true)  FieldWrapper.find('button:eq('+(index-1)+')').addClass('active');
				});
			}

		},
		aoColumns:FormsDTCols,
		bServerSide: true,
		oColReorder: {iFixedColumns: 1},
		oLanguage: {
			sLengthMenu: 'Display <select>'+
				'<option value="10">10</option>'+
				'<option value="15">15</option>'+
				'<option value="20">20</option>'+
				'<option value="25">25</option>'+
				'<option value="50">50</option>'+
				'<option value="75">75</option>'+
				'<option value="100">100</option>'+
				'<option value="-1">All</option>'+
				'</select> records'
		}
	});
};

//********************************************************************************* //

Forms.ToggleColVisFEntriesDT = function(Event){
	var Target = jQuery(Event.target);

	if ( typeof(Forms.FormsEntriesDatatable.fnSettings().aoData[0]) != 'undefined'){
		jQuery.each(Forms.FormsEntriesDatatable.fnSettings().aoColumns, function(index, elem){
			if (Target.attr('rel') == elem.sName){
				if (Target.hasClass('active') == true) {
					Forms.FormsEntriesDatatable.fnSetColumnVis(index, false);
					Target.removeClass('active');
				}
				else {
					Forms.FormsEntriesDatatable.fnSetColumnVis(index, true);
					Target.addClass('active');
				}
			}
		});
	}
	//oTable
	return false;
};

//********************************************************************************* //

Forms.OpenEntriesExport = function(Event){

	var Section = jQuery(this).attr('rel');

	jQuery.colorbox({
		html:jQuery('#FormsExportDialogWrapper').html(),
		innerWidth: '50%',
		onComplete: function(){
			var CBOX = jQuery('#cboxContent');

			// Add the DIV ID so CSS Applies & Hide the other sections
			CBOX.find('.FormsExportDialog').attr('id', 'FormsExportDialog');
			CBOX.find('.sectionwrapper').hide().filter('.'+Section).show();

			jQuery.colorbox.resize();

			// ExportButton CLICK
			CBOX.find('.ExportButton').click(function(Event){
				Event.preventDefault();

				var Form = jQuery(Event.target).closest('form');
				CBOX.find('.LoadingExport').show();

				Form.find('.hidden_fields').empty();

				// Visible Cols
				var VisCols = '';
				jQuery.each(Forms.FormsEntriesDatatable.fnSettings().aoColumns, function(index, elem){
					if (elem.bVisible == true) VisCols += '<input name="export[visible_cols][]" type="hidden" value="'+elem.sName+'"/>';
				});

				// Current Entries
				var CurrentEntries = '';
				jQuery.each(Forms.FormsEntriesDatatable.fnSettings().aoData, function(index, elem){
					CurrentEntries += '<input name="export[current_entries][]" type="hidden" value="'+elem._aData.id+'"/>';
				});

				// Add hidden fields
				Form.find('.hidden_fields').html(VisCols + CurrentEntries);

				Form.attr('action', Forms.AJAX_URL + '&ajax_method=export_entries');
				//Form.attr('target', '_blank');
				Form.submit();

				setTimeout(function(){ CBOX.find('.LoadingExport').hide(); }, 2000);
			});
		}
	});

	return false;
};

//********************************************************************************* //

Forms.OpenFormEntry = function(Event){
	Event.preventDefault();

	jQuery.colorbox({
		href:Forms.AJAX_URL,
		data: {ajax_method: 'show_form_entry', fentry_id: jQuery(Event.target).text()},
		width: '80%',
		height: '80%'
	});

};

//********************************************************************************* //