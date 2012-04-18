// ********************************************************************************* //
var ChannelImages = ChannelImages ? ChannelImages : new Object();
ChannelImages.prototype = {}; // Get Outline Going
//********************************************************************************* //

// Add :Contains (case-insensitive)
jQuery.expr[':'].Contains = function(a,i,m){
    return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
};


//********************************************************************************* //

$(document).ready(function() {

	ChannelImages.CI_Images = {}; // Images in Field (for WYGWAM)
	ChannelImages.LastError = '';
	ChannelImages.CIField = jQuery('.CIField');
	ChannelImages.Data = JSON.parse(ChannelImages.CIField.find('.CI_Data').val());


	ChannelImages.CIField.find('.StoredImages').click(ChannelImages.OpenStoredImages);
	ChannelImages.CIField.find('.SearchImages .Button').click(ChannelImages.SearchForImages);
	ChannelImages.CIField.find('input[type=text]').keypress(ChannelImages.DisableEnter);
	ChannelImages.CIField.find('.ClearImageSearch').live('click', ChannelImages.ClearImageSearch);

	// Open Error
	ChannelImages.CIField.find('.OpenError').live('click', function(){
		$.colorbox({width:'90%', height:'90%', html:'<pre style="font-size:11px; font-family:helvetica,arial">'+ChannelImages.LastError+'</pre>'});
		return false;
	});

	ChannelImages.CIField.find('.ImageCover').live('click', ChannelImages.TogglePrimaryImage);
	ChannelImages.CIField.find('.ImageDel').live('click', ChannelImages.DeleteImage);
	ChannelImages.CIField.find('.ImageProcessAction').live('click', ChannelImages.OpenPerImageAction);


	ChannelImages.SwfUploadInitialize(); // Initialize SWFUpload

	ChannelImages.SyncOrderNumbers(); // Sync Order Numbers
	if (ChannelImages.CIField.find('.ImgUrl').length > 0) ChannelImages.CIField.find('.ImgUrl').colorbox();

	// Editable
	ChannelImages.CIField.find('.Image').each(function(){
		ChannelImages.ActivateEditable($(this));
	});

	// Calculate Remaining Images!
	ChannelImages.ImagesRemaining();

	// Activate Sortable
	ChannelImages.CIField.find('.AssignedImages').sortable({
		axis: 'y',
		cursor: 'move',
		opacity: 0.6,
		handle: '.ImageMove',
		helper: function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});

			return ui;
		},
		update:ChannelImages.SyncOrderNumbers
	});

	// Cancel Upload
	ChannelImages.CIField.find('.StopUpload').click(function(){
		ChannelImages.CIField.find('.ImageQueue div.File').not('div.Done').each(function(index,elem){
			ChannelImages.SWFUpload.cancelUpload(jQuery(elem).attr('id'), true);
			jQuery(elem).fadeOut(1400, function(){ jQuery(elem).remove(); });
		});
		return false;
	});

	// Submit Entry Stop
	jQuery('#submit_button').click(function(Event){
		if (ChannelImages.CIField.find('.ImageQueue div.Done').length > 0){
			jQuery(Event.target).parent(':first').append('<div class="ChannelImagesSubmitWait">' + ChannelImages.Data.submitwait + '</div>');
			setTimeout(function(){jQuery(Event.target).attr('disabled', 'disabled').css('background', '#DDE2E5');}, 300);
		}
	});


});

//********************************************************************************* //

