$(".navee_nav_select").change(function(){
	base = $(this).parent("li").parent("ol").siblings("#naveeBase").val();

	$.ajax({
					type: "GET",
					cache: false,
					url: base+"&C=addons_modules&M=show_module_cp&module=navee&method=get_parent_select_by_id",
					data: "id="+$(this).val(),
					dataType: "json",
					success: function(msg){
						$("#naveeFTParent").children("select").html("<option value='0'>Top Level</option>"+msg[0].options);
					}
			});

});

$(".naveeExistingElements dl dd a").click(function(){
	$(".navee_trash_dump").attr("href",$(this).attr("href"));
	$(".navee_alert").css("top", $(window).scrollTop()+100).fadeIn(333);
	return false;
});

$(".navee_trash_no_dump").click(function(){
	$(".navee_alert").fadeOut(333);
});
