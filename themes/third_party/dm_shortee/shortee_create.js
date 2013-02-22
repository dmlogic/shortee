$(function(){

	// tab focus control
	$("#long_url_choices li").click(function(){

		$("#long_url_choices li").removeClass("current");
		$(this).addClass("current");

		$("#url_wrapper p").addClass("offscreen");

		id = $(this).attr("id").replace('luc_','');
		$("#long_url_"+id).removeClass("offscreen");

	})

	// select pages handler
	$("#long_url_pages select").change(function(){

		if($(this).val() == 0) {
			return;
		}

		shortee_select(shortee_base_url + $(this).val());

	})

	// select template group handler
	$("#template_groups").change(function(){

		$("#templates").html(init_group_text);

		$.each(shortee_templates[$(this).val()],function(i,v){
			$("#templates").append('<option value="'+v+'">'+v+'</option>').show();
		})
	})

	// select template handler
	$("#templates").change(function(){

		shortee_select(shortee_base_url + '/' + $("#template_groups :selected").text() + '/'+$(this).val());

	})

	// code generation handler
	$("#generate").click(function(){
		$.get(
			$(this).closest('form').attr("action")+"&generate=1&domain="+$("short_domain").val()+"url="+escape($("#long_url").val()),
			function(data) {

				if(data.result == 'duplicate') {
					return shortee_result('duplicate',data.message,data.url,data.id);
				}

				$("#short_url").val(data.message);

			},'json'
		);
	})

	// submission handler
	$("#shortee_submit").click(function(){
		$.post(
			$(this).closest('form').attr("action"),
			$(this).closest('form').serialize(),
			function(data){
				return shortee_result(data.result,data.message,data.url,data.id);
			},
			'json'
		);
	})

	// return handler
	$("#shortee_return").click(function(){

		$("#final_url").val("");
		$("#shortee_feedback").hide();

		if( $("#shortee_feedback").hasClass("success")) {
			$('#short_url').val("");
		}

		$("#url_wrapper p").addClass("offscreen");
		$("#long_url_enter").removeClass("offscreen");
		$("#shortee_form").show();
	})

	// link handler
	$("#final_url a.shortee-link").click(function(e){
		e.preventDefault();
		window.open($(this).attr("href"));
	})

})

/**
 * Populates main enter field with required value,
 * resets other aspects of form to initial state
 */
function shortee_select(v) {

	$("#url_wrapper p").addClass("offscreen");
	$("#long_url").val(v);
	$("#long_url_enter").removeClass("offscreen");

	$("#long_url_choices li").removeClass("current");
	$("#luc_enter").addClass("current");

	 var field = $('#template_groups');
	 field.val($('option:first', field).val());

	 $("#templates").html(init_group_text).hide();

	 var field = $('#long_url_pages select');
	 field.val($('option:first', field).val());
}

/**
 * Prepares a formatted result screen
 */
function shortee_result(type,message,url,id) {

	$("#shortee_form").hide();

	$("#shortee_feedback h3").text(message);

	$("#shortee_feedback").attr("class",type).show();

	if(type == 'error') {
		$("#final_url").hide();
	} else {
		$("#final_url").show();
		$("#full_short_url").val(url).focus().select();
		$("#long_link").attr("href",$("#long_url").val()).text($("#long_url").val());
		$("#short_link").attr("href",url).text(url);

		qr = $("#qr_view").attr("href");
		$("#qr_view").attr("href",qr+'&id='+id);
		$("#qr_dl").attr("href",qr+'&download=true&id='+id);

	}
}