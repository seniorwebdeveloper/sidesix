// ********************************************************************************* //
var ChannelVideos = ChannelVideos ? ChannelVideos : new Object();
ChannelVideos.prototype = {};
ChannelVideos.Data = new Object();
//********************************************************************************* //

$(document).ready(function() {
	
	var CVField = $('.CVField');	
	CVField.find('.SearchVideos').click(ChannelVideos.ToggleSearchVideos);
	CVField.find('.SearchVideos input[type=text]').keypress(ChannelVideos.DisableEnter);
	CVField.find('.SVWrapper .Button').click(ChannelVideos.SearchForVideos);
	CVField.find('.SubmitVideoUrl').click(ChannelVideos.SubmitVideoUrl);
	CVField.find('.AssignedVideos').sortable({
		cursor: 'move', opacity: 0.6, handle: '.MoveVideo', update:ChannelVideos.SyncOrderNumbers,
		helper: function(event, ui){
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		},
		forcePlaceholderSize: true,
		start: function(event, ui){
			ui.placeholder.html('<td colspan="20"></td>');
		},
		placeholder: 'cvideo-reorder-state-highlight'
	});
	CVField.find('.DelVideo').live('click', ChannelVideos.DelVideo);
	CVField.find('.ClearVideoSearch').live('click', ChannelVideos.ClearVideoSearch);
	
	CVField.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});
	
	/*	
	//CVField.find('.RefreshVideo').live('click', ChannelVideos.RefreshVideo);	
	*/
});

//********************************************************************************* //

ChannelVideos.ToggleSearchVideos = function(event){
	var Target = $(event.target).closest('.CVTable').find('.SVWrapperTR').toggle();	
	
	return false;
};

//********************************************************************************* //

ChannelVideos.DisableEnter = function(Event){
	if (Event.which == 13)	{
		jQuery(Event.target).closest('.SVWrapper').find('.Button').click();		
		return false;
	}
};

//********************************************************************************* //

ChannelVideos.SearchForVideos = function(Event){

	var TargetBox = $(Event.target).closest('.CVTable');
	
	// Show Loading Icon
	TargetBox.find('.LoadingVideos').show();
	
	// Params
	var Params = new Object();
	Params.field_id = TargetBox.closest('.CVField').attr('rel');
	Params.ajax_method = 'search_videos';
	Params.XID = ChannelVideos.XID;
	
	// Grab all input fields
	TargetBox.find('.SVWrapper .cvsearch').find('input[type=text], input[type=hidden]').each(function(){
		Params[jQuery(this).attr('rel')] = jQuery(this).val();
	});
	
	TargetBox.find('.VideosResults').empty();
	
	jQuery.ajax({
		url: ChannelVideos.AJAX_URL, type: 'POST', data: Params, dataType: 'json',
		success: function(rData){
			
			var html = '';
			for (var key in rData.services) {
				if (rData.services.hasOwnProperty(key)) {
					html += '<h6>' + key.replace(/^\w/, function($0) { return $0.toUpperCase(); }) + ' <a href="#" class="ClearVideoSearch">'+ChannelVideos.LANG.clear_search+'</a></h6>';
					html += '<div class="vids">';
					
					if (rData.services[key] == false) {
						html += '<p>No Results Found...</p>';
					}
					
					$.each(rData.services[key], function(index, elem){
						html += '<div class="video" rel="'+key+'|'+elem.id+'">';
						html += 	'<img src="' + elem.img_url + '" width="100px" height="75px">';
						html += 	'<small>'+ elem.title +'</small>';
						html += 	'<span>';
						html += 		'<a href="' + elem.vid_url + '" class="play">&nbsp;</a>';
						html += 		'<a href="#" class="add">&nbsp;</a>';
						html += 	'</span>';
						html += '</div>';
					});
					
					html += '<br clear="all"></div>';
				}
			}

			TargetBox.find('.VideosResults').show().html(html);
			TargetBox.find('.VideosResults .video .play').colorbox({iframe:true, width: 450, height:375});
			TargetBox.find('.VideosResults .video .add').click(ChannelVideos.AddVideo);
			TargetBox.find('.LoadingVideos').hide();
		}
	});
		
	
	
	return false;
};

