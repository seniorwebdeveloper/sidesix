<script type="text/javascript">
ChannelImages.LANG = <?=$langjson?>;
</script>

<script id="ChannelImagesSingleField" type="text/x-jquery-tmpl">
<tr class="Image {{#is_cover}}PrimaryImage{{/is_cover}}">
	{{#show_row_num}}<td class="num"></td>{{/show_row_num}}
	{{#show_id}}<td>{{{image_id}}}</td>{{/show_id}}
	{{#show_image}}<td>
		<a href='{{{big_img_url}}}' class='ImgUrl' rel='ChannelImagesGal' title='{{{image_title}}}'>
			<img src="{{{small_img_url}}}" width='50px' alt='{{{image_title}}}'>
		</a></td>
	{{/show_image}}
	{{#show_filename}}<td>{{{filename}}}</td>{{/show_filename}}
	{{#show_title}}<td data-field="title">{{{image_title}}}</td>{{/show_title}}
	{{#show_url_title}}<td data-field="url_title">{{{image_url_title}}}</td>{{/show_url_title}}
	{{#show_desc}}<td data-field="description">{{{description}}}</td>{{/show_desc}}
	{{#show_category}}<td data-field="category">{{{category}}}</td>{{/show_category}}
	{{#show_cifield_1}}<td data-field="cifield_1">{{{cifield_1}}}</td>{{/show_cifield_1}}
	{{#show_cifield_2}}<td data-field="cifield_2">{{{cifield_2}}}</td>{{/show_cifield_2}}
	{{#show_cifield_3}}<td data-field="cifield_3">{{{cifield_3}}}</td>{{/show_cifield_3}}
	{{#show_cifield_4}}<td data-field="cifield_4">{{{cifield_4}}}</td>{{/show_cifield_4}}
	{{#show_cifield_5}}<td data-field="cifield_5">{{{cifield_5}}}</td>{{/show_cifield_5}}
	<td>
		{{#show_image_action}}{{^is_linked}}<a href='#' class='gIcon ImageProcessAction' title='Process Action' ></a>{{/is_linked}}{{/show_image_action}}
		<a href='javascript:void(0)' class='gIcon ImageMove'></a>
		<a href='#' class='gIcon {{#is_cover}}StarIcon ImageCover{{/is_cover}} {{^is_cover}}ImageCover{{/is_cover}}' title='Cover'></a>
		<a href="#" {{#is_linked}}class="gIcon ImageDel ImageLinked" title="Unlink"{{/is_linked}} {{^is_linked}}class="gIcon ImageDel" title="Delete"{{/is_linked}}></a>
		<textarea name="{{{field_name}}}[images][][data]" class="ImageData hidden">{{{json_data}}}</textarea>
	</td>
</tr>
</script>

