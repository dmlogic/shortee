var shortee_vals = {
	sort : '',
	order: '',
	offset: 0
}

$(function(){

	// search button handler
	$("#search_button").click(function(){shortee_filter()});

	// form element handler
	$("#date_range, #f_perpage, #keywords").bind("click keyup",function(){shortee_filter(1)});

	// pagination handler
	$("#shortee-pagination a").live("click",function(e){
		e.preventDefault();
		shortee_vals.offset = $(this).attr("href").replace('#&offset=','');
		shortee_filter();
	})

	// table heading handler
	$("table.mainTable th").click(function(){
		if($(this).attr("title") == "") {
			return;
		}

		c =$(this).attr("class");
		shortee_vals.sort = $(this).attr("title");

		if(c == "headerSortUp") {
			shortee_vals.order = 'desc';
			nc = "headerSortDown";

		} else if(c == "headerSortDown") {
			nc = "headerSortUp";
			shortee_vals.order = 'asc';

		} else {
			nc = "headerSortUp";
			shortee_vals.order = 'asc';
		}

		$("table.mainTable th").attr("class","");
		$(this).addClass(nc);

		shortee_filter();
	})

	// external link handler
	$("a.shortee-link").live("click",function(e){
		e.preventDefault();
		window.open($(this).attr("href"));
	})

	// delete handler
	$("a.shortee-delete").live("click",function(e){
		e.preventDefault();

		if(!confirm("Delete this URL and all stats?")) {
			return;
		}

		$.get(
			$(this).attr("href"),
			function(data) {
				if(data == "success") {
					shortee_filter();
				}
			}
		);
	})

})

function shortee_filter(reset_page) {

	if(typeof(reset_page) != "undefined") {
		shortee_vals.offset	 = 0;
	}

	$.get(
		$('#filterform').attr("action"),
		{
			date_range : $("#date_range").val(),
			perpage : $("#f_perpage").val(),
			keywords : $("#keywords").val(),
			offset : shortee_vals.offset,
			sort : shortee_vals.sort,
			order : shortee_vals.order
		},
		function(data) {
			$("table.mainTable tbody").html(data.table);
			$("#shortee-pagination").html(data.pagination);
		},
		'json'
	);
}