ChannelImages.OpenStoredImages = function(Event){

	var Target = $(Event.target).closest('.CIField');
	var Parent = Target.find('.SearchImages');

	// Is it hidden already?
	if (Parent.css('display') == 'none'){
		Parent.css('display', '');
	}
	else {
		Parent.css('display', 'none');
		return false;
	}

	// Entry Based?
	if ( Parent.find('.entryfilter').length > 0 ){

		if (Parent.data('event_binded') != true){
			Parent.find('.entryfilter .filter select').change(ChannelImages.StoredImagesLoadEntries);

			// Activate Filter
			Parent.find('.entryfilter .filter input').keyup(function(){
				var filter = $(this).val();
			    if (filter) {
			    	Parent.find('.entryfilter .entries').find("a:not(:Contains(" + filter + "))").slideUp();
			    	Parent.find('.entryfilter .entries').find("a:Contains(" + filter + ")").slideDown();
			    } else {
			    	Parent.find('.entryfilter .entries').find('a').slideDown();
			    }
			});

			// Disable Enter!
			Parent.find('.entryfilter .filter input').keydown(function(event){ if (event.keyCode == 13) return false;  });

			Parent.data('event_binded', true);
		}

		ChannelImages.StoredImagesLoadEntries();
	}
	else {
		if (Parent.data('event_binded') != true){
			Parent.find('.imagefilter .filter select').change(ChannelImages.StoredImagesLoadImages);
			Parent.find('.imagefilter .filter input').keyup(ChannelImages.StoredImagesLoadImages);

			// Disable Enter!
			Parent.find('.imagefilter .filter input').keydown(function(event){ if (event.keyCode == 13) return false;  });

			Parent.data('event_binded', true);
		}
		ChannelImages.StoredImagesLoadImages();
	}


	return false;
};

//********************************************************************************* //

ChannelImages.StoredImagesLoadEntries = function(){

	ChannelImages.CIField.find('.SearchImages .entryfilter .entries a').slideUp('fast', function(){
		jQuery(this).remove();
	});

	ChannelImages.CIField.find('.SearchImages .Loading').show();

	var Params = {};
	Params.ajax_method = 'load_entries';
	Params.field_id = ChannelImages.Data.field_id;
	Params.limit = ChannelImages.CIField.find('.SearchImages .entryfilter .filter select').val();
	Params.entry_id = jQuery('input[name=entry_id]').val();

	jQuery.get(ChannelImages.AJAX_URL, Params, function(rData){
		ChannelImages.CIField.find('.SearchImages .Loading').hide();
		ChannelImages.CIField.find('.SearchImages .entryfilter .entries').prepend(rData).find('a').click(ChannelImages.StoredImagesLoadEntryImages);

	});

};

//********************************************************************************* //

ChannelImages.StoredImagesLoadEntryImages = function(Event){
	Event.preventDefault();

	ChannelImages.CIField.find('.SearchImages .entryimages .NoEntrySelect').hide();
	ChannelImages.CIField.find('.SearchImages .SearchingForImages').show();
	ChannelImages.CIField.find('.SearchImages .entryimages .images div').remove();

	var Params = {};
	Params.ajax_method = 'load_images';
	Params.field_id = ChannelImages.Data.field_id;
	Params.entry_id = jQuery(Event.target).attr('rel');

	jQuery.get(ChannelImages.AJAX_URL, Params, function(rData){
		ChannelImages.CIField.find('.SearchImages .SearchingForImages').hide();
		ChannelImages.CIField.find('.SearchImages .entryimages .images').append(rData);
		ChannelImages.CIField.find('.SearchImages .entryimages .images a').colorbox();
		ChannelImages.CIField.find('.SearchImages .entryimages .images a span.add').click(ChannelImages.AddImage);
	});

};

//********************************************************************************* //

ChannelImages.StoredImagesLoadImages = function(){
	var ImgFilter = ChannelImages.CIField.find('.SearchImages .imagefilter');
	ImgFilter.find('.Loading').show();
	ImgFilter.find('.images div').remove();

	var Params = {};
	Params.ajax_method = 'load_images';
	Params.field_id = ChannelImages.Data.field_id;
	ImgFilter.find('.filter').find('input, select').each(function(){
		Params[$(this).attr('rel')] = $(this).val();
	});

	jQuery.get(ChannelImages.AJAX_URL, Params, function(rData){
		ImgFilter.find('.Loading').hide();
		ImgFilter.find('.images').html(rData);
		ImgFilter.find('.images a').colorbox();
		ImgFilter.find('.images a span.add').click(ChannelImages.AddImage);
	});

};

//********************************************************************************* //

