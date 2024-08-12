
/* Localization */
function translateJS(key)
{
	if(typeof(js_locale_data) == "undefined")
		return;
	
	if(js_locale_data[key])
	{
		var string = js_locale_data[key].replace("\n", "<br />");
		string = string.replace(/'([^'\s]+)'/g, "&laquo;$1&raquo;");
		
		if(typeof(arguments[1]) == "object")
			for(value in arguments[1])
				string = string.replace("{" + value + "}", arguments[1][value]);
		
		return string;
	}
	else
		return "{" + key + "_" + localeRegion + "}";
}

function createDateObject(date)
{
	var result = {year: 0, month: 0, day: 0};
	var delimeter = (date.indexOf(".") != -1) ? "." : "/";
	var date = date.split(delimeter);
	let places = dateFormat.split(delimeter);
	
	for(i = 0; i <= 2; i ++)
	{
		if(places[i] == "dd")
			result.day = date[i];
		else if(places[i] == "mm")
			result.month = date[i] - 1;
		else if(places[i] == "yyyy")
			result.year = date[i];			
	}
	
	return new Date(result.year, result.month, result.day);
}

$(document).ready(function()
{
	var datepicker_params = {"language": localeRegion, "autoClose": true, "dateFormat" : dateFormat};
	
	if(americanLocale)
		datepicker_params["firstDay"] = 0;
	
	var datepicker = $("form.regular input[name='date_due']").datepicker(datepicker_params).data('datepicker');
	var selected_date = $("form.regular input[name='date_due']").val();
	
	if(selected_date)
		datepicker.selectDate(createDateObject(selected_date));
	
	$("table.tasks-table td.actions span[id^='document-delete-']").on("click", function()
	{
		var parts = $(this).prop("id").split("-");
		var link = rootPath + "documentation/?delete=" + parts[2] + "&token=" + parts[3];
		var name = $(this).parent().parent().find("td.name a").text();
		var message = translateJS("delete_document", {"name": name});
		
		$.modalWindow.open(message, {url: link});
	});
	
	$("table.tasks-table td.actions span[id^='project-delete-']").on("click", function()
	{
		var archive = location.href.match(/\/archive/) ? "archive/" : "";
		var parts = $(this).prop("id").split("-");
		
		var link = rootPath + "projects/" + archive + "?delete=" + parts[2] + "&token=" + parts[3];
		var selector = archive ? "td.name" : "td.name a";
		var name = $(this).parent().parent().find(selector).text();
		var message = translateJS("delete_project", {"name": name});
		
		$.modalWindow.open(message, {url: link});
	});
	
	$("table.tasks-table td.actions span[id^='project-restore-']").on("click", function()
	{
		var parts = $(this).prop("id").split("-");
		var link = rootPath + "projects/archive/" + "?restore=" + parts[2] + "&token=" + parts[3];
		var name = $(this).parent().parent().find("td.name").text();
		var message = translateJS("restore_project", {"name": name});
		
		$.modalWindow.open(message, {url: link});
	});
	
	$("div.item-actions span[id^='delete-task-']").on("click", function()
	{
		var parts = $(this).prop("id").split("-");
		var link = rootPath + "tasks/?delete=" + parts[2] + "&token=" + parts[3];
		var message = translateJS("delete_task", {"name": $("#content h1:first").text()});
		 
		$.modalWindow.open(message, {url: link});
	});
	
	$("div.item-actions span[id^='delete-document-']").on("click", function()
	{
		var parts = $(this).prop("id").split("-");
		var link = rootPath + "documentation/?delete=" + parts[2] + "&token=" + parts[3];
		var message = translateJS("delete_document", {"name": $("#content h1:first").text()});
		 
		$.modalWindow.open(message, {url: link});
	});
	
	$("span.archive-project").on("click", function()
	{
		var parts = $(this).prop("id").split("-");
		var link = rootPath + "projects/archive/?archive=" + parts[1] + "&token=" + parts[2];
		var message = translateJS("archive_project");
		 
		$.modalWindow.open(message, {url: link});
	});
	
	$("table.tasks-table td.actions span[id^='task-delete-']").on("click", function()
	{
		var parts = $(this).prop("id").split("-");
		var link = (location.href.indexOf("?") != -1) ? location.href + "&" : location.href + "?";
		link += "delete=" + parts[2] + "&token=" + parts[3];
		
		var name = $(this).parent().parent().find("td.name a").text();
		var message = translateJS("delete_task", {"name": name});
		
		if(name == "")
			message = message.replace(" &laquo;", "").replace("&raquo;", "");
		
		$.modalWindow.open(message, {url: link});
	});

	$("div.item-actions span.delete.off, table.tasks-table td.actions span.delete.off").off("click");
	
	$("ul.files li span.delete").on("click", function()
	{
		var ajax_params = "delete-file=" + $(this).prev().text() + "&params=" + $(this).prop("id");
		var _this = $(this);
		
		var action = function()
		{
			$.ajax({
				type: "POST",
				dataType: "json",
				url: rootPath + "views/ajax/common.php",
				data: ajax_params,
				success: function(data)
				{
					if(data.error != "")
					{
						$("#custom-modal-window div.message").text(data.error);
						$("#modal-button-ok").remove();
					}
					else
					{
						var tag = (data.files_left == "0") ? _this.parents("ul.files") : _this.parent().parent();
						tag.fadeOut(200, function() { $(this).remove(); });
						
						if(_this.parents("div.section").length && data.files_left == "0")
							if(!_this.parents("div.section").find("div.content").length)
								_this.parents("div.section").fadeOut(200, function() { $(this).remove(); });
						
						$.modalWindow.close();
					}
				}
			});
		}
		 
		var message = translateJS("delete_file", {"name": $(this).prev().text()});
		$.modalWindow.open(message, {event: action});
		
		return false;
	});
	
	$("form.regular input.button.submit").on("click", function()
	{
		$(this).off("click").parents("form.regular").submit();
	});
	
	$("#edit-description").on("click", function()
	{
		$("#description-area").toggleClass("hidden");
		$(this).toggleClass("open");
		var text = $("#description-area").hasClass("hidden") ? translateJS("edit") : translateJS("fold");
		
		$(this).text(text);
	});
	
	$("form .field-input span.delete").click(function() 
    { 
        $(this).parents("div.file-params").empty().next().show(); 
    });

    $("form .multiple-files-data span.delete").on("click", function() 
    { 
        $(this).parent().remove();
    });    
	
	$("#check-all").on("change", function()
	{
		if($(this).is(":checked"))
			$("#items-table-form input[name^='item-']").prop("checked", "checked").change();
		else
			$("#items-table-form input[name^='item-']").prop("checked", "").change();
	});

	$("label.checkbox-wrapper").on("click", function()
	{
		$(this).toggleClass("checked");
	});
	
	$("#items-table-form td :checkbox").on("change", function()
	{
		if($(this).is(":checked"))
			$(this).parents("tr").addClass("active");
		else
			$(this).parents("tr").removeClass("active");
	});
	
	$("input.mass-action").on("click", function()
	{
		var total = $("#items-table-form input[name^='item-']:checked").length;
		
		if(!total)
			return;
		
		$.ajax({
			type: "POST",
			dataType: "html",
			url: rootPath + "views/ajax/common.php",
			data: "get-mass-action-data=1",
			success: function(data)
			{
				var event_action = function()
				{
					var params = $("#custom-modal-window div.extra form").serialize();
					
					if(params.match(/=\w/))
					{
						$("form input[name='mass-action-total']").val(total);
						$("form input[name='mass-action-fields']").val(params);
						
						var action = $("#filter-url-params").val();
						action = $("#items-table-form").prop("action") + (action ? "?" + action : "");
						
						if(location.href.match(/\Wpage=\d+/))
						{
							var page = "page=" + location.href.replace(/.*page=(\d+).*/, "$1");
							action += ((action.indexOf("?") == -1) ? "?" + page : "&" + page);
						}

						$("#items-table-form").prop("action", action).submit();
					}
				}
				
				var params = {event: event_action, css_class: "mass-actions", extra_html: data};
				$.modalWindow.open(translateJS("with_selected"), params);
				
				$("#custom-modal-window input[name='date_due']").datepicker(datepicker_params);
				
				let option = "<option value=\"\">" + translateJS("not_defined") + "</option>";
				$("#custom-modal-window select[name='complete'] option[value='']").prop("value", 0);
				$("#custom-modal-window select[name='complete']").prepend(option).val("");
				
				var button = "<input type=\"button\" id=\"mass-delete-button\" value=\"" + translateJS("delete") + "\" />";
				$(button).prependTo($("#custom-modal-window div.buttons"));

				let allow_mass_delete = false;

				$("#items-table-form input[name^='item-']:checked").each(function()
				{
					let button = $(this).parents("tr").find("td.actions span.delete");

					if(button.length && !button.hasClass("off"))
					{
						allow_mass_delete = true;
						return false;
					}
				});

				if(!allow_mass_delete)
				{
					$("#mass-delete-button").remove();
					return;
				}

				$("#mass-delete-button").on("click", function()
				{
					$(this).remove();
					
					$("#custom-modal-window").hide().prop("class", "").find("div.extra").html("");
					$("#custom-modal-window div.message").text(translateJS("delete_checked") + "?");

					$("#modal-button-ok").off("click").on("click", function()
					{
						var action = $("#items-table-form").prop("action") + "?mass-delete=" + total;
						
						if(location.href.match(/\Wpage=\d+/))
							action += "&page=" + location.href.replace(/.*page=(\d+).*/, "$1");
						
						var url_params = $("#filter-url-params").val();
						action += url_params ? "&" + url_params : "";
						
						$("#items-table-form").prop("action", action).submit();
					});
					
					$("#custom-modal-window").show();
				});
			}
		});
	});
	
	$("div.button.add-comment").on("click", function()
	{
		var _this = this;

		$.ajax({
			type: "POST",
			dataType: "html",
			url: rootPath + "views/ajax/common.php",
			data: "get-add-comment-data=1",
			success: function(data)
			{
				var action = function()
				{
					var form_values = $("#custom-modal-window div.extra form").serialize();
					var text = $.trim($("#custom-modal-window textarea[name='text']").val());
					var params = $(_this).prop("id").split("-");
					
					params = "add-comment=" + params[2] + "&" + form_values + "&token=" + params[3];
					
					if(text == "" && !$("#custom-modal-window form select option[value!='']:selected").length)
						return;
					
					$("#modal-button-ok").off("click");
					
					$.ajax({
						type: "POST",
						dataType: "text",
						url: rootPath + "views/ajax/common.php",
						data: params,
						success: function(data)
						{
							location.reload();
						}
					});
				};
				
				$.modalWindow.open(translateJS("add_comment"), {event: action, css_class: "task-comment", extra_html: data});

				let option = "<option value=\"\">" + translateJS("not_defined") + "</option>";
				$("#custom-modal-window select[name='complete'] option[value='']").prop("value", 0);
				$("#custom-modal-window select[name='complete']").prepend(option).val("");
			}
		});		
	});
	
	$("#limit-per-page").on("change", function()
	{
		var path = location.href.replace(/\?.*$/, "") + "?pager-limit=" + $(this).val();
		var params = $("#filter-url-params").val();
		
		location.href = path + (params ? "&" + params : "");
	});
	
	$(".field-list .button.options").on("click", function()
	{
		$(".field-list .list").fadeToggle("fast");
	});

    $(".field-list .list .cancel").on("click", function()
    {
        $(".field-list .list").fadeOut("fast");
    });
	
	$("#filters-header").on("click", function()
	{
		$("#filters-form, #filters-header").toggleClass("active");
	});
	
	$("#filter-date_due").prop("autocomplete", "off");
	$("#filters-form input[name='jquery_check_code']").remove();

	var datepicker_filter = $("#filter-date_due").datepicker(datepicker_params).data("datepicker");
	selected_date = $("#filter-date_due").val();
	
	if(selected_date)
		datepicker_filter.selectDate(createDateObject(selected_date));
	
	$("#filters-reject").on("click", function()
	{
		location.href = $("#filters-form").prop("action") + "?filters-reject";
	});

	$("#filters-hide").on("click", function()
	{
		location.href = $("#filters-form").prop("action");
	});	
	
	$("div.open-filters select").on("change", function()
	{
		var path = $("#items-table-form").prop("action");
		
		if($(this).val())
			path += "?" + $(this).prop("name") + "=" + $(this).val();
		
		location.href = path;
	});
	
	$("div.section div.controls span.edit").on("click", function()
	{
		var parts = $(this).parent().prop("id").split("-");
		var text = $(this).parent().parent().find("div.text").text();
		var _this = $(this);
		
		event_action = function()
		{
			if($.trim($("#custom-modal-window textarea.edit-comment").val()) == "")
				return;
			
			var value = $("#custom-modal-window div.extra form").serialize();
			var ajax_params = "edit-comment=" + parts[1] + "&" + value + "&token=" + parts[3];
			
			$.ajax({
				type: "POST",
				dataType: "html",
				url: rootPath + "views/ajax/common.php",
				data: ajax_params,
				success: function(data)
				{
					if(data == "")
						return;
					
					_this.parent().prev().html(data);
					$.modalWindow.close();
				}
			});
		}
		
		var data = "<form><textarea name=\"text\" class=\"edit-comment\">" + text + "</textarea></form>";
		
		var params = {event: event_action, css_class: "task-comment", extra_html: data};
		$.modalWindow.open(translateJS("edit_comment"), params);
	});
	
	$("div.section div.controls span.delete").on("click", function()
	{
		var parts = $(this).parent().prop("id").split("-");
		var name = $(this).parents("div.section").find("div.author").html();
		var _this = $(this);
		
		event_action = function()
		{
			$.ajax({
				type: "POST",
				dataType: "text",
				url: rootPath + "views/ajax/common.php",
				data: "delete-comment=" + parts[1] + "&token=" + parts[3],
				success: function(data)
				{
					if(data == "delete")
						_this.parents("div.section").fadeOut(200, function() { $(this).remove(); });
					else if(data == parts[1])
						_this.parent().parent().remove();
					
					$.modalWindow.close();
				}
			});
		}
		
		$.modalWindow.open(translateJS("delete_comment", {"name": name}), {event: event_action});
	});
	
	$("div.pager input.active-page").on("change", function()
	{
		var value = parseInt($(this).val());
		var total = parseInt($(this).parent().find("span.total-pages").text());
		
		if(value && value <= total)
		{
			if($(this).hasClass("module"))
				var url = $("div.pager > a").prop("href").replace(/page=\d+/, "page=" + value);
			else
			{
				var params = $("#filter-url-params").val();
				var url = $("#filters-form").prop("action") + "?" + params + (params ? "&" : "") + "page=" + value;
			}
			
			location.href = url;
		}
	});
	
	$("div.pager input.active-page").on("keyup", function(event)
	{
		if(event.keyCode == "13")
			$(this).change();
	});
	
	$("div.m2m-buttons span.m2m-left, div.m2m-buttons span.m2m-right").click(function()
	{
		var from = ".m2m-not-selected";
		var to = ".m2m-selected";
		
		if(this.className == "m2m-left")
		{
			from = ".m2m-selected";
			to = ".m2m-not-selected";			
		}
		
		$(this).parents("div.m2m-wrapper").find("select" + from + " option:selected").each(function()
		{
			 $(this).removeAttr("selected").parents("div.m2m-wrapper").find("select" + to).append($(this));
		});
	});
	
	$("div.m2m-buttons span.m2m-up, div.m2m-buttons span.m2m-down").click(function()
	{
		var option = $(this).parent().prev().find("option:selected:first");
		
		if(this.className == "m2m-up")
			option.insertBefore(option.prev());
		else
			option.insertAfter(option.next());
	});
	
	$("div.field-list div.controls input.apply").on("click", function()
	{
		var view = $(this).prop("id").replace("save-columns-", "");
		var fields = [];
		
		$("div.field-list div.m2m-wrapper").find("select.m2m-selected option").each(function()
		{
			fields.push($(this).prop("value")); 
		});
		
		if(!fields.length)
			return;
		
		$.ajax({
			type: "POST",
			dataType: "text",
			url: rootPath + "views/ajax/common.php",
			data: "save-columns=" + fields.join(",") + "&view=" + view,
			success: function(data)
			{
				location.reload();
			}
		});
	});

    $(window).resize(function()
    {
        if($(window).width() <= 780)
        	$(".filters-buttons .found-amount, .filters-buttons #filters-reject").detach().appendTo(".mobile-filters-buttons")
		else
            $(".mobile-filters-buttons .found-amount, .mobile-filters-buttons #filters-reject").detach().appendTo(".filters-buttons")
        
        if($(window).width() <= 510)
        	$(".button.create-task, .documents-page .item-actions .create").text(translateJS("create"));
    });

    $(window).resize();

    $("#menu-button").on("click", function()
    {
        $("#overlay").fadeIn(500, function()
        {
            $("#mobile-menu").addClass("showed");
        });
    });

    $("#menu-close").on("click", function()
    {
        $("#mobile-menu").removeClass("showed");
        $("#overlay").fadeOut(500);
    });

    var getMyNewAssignedTasks = function()
    {
		$.ajax({
			type: "POST",
			dataType: "text",
			url: rootPath + "views/ajax/live.php",
			data: "get-my-new-tasks=1",
			success: function(data)
			{
				if(data && data.indexOf("container") == -1)
				{
					$("ul.account-menu li span.new, div.bottom-menu a span.new").remove();
					$("ul.account-menu a.tasks, div.bottom-menu span.tasks").parent().append(data);
				}
			}
		});
    };

    if($("ul.account-menu li").length)
    {
	    getMyNewAssignedTasks();
	    setInterval(getMyNewAssignedTasks, 10000);
	}

    if($("div.button.add-comment").length && $("div.task-params").length)
    {
    	let parts = $("div.button.add-comment").prop("id").split("-");
    	let task_id = parts[2];
    	var current = 0;

    	if($("div.task-history").length)
    		current = $("div.task-history div.section").length;

    	var checkNewComments = setInterval(function()
    	{
			$.ajax({
				type: "POST",
				dataType: "text",
				url: rootPath + "views/ajax/live.php",
				data: "get-new-comments=" + task_id,
				success: function(data)
				{	
					if($.modalWindow.is_opened)
						return;

					if(data && data != "0" && parseInt(data) > current)
					{
						clearInterval(checkNewComments);

						let message = translateJS("new_comments_reload");
						let action = function(){ location.reload(); };

						$.modalWindow.open(message, {event: action, css_class: "live-alert"});
					}
				}
			});

    	}, 12000);
    }

    if(typeof myCurrentTasksHash !== "undefined")
    {
    	var toSeeHash = "";

    	var checkMyNewTasks = setInterval(function()
    	{
			$.ajax({
				type: "POST",
				dataType: "json",
				url: rootPath + "views/ajax/live.php",
				data: "check-my-new-tasks=" + myCurrentTasksHash,
				success: function(data)
				{
					if($.modalWindow.is_opened)
						return;

					if(data.reload == true)
					{
						clearInterval(checkMyNewTasks);

						let message = translateJS("tasks_list_reload");
						let action = function(){ location.reload(); };

						$.modalWindow.open(message, {event: action, css_class: "live-alert"});
					}
					else if(toSeeHash != data.to_see_hash)
					{
						toSeeHash = data.to_see_hash;
						highlightTasksToSee(Object.values(data.to_see));
					}
				}
			});

    	}, 15000);
    }

    function highlightTasksToSee(ids)
    {
    	for(i = 0; i < ids.length; i ++)
    	{
    		let bage = '<span class="see"></span>';
    		let row = $("input[name='item-" + ids[i] + "']").parents("tr");

    		if(row.find("span.see").length)
    			continue;
    		
    		row.find("td.number").addClass("bage").append(bage);
    		row.find("td.name span.tracker span.for-mobile").append(bage);
    	}
    }

    if(typeof tasksToSee !== "undefined")
    {
    	highlightTasksToSee(tasksToSee);
    }

    if(!$("ul.account-menu li").length)
    	return;

    if(typeof isDemoMode != "undefined")
    {
    	$("form.regular input.button.submit, span.archive-project").off("click").css("cursor", "default");
    	$("div.field-list div.controls input.apply").off("click").css("cursor", "default");
    }
});