//********************************************************************************* //

ChannelVideos.SubmitVideoUrl = function(Event){
	
	var VideoURL = prompt("Video URL?", "");	
	if (VideoURL == null) return false;
	
	var TargetBox = $(Event.target).closest('div.CVField');
	var FieldID = TargetBox.attr('rel');
	
	var Params = {};
	Params.ajax_method = 'get_video';
	Params.url = VideoURL;
	Params.XID = ChannelVideos.XID;
	Params.field_id = FieldID;
	Params.field_name = ChannelVideos.Data['FIELD'+FieldID].field_name;
	Params.field_layout = ChannelVideos.Data['FIELD'+FieldID].layout;
	
	jQuery.ajax({
		url: ChannelVideos.AJAX_URL, type: 'POST', data: Params, dataType: 'json',
		success: function(rData){			
			if (rData.body != false && rData.success == 'yes'){
				TargetBox.find('.AssignedVideos .NoVideos').hide();
				jQuery(Event.target).closest('.CVTable').find('.AssignedVideos').append(rData.body);
				ChannelVideos.SyncOrderNumbers();
				TargetBox.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});
			}
		}
	});
	
		
	return false;
};

//********************************************************************************* //

ChannelVideos.AddVideo = function(Event){
	var Parent = jQuery(Event.target).closest('div.video');
	var TargetBox = jQuery(Event.target).closest('div.CVField');
	var FieldID = TargetBox.attr('rel');
	
	jQuery(Event.target).addClass('loading');

	var Params = {};
	Params.ajax_method = 'get_video';
	Params.service = Parent.attr('rel').split('|')[0];
	Params.video_id = Parent.attr('rel').split('|')[1];
	Params.XID = ChannelVideos.XID;
	Params.field_id = FieldID;
	Params.field_name = ChannelVideos.Data['FIELD'+FieldID].field_name;
	Params.field_layout = ChannelVideos.Data['FIELD'+FieldID].layout;
	
	jQuery.ajax({
		url: ChannelVideos.AJAX_URL, type: 'POST', data: Params, dataType: 'json',
		success: function(rData){
			if (rData.body != false && rData.success == 'yes'){
				TargetBox.find('.AssignedVideos .NoVideos').hide();
				TargetBox.find('.AssignedVideos').append(rData.body);
				ChannelVideos.SyncOrderNumbers();
				Parent.slideUp('slow');
				TargetBox.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});
			}
		}
	});
	
	return false;
};

//********************************************************************************* //

ChannelVideos.DelVideo = function(Event){
	
	VideoID = $(Event.target).attr('rel');	
	
	// Send Ajax
	if (VideoID != false) {	
		$.get(ChannelVideos.AJAX_URL, {video_id: VideoID, ajax_method: 'delete_video'}, function(){
			
		});
	}
	
	$(Event.target).closest('.CVItem').fadeOut('slow', function(){ $(this).remove(); ChannelVideos.SyncOrderNumbers(); });
		
};

//********************************************************************************* //

ChannelVideos.ClearVideoSearch = function(Event){
	
	var TargetBox = jQuery(Event.target).closest('div.CVField');
	
	TargetBox.find('.VideosResults').slideUp('slow', function(){
		TargetBox.find('.VideosResults').empty();
	});
	
	return false;
}

//********************************************************************************* //

ChannelVideos.SyncOrderNumbers = function(){
	
	// Loop over all Channel Videos Fields
	$('.CVField').each(function(FieldIndex, VideoField){
		
	
		// Loop over all individual Videos
		jQuery(VideoField).find('.CVItem').each(function(VideoIndex, VideoItem){
			
			// Loop Over all Input Elements of the Relation Item
			jQuery(VideoItem).find('input, textarea, select').each(function(){
				attr = jQuery(this).attr('name').replace(/\[videos\]\[.*?\]/, '[videos][' + (VideoIndex+1) + ']');
				jQuery(this).attr('name', attr);
			});
		});	
		
		// Add Zebra
		jQuery(VideoField).find('.CVItem').removeClass('odd');
		jQuery(VideoField).find('.CVItem:odd').addClass('odd');
	});
	
};

//********************************************************************************* //