ChannelImages.AddImage = function(Event){

	// Stop Defailt Event Stuff
	Event.preventDefault();
	Event.stopPropagation();

	// How Many Images Remaining?
	if (ChannelImages.ImagesRemaining() < 1){
		alert(ChannelImages.Data.imglimitreached);
		return false;
	}

	var TargetBox = $(Event.target).closest('.CIField');
	var FIELDID = ChannelImages.Data.field_id;

	// Mark it as Added!
	jQuery(Event.target).addClass('Loading');

	var Params = {};
	Params.ajax_method = 'add_linked_image';
	Params.field_id = ChannelImages.Data.field_id;
	Params.image_id = $(this).closest('a').attr('rel');

	// Get Image Details
	jQuery.get(ChannelImages.AJAX_URL, Params, function(rData){

		// Grab my TD
		var Data = jQuery.base64Decode(rData.tr);
		var ImageData = rData.img;

		Data = Data.replace('#REPLACE#',
				"<input name='field_id_"+FIELDID+"[images][0][title]' value='" + ImageData.title + "' class='title'> " +
				"<input name='field_id_"+FIELDID+"[images][0][url_title]' value='" + ImageData.url_title + "' class='url_title'> " +
				"<textarea name='field_id_"+FIELDID+"[images][0][desc]' class='desc'>" + ImageData.description + "</textarea> " +
				"<input name='field_id_"+FIELDID+"[images][0][category]' value='' class='category'> " +
				"<input name='field_id_"+FIELDID+"[images][0][cifield_1]' value='' class='cifield_1'> " +
				"<input name='field_id_"+FIELDID+"[images][0][cifield_2]' value='' class='cifield_2'> " +
				"<input name='field_id_"+FIELDID+"[images][0][cifield_3]' value='' class='cifield_3'> " +
				"<input name='field_id_"+FIELDID+"[images][0][cifield_4]' value='' class='cifield_4'> " +
				"<input name='field_id_"+FIELDID+"[images][0][cifield_5]' value='' class='cifield_5'> " +
				"<input name='field_id_"+FIELDID+"[images][0][imageid]' value='0' class='imageid'> " +
				"<input name='field_id_"+FIELDID+"[images][0][linked_imageid]' value='" + ImageData.image_id_hidden + "' class='linked_imageid'> " +
				"<input name='field_id_"+FIELDID+"[images][0][filename]' value=\"" +ImageData.filename + "\" class='filename'/> " +
				"<input name='field_id_"+FIELDID+"[images][0][cover]' value='0' class='cover'> ");

		TargetBox.find('.AssignedImages').append(Data);
		TargetBox.find('.NoImages').hide();

		// Get the appended div so we can activate stuff
		var Appended = ChannelImages.CIField.find('.AssignedImages .Image:last');
		ChannelImages.ActivateEditable(Appended);

		ChannelImages.SyncOrderNumbers();
		TargetBox.find('.ImgUrl').colorbox();
		ChannelImages.ImagesRemaining();

		jQuery(Event.target).closest('div.img').slideUp('slow', function(){jQuery(this).remove();});
	}, 'json');



};


//********************************************************************************* //

ChannelImages.ClearImageSearch = function(Event){

	var TargetBox = $(Event.target).closest('.CIField');

	TargetBox.find('.ImagesResult').slideToggle('slow', function(){ $(this).empty(); });

	return false;

};

//********************************************************************************* //

ChannelImages.AddQueueImages = function(File) {
	var Remaining = ChannelImages.ImagesRemaining();
	Remaining = (Remaining - ChannelImages.CIField.find('.ImageQueue .Queued').length);

	if (Remaining > 0){
		var File = jQuery('<div class="File Queued" id="' + File.id + '">' + File.name + '</div>'); //' <a href="#" class="DeleteIcon">&nbsp;</a><a href="#" class="EditIcon">&nbsp;</a></div>');
		ChannelImages.CIField.find('.ImageQueue').css('display', 'table-row').children('th').append(File);
	}
	else {
		ChannelImages.SWFUpload.cancelUpload(File.id, false);
		return false;
	}
};

//********************************************************************************* //

/**
* SwfUpload File Dialog Complete
* @param {Object} FilesSelected - number of files selected
* @param {Object} FilesQueued - number of files queued
* @param {Object} TotalFilesQueued - absolute total number of files in the queued
* @return {Void}
*/
ChannelImages.FileDialogClosed = function(FilesSelected, FilesQueued, TotalFilesQueued){
	ChannelImages.ProgressBox = ChannelImages.CIField.find('.UploadProgress'); // Cache the Progress Handler
	ChannelImages.ProgressBox.show();
	// ChannelImages.PBFBoxProgress.children('.progress').css('width', '100%'); // For now (no progress on resize upload)
	//ChannelImages.CIField.find('.TopBar .Files .File').click(); // IE is slow with parsing. clicking it makes it parse!

	ChannelImages.UploadStart();
};

//********************************************************************************* //

ChannelImages.UploadStart = function(){

	ChannelImages.UploadError = false;

	// Grab the next queued item
	ChannelImages.CurrentFile = ChannelImages.CIField.find('.ImageQueue .Queued:first');

	// Grab our Identifier
	ChannelImages.Identifier = ChannelImages.CurrentFile.attr('id');

	// If Identifier is undefined then there are no more to upload
	if (typeof ChannelImages.Identifier == 'undefined') return false;

	// Shoot!
	ChannelImages.SWFUpload.startUpload(ChannelImages.Identifier);

	return false;
};

//********************************************************************************* //

/**
 * SwfUpload Upload Start Handler
 * @param {Object} file
 * @return {Void}
 */
ChannelImages.UploadInit = function(File){

	// Grab the next queued item
	ChannelImages.CurrentFile = $('#' + File.id);

	if (ChannelImages.UploadError == true) return false;

	// Add the UploadingClass
	ChannelImages.CurrentFile.removeClass('Queued').addClass('Uploading');

	ChannelImages.CIField.find('.StopUpload').show();

	ChannelImages.ProgressBox.show();

	ChannelImages.LastError = '';

	// Set the progress bar to 0 again
	//ChannelImages.ProgressBox.children('.progress').css('width', '0%');

	//ChannelImages.PBFBoxProgress.find('.progress span strong').html('Uploading Original File');
	//ChannelImages.Debug('Uploading Original File (ID:' + ChannelImages.Identifier + ') ');
};

//********************************************************************************* //

/**
 * SwfUpload File Progress Handler
 * @param {Object} file
 * @param {Object} bytesLoaded
 * @param {Object} bytesTotal
 * @return {Void}
 */
ChannelImages.UploadProgress = function(file, bytesLoaded, bytesTotal){
	ChannelImages.ProgressBox.children('.progress').css('width', file.percentUploaded+'%');

	ChannelImages.ProgressBox.find('.percent').html(SWFUpload.speed.formatPercent(file.percentUploaded));
	ChannelImages.ProgressBox.find('.speed').html(SWFUpload.speed.formatBPS(file.averageSpeed / 10));
	ChannelImages.ProgressBox.find('.bytes .uploadedBytes').html(SWFUpload.speed.formatBytes(file.sizeUploaded));
	ChannelImages.ProgressBox.find('.bytes .totalBytes').html('/ ' + SWFUpload.speed.formatBytes(file.size));

	//SWFUpload.speed.formatPercent(file.percentUploaded)
	//console.log(SWFUpload.speed.formatBPS(file.averageSpeed));
	//SWFUpload.speed.formatBPS(file.currentSpeed);
	//SWFUpload.speed.formatBPS(file.averageSpeed);
	//SWFUpload.speed.formatBPS(file.movingAverageSpeed);
	//SWFUpload.speed.formatTime(file.timeRemaining);
	//SWFUpload.speed.formatTime(file.timeElapsed);
	//SWFUpload.speed.formatPercent(file.percentUploaded);
	//SWFUpload.speed.formatBytes(file.sizeUploaded);
};

//********************************************************************************* //

/**
 * File Upload Success Handler
 * @param {Object} file object
 * @param {Object} server data
 * @param {Object} received response
 * @return {Void}
 */
ChannelImages.UploadResponse = function(file, serverData, response){

	// If evalJSON fails, probally a php error occured
	try {
		rData = JSON.parse(serverData);
		//ChannelImages.Debug('Received server response. (correctly parsed as JSON)');
		//console.log(serverData);
	}
	catch(errorThrown) {
		ChannelImages.LastError = serverData;
		ChannelImages.ErrorMSG('Server response was not as expected, probably a PHP error. <a href="#" class="OpenError">OPEN ERROR</a>');
		ChannelImages.Debug("Server response was not as expected, probably a PHP error. \n RETURNED RESPONSE: \n" + serverData);
		ChannelImages.CurrentFile.removeClass('Uploading').addClass('Error');
		delete ChannelImages.CurrentFile;
		delete ChannelImages.Identifier;
		return false;
	}

	// Parse the server data
	ChannelImages.UploadSuccess(rData);

	return;
};

//********************************************************************************* //

ChannelImages.UploadSuccess = function(rData){

	if (rData.success == 'yes') {

		// Mark it as done
		ChannelImages.CurrentFile.removeClass('Uploading').addClass('Done');

		var TempCurrentFile = ChannelImages.CurrentFile;

		// Hide it?
		setTimeout(function(){
			if (TempCurrentFile.hasClass('Done') == false) return;
			TempCurrentFile.slideUp('slow');
		}, 2000);

		rData.body = rData.body.replace('#REPLACE#',
				"<input name='field_id_"+rData.field_id+"[images][0][title]' value='" + rData.title + "' class='title'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][url_title]' value='" + rData.url_title + "' class='url_title'> " +
				"<textarea name='field_id_"+rData.field_id+"[images][0][desc]' class='desc'></textarea> " +
				"<input name='field_id_"+rData.field_id+"[images][0][category]' value='' class='category'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][cifield_1]' value='' class='cifield_1'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][cifield_2]' value='' class='cifield_2'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][cifield_3]' value='' class='cifield_3'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][cifield_4]' value='' class='cifield_4'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][cifield_5]' value='' class='cifield_5'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][imageid]' value='0' class='imageid'> " +
				"<input name='field_id_"+rData.field_id+"[images][0][filename]' value=\"" + rData.filename + "\" class='filename'/> " +
				"<input name='field_id_"+rData.field_id+"[images][0][cover]' value='0' class='cover'> ");
			//console.log(rData.body);
		ChannelImages.CIField.find('.AssignedImages').append(rData.body);
		ChannelImages.CIField.find('.AssignedImages .NoImages').hide();

		// Get the appended div so we can activate stuff
		var Appended = ChannelImages.CIField.find('.AssignedImages .Image:last');
		ChannelImages.ActivateEditable(Appended);

		ChannelImages.SyncOrderNumbers();
		ChannelImages.CIField.find('.ImgUrl').colorbox();
	}
	else {
		ChannelImages.CurrentFile.removeClass('Uploading').addClass('Error');
		ChannelImages.ErrorMSG(rData.body);
		ChannelImages.Debug('ERROR: ' + rData.body);
		delete ChannelImages.Identifier;
		//delete ChannelImages.CurrentFile;

	}

	ChannelImages.CIField.find('.StopUpload').hide();
	ChannelImages.ImagesRemaining();

	// Hide the progressbox! when done
	if (ChannelImages.CIField.find('.ImageQueue .Queued:first').length < 1) ChannelImages.ProgressBox.css('display', 'none');
};

//********************************************************************************* //

ChannelImages.UploadFailed = function(file, error, message){
	// Sometimes we cancel the upload because of an error, no need to display "Cancelled error"
	if (error == '-270') return;

	ChannelImages.CurrentFile.removeClass('Uploading').addClass('Error');
	ChannelImages.ErrorMSG('Upload Failed:' + error + ' MSG:' + message);
	ChannelImages.Debug('Upload Failed:' + error + ' MSG:' + message);
	ChannelImages.CIField.find('.StopUpload').hide();
};

//********************************************************************************* //

ChannelImages.SwfUploadInitialize = function(){

	var ButtonWith = 120;
	if (jQuery('#ChannelImagesSelect').is(':visible') != false){
		ButtonWith = (ChannelImages.CIField.find('th.top_actions .block:first').width() - 10);
	}

	ChannelImages.SWFUpload = new SWFUpload({

		// Backend Settings
		flash_url : ChannelImages.ThemeURL + 'swfupload.swf',
		upload_url: ChannelImages.AJAX_URL,
		post_params: {
			XID: EE.XID,
			ajax_method: 'upload_file',
			key: ChannelImages.Data.key,
			field_id: ChannelImages.Data.field_id,
			site_id: ChannelImages.Data.site_id
		},
		file_post_name: 'channel_images_file',
		prevent_swf_caching: false,
		assume_success_timeout: 0,
		// debug: true,

		// File Upload Settings
		file_size_limit : 0,
		file_types : '*.jpg;*.jpeg;*.png;*.gif',
		file_types_description : 'Images',
		file_upload_limit : 0,
		file_queue_limit : 0,

		// Event Handler Settings
		swfupload_preload_handler : function(){},
		swfupload_load_failed_handler : function(){},
		file_dialog_start_handler : function(){},
		file_queued_handler : ChannelImages.AddQueueImages,
		file_queue_error_handler : function(){},
		file_dialog_complete_handler : ChannelImages.FileDialogClosed,
		upload_resize_start_handler : function(){},
		upload_start_handler : ChannelImages.UploadInit,
		upload_progress_handler : ChannelImages.UploadProgress,
		upload_error_handler : ChannelImages.UploadFailed,
		upload_success_handler : ChannelImages.UploadResponse,
		upload_complete_handler : function(){},


		// Button Settings
		button_image_url : '', // Relative to the SWF file
		button_placeholder_id : 'ChannelImagesSelect',
		button_width: ButtonWith,
		button_height: 16,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,
		button_action: SWFUpload.BUTTON_ACTION.SELECT_FILES, //SWFUpload.BUTTON_ACTION.SELECT_FILE for single files

		// Debug Settings
		debug: false

	});

};

//********************************************************************************* //

ChannelImages.TogglePrimaryImage = function(Event){

	var Parent = jQuery(this).closest('tr');

	// Do we have already one selected?
	var SelectedCover = false;
	if ( ChannelImages.CIField.find('.StarIcon').attr('rel') == jQuery(this).attr('rel') ) SelectedCover = true;

	// Find all images and remove the StarClass & Cover Value
	ChannelImages.CIField.find('.Image').each(function(){
		jQuery(this).removeClass('PrimaryImage')
		.find('.StarIcon').removeClass('StarIcon').addClass('StarGreyIcon')
		.closest('.Image').find('.inputs .cover').attr('value', '0');
	});

	if (SelectedCover == true) return false;

	// Add the star status to the clicked image
	Parent.addClass('PrimaryImage').find('.StarGreyIcon').removeClass('StarGreyIcon').addClass('StarIcon')
	.closest('.Image').find('.inputs .cover').attr('value', '1');

	return false;
};

//********************************************************************************* //

ChannelImages.DeleteImage = function(Event){

	if ( $(Event.target).hasClass('ImageLinked') == true){
		confirm_delete = confirm('Are you sure you want to unlink this image?');
		if (confirm_delete == false) return false;
	}
	else {
		confirm_delete = confirm('Are you sure you want to delete this image?');
		if (confirm_delete == false) return false;
	}


	var PostParams = {XID: EE.XID, ajax_method:'delete_image'};
	PostParams.entry_id = jQuery('input[name=entry_id]').val();
	PostParams.site_id = ChannelImages.Data.site_id;
	PostParams.field_id = ChannelImages.Data.field_id;
	PostParams.key = ChannelImages.Data.key;

	var ItemObj = jQuery(this).closest('.Image');
	PostParams.image_id = ItemObj.find('.imageid').val();
	PostParams.filename = ItemObj.find('.filename').val();

	jQuery.post(ChannelImages.AJAX_URL, PostParams);

	ItemObj.fadeOut('slow', function(){ jQuery(this).remove(); ChannelImages.ImagesRemaining(); ChannelImages.SyncOrderNumbers(); } );


	return false;
};

//********************************************************************************* //

ChannelImages.OpenPerImageAction = function(Event){
	Event.preventDefault();

	var PostParams = {XID: EE.XID, ajax_method:'apply_action'};
	PostParams.entry_id = jQuery('input[name=entry_id]').val();
	PostParams.site_id = ChannelImages.Data.site_id;
	PostParams.field_id = ChannelImages.Data.field_id;
	PostParams.key = ChannelImages.Data.key;

	var ItemObj = jQuery(Event.target).closest('.Image');
	PostParams.image_id = ItemObj.find('.imageid').val();
	PostParams.filename = ItemObj.find('.filename').val();

	jQuery.colorbox({
		innerWidth:550,
		innerHeight:400,
		html:jQuery.base64Decode(ChannelImages.CIField.find('.PerImageActionHolder').text()),
		onComplete: function(){
			var Wrapper = jQuery('#cboxContent');

			Wrapper.find('.SelectAction').change(function(e){
				if (jQuery(e.target).val() == false) return;

				var Content = jQuery.base64Decode( Wrapper.find('.ActionSettings .'+jQuery(e.target).val()).text() ); // Decode Text
				Wrapper.find('.ActionHolder').html(Content).find('.ChannelImagesTable').css('width', '100%');
				setTimeout(function(){
					Wrapper.find('.ActionHolder').find('input,select,textarea').each(function(index,elem){
						attr = jQuery(elem).attr('name').replace(/\[action_groups\]\[.*?\]\[actions\]/, '');
						jQuery(elem).attr('name', attr);
					});
				}, 200);
			});

			Wrapper.find('.PreviewImage').click(function(e){
				e.preventDefault();

				if (Wrapper.find('.SelectAction').val() == false) return;
				Wrapper.find('.ApplyingAction').show();
				Wrapper.find('.PreviewHolder').empty();

				$.colorbox.resize({height:'80%', width:'60%'});
				PostParams.stage = 'preview';
				PostParams.size = Wrapper.find('.ImageSizes input:checked').val();
				PostParams.action = Wrapper.find('.SelectAction').val();

				Wrapper.find('.ActionHolder').find('input,select,textarea').each(function(index, elem){
					PostParams[$(elem).attr('name')] = $(elem).val();
				});

				jQuery.post(ChannelImages.AJAX_URL, PostParams, function(rData){
					Wrapper.find('.ApplyingAction').hide();
					Wrapper.find('.PreviewHolder').html(rData);
				});
			});

			Wrapper.find('.SaveImage').click(function(e){
				e.preventDefault();

				if (Wrapper.find('.SelectAction').val() == false) return;
				Wrapper.find('.ApplyingAction').show();

				PostParams.stage = 'save';
				PostParams.size = Wrapper.find('.ImageSizes input:checked').val();
				PostParams.action = Wrapper.find('.SelectAction').val();

				Wrapper.find('.ActionHolder').find('input,select,textarea').each(function(index, elem){
					PostParams[$(elem).attr('name')] = $(elem).val();
				});

				jQuery.post(ChannelImages.AJAX_URL, PostParams, function(rData){

					Wrapper.find('.ApplyingAction').hide();
					$.colorbox.close();
				});
			});
		}
	});


};

//********************************************************************************* //

ChannelImages.EditImageDetails = function(value, settings){
	InputClass = jQuery(this).attr('rel');

	if (InputClass == 'desc'){
		jQuery(this).closest('.Image').find('.inputs .' + InputClass).html(value);
	}
	else { //if (InputClass == 'title') {
		jQuery(this).closest('.Image').find('.inputs .' + InputClass).attr('value', value.replace("'", "\'"));
	}

	if (InputClass == 'title') ChannelImages.SyncOrderNumbers();

	return value;
};

//********************************************************************************* //

