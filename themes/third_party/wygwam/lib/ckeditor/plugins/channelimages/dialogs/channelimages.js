( function(){
	
	// Dialog Object
	// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.dialogDefinition.html
	var channelimages_dialog = function(editor){
		
		DialogElements = new Array();
		DialogSizes = new Array();
		
		
		//********************************************************************************* //
		
		var GetCISizes = function(){
			
			// If Channel Images is not loaded return
			if (typeof(ChannelImages.Sizes) != 'object') return DialogSizes;
			
			var LoopCount = 1;
			var RadioName = Math.floor(Math.random()*11);
			
			// Loop over all images
			jQuery.each(ChannelImages.Sizes, function(index, val){
				
				var Checked = '';
				if (LoopCount == 1) Checked = 'checked';
				
				DialogSizes.push({
					type:'html',
					onClick: SelectImage,
					html: '<div class="CISize">'+
							'<input type="radio" value="'+val.toLowerCase()+'" name="ci_size_'+RadioName+'" '+Checked+'/> &nbsp;&nbsp;&nbsp;'+
							'<strong>'+ val +'</strong>&nbsp;&nbsp;'+
						'</div>'
				});
				
				LoopCount++;
			});
			
			return DialogSizes;
		};
		
		//********************************************************************************* //
		
		var SelectImage = function(Event){

			if (typeof(Event.target) == 'undefined') return;
			
			var Target = jQuery(Event.target);
			
			// Remove all other
			Target.closest('table').find('.CImage').removeClass('Selected');
			
			Target.closest('.CImage').addClass('Selected');
		};
		
		
		//********************************************************************************* //
		
		return {
			
			// The dialog title, displayed in the dialog's header. Required. 
			title: 'Channel Images',
			
			// The minimum width of the dialog, in pixels.
			minWidth: '600',
			
			// The minimum height of the dialog, in pixels.
			minHeight: '400',
			
			// Buttons
			buttons: [CKEDITOR.dialog.okButton, CKEDITOR.dialog.cancelButton] /*array of button definitions*/,
			
			// On OK event
			onOk: function(Event){
				var Wrapper = jQuery(CKEDITOR.dialog.getCurrent().definition.dialog.parts.dialog.$);
				
				if ( Wrapper.find('.Selected').length == 0) return;
				
				var Selected = Wrapper.find('.Selected img');
				
				var IMGSRC = Selected.attr('src');
				
				var filename = Selected.attr('rel');
				var dot = filename.lastIndexOf('.');
				var extension = filename.substr(dot,filename.length);
				
				var Size = Wrapper.find('.CISize input[type=radio]:checked').val();
				var OLDFILENAME = IMGSRC.match(/[-\.,_\w]+[.][\w]+$/i)[0];
	
				if (Size != 'original'){
					var NewName = filename.replace(extension, '__'+Size+extension);
					IMGSRC = IMGSRC.replace(OLDFILENAME, NewName);
				}
				else {
					IMGSRC = IMGSRC.replace(OLDFILENAME, filename);
				}
				
				var imageElement = editor.document.createElement('img');
				imageElement.setAttribute('src', IMGSRC);
				//imageElement.setAttribute('width', ChannelImages.Sizes[Size].width);
				//imageElement.setAttribute('height', ChannelImages.Sizes[Size].height);
				imageElement.setAttribute('alt', Selected.attr('alt'));
				imageElement.setAttribute('class', 'ci-image ci-'+Size);
				
				editor.insertElement( imageElement );
				
				Selected.parent().removeClass('Selected');
			},
			
			// On Cancel Event
			onCancel: function(){
				
				var Wrapper = jQuery(CKEDITOR.dialog.getCurrent().definition.dialog.parts.dialog.$);
				
				if ( Wrapper.find('.Selected').length == 0) return;
				Wrapper.find('.Selected').removeClass('Selected');
				
			},
			
			// On Load Event
			onLoad: function(){},
			
			// On Show Event
			onShow: function(){
				
				// Grab the ImageWrapper
				var ImgWrapper = jQuery(this.getElement().$).find('.WCI_Images');
				
				// Loop over all images
				jQuery.each(ChannelImages.CI_Images, function(index, val){
					ImgWrapper.append('<div class="CImage"><img src="'+val.url+'" rel="'+val.filename+'" alt="'+val.alt+'"/></div>');
				});
			
				ImgWrapper.find('.CImage').click(SelectImage);
			},
			
			// On Hide Event
			onHide: function(){
				jQuery(this.getElement().$).find('.WCI_Images').empty();
			},
			
			// Can dialog be resized?
			resizable: CKEDITOR.DIALOG_RESIZE_NONE,
			
			// Content definition, basically the UI of the dialog
			contents: 
			[
				 {
					id: 'ci_images',  /* not CSS ID attribute! */
					label: 'Images',
					className : 'weeeej', 
					elements: [
					    {
						   type : 'html',
						   html : '<p>Please select an image and then your desired image size.</p>'
						},
						{
							type : 'html',
							 html : '<div class="WCI_Images"></div>'
						},
						{
							type : 'vbox',
							widths : [ '100%'],
							children : GetCISizes()
						}
					]
				 }
			]
		};
		
		//********************************************************************************* //
		
		
	};
	
	// Add the Dialog
	CKEDITOR.dialog.add('channelimages', function(editor) {
		return channelimages_dialog(editor);
	});
		
})();