ChannelImages.SyncOrderNumbers = function(){
	ChannelImages.CIField.find('.Image').each(function(index){
		jQuery(this).find('td.num').html(index+1);

		jQuery(this).find('.ImageCover').attr('rel', 'CI_' + (index+1));

		jQuery(this).find('input, textarea, select').each(function(){
			attr = jQuery(this).attr('name').replace(/\[images\]\[.*?\]/, '[images][' + (index+1) + ']');
			jQuery(this).attr('name', attr);
		});

		var Filename = jQuery(this).find('.filename').val();
		var Alt = jQuery(this).find('.title').val();

		// FOr WYGWAM
		ChannelImages.CI_Images[index] = {url:$(this).find('.ImgUrl img').attr('src'), filename:Filename, alt:Alt};
	});

	ChannelImages.CIField.find('.Image').removeClass('odd');
	ChannelImages.CIField.find('.Image:odd').addClass('odd');
};

//********************************************************************************* //

ChannelImages.ActivateEditable = function(TargetTR){

	var jEvent = ChannelImages.Data.jeditable_event;
	var jText = ChannelImages.Data[jEvent+'2edit'];

	TargetTR.find('td[rel=title]').editable(ChannelImages.EditImageDetails,{type:'text', placeholder: jText, onedit:ChannelImages.ActivateLiveUrlTitle, event: jEvent, onblur: 'submit'});
	TargetTR.find('td[rel=url_title]').editable(ChannelImages.EditImageDetails, {type:'text', placeholder: jText, event: jEvent, onblur: 'submit'});
	TargetTR.find('td[rel=desc]').editable(ChannelImages.EditImageDetails, {type:'textarea', placeholder: jText, event: jEvent, onblur:'submit'});
	TargetTR.find('td[rel=category]').editable(ChannelImages.EditImageDetails, {type:'select', data: ChannelImages.Categories, placeholder: jText, event: jEvent, onblur:'submit'});
	TargetTR.find('td[rel=cifield_1]').editable(ChannelImages.EditImageDetails, {type:'text', placeholder: jText, event: jEvent, onblur:'submit'});
	TargetTR.find('td[rel=cifield_2]').editable(ChannelImages.EditImageDetails, {type:'text', placeholder: jText, event: jEvent, onblur:'submit'});
	TargetTR.find('td[rel=cifield_3]').editable(ChannelImages.EditImageDetails, {type:'text', placeholder: jText, event: jEvent, onblur:'submit'});
	TargetTR.find('td[rel=cifield_4]').editable(ChannelImages.EditImageDetails, {type:'text', placeholder: jText, event: jEvent, onblur:'submit'});
	TargetTR.find('td[rel=cifield_5]').editable(ChannelImages.EditImageDetails, {type:'text', placeholder: jText, event: jEvent, onblur:'submit'});
};

//********************************************************************************* //

ChannelImages.ActivateLiveUrlTitle = function(options, parentTD){
	var parentTR = $(parentTD).closest('.Image');
	setTimeout(function(){
		$(parentTD).find('input[name=value]').liveUrlTitle(parentTR.find('td[rel=url_title]'), {separator: EE.publish.word_separator});
		$(parentTD).find('input[name=value]').liveUrlTitle(parentTR.find('.inputs .url_title'), {separator: EE.publish.word_separator});
	}, 500);
};

//********************************************************************************* //

ChannelImages.ImagesRemaining = function(){
	var TotalImages = ChannelImages.CIField.find('.Image').length;
	var ImageLimit = ChannelImages.Data.image_limit;
	var ImageRemaining = (ImageLimit - TotalImages);

	var RemainingColor = (ImageRemaining > 0) ? 'green' : 'red';

	ChannelImages.CIField.find('.ImageLimit .remain').css('color', RemainingColor).text(ImageRemaining);

	return ImageRemaining;
};

//********************************************************************************* //

ChannelImages.ErrorMSG = function (Msg){
	ChannelImages.UploadError = true;
	ChannelImages.ProgressBox.find('.percent').html('<span style="color:brown; font-weight:bold;">' + Msg + '</span>');
	ChannelImages.ProgressBox.find('.speed, .uploadedBytes, .totalBytes').empty();
};

//********************************************************************************* //

ChannelImages.Debug = function(msg){
	try {
		console.log(msg);
	}
	catch (e) {	}
};

//********************************************************************************* //