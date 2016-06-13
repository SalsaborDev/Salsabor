$(document).on('click', '.label-deletable', function(e){
	e.stopPropagation();
	var id = $(this).attr("id");
	var target = document.getElementById(id).dataset.target;
	var table = "assoc_"+document.getElementById(id).dataset.targettype+"_tags";
	$.when(deleteEntry(table, target)).done(function(data){
		$("#"+id).remove();
	});
}).on('click', '.label-addable', function(e){
	e.stopPropagation();
	var tag = document.getElementById($(this).attr("id")).dataset.tag;
	var target_type = document.getElementById($(this).attr("id")).dataset.targettype;
	if(target_type == "user"){
		var target = /([0-9]+$)/.exec(document.location.href)[0];
	} else {
		var target = /([0-9]+)/.exec(window.target)[0];
	}
	var tag_text = $(this).text();
	if($(this).hasClass("toggled")){
		$.when(detachTag(tag, target, target_type)).done(function(data){
			$("#tag-"+tag).removeClass("toggled");
			$("#tag-"+tag).find("span").remove();
			$("#"+target_type+"-tag-"+data).remove();
		})
	} else {
		var value = /([a-z0-9]+)/i.exec($(this).css("backgroundColor"));
		$.when(attachTag(tag, target, target_type)).done(function(data){
			$("#tag-"+tag).addClass("toggled");
			$("#tag-"+tag).append("<span class='glyphicon glyphicon-ok remove-extension'></span>");
			if(target_type == "user"){
				var insert = ".label-add";
			} else {
				var insert = "#label-add-"+target;
			}
			$(insert).before("<span class='label label-salsabor label-clickable label-deletable' title='Supprimer l&apos;étiquette' id='user-tag-"+data+"' data-target='"+data+"' data-targettype='"+target_type+"' style='background-color:"+value[0]+"'>"+tag_text+"</span>");
		})
	}
}).on('click', '.label-new-tag', function(){
	$(this).before("<input class='tag-input form-control' placeholder='Titre de l&apos;étiquette'>");
	$(".tag-input").focus();
}).on('focus', '.tag-input', function(){
	$(this).keyup(function(event){
		if(event.which == 13){
			var tag_name = $(this).val();
			createUserTag(tag_name);
		} else if(event.which == 27){
			$(".tag-input").remove();
		}
	})
}).on('click', '.color-cube', function(e){
	// Assign a color to a tag
	e.stopPropagation();
	var cube = $(this);
	var target = document.getElementById(cube.attr("id")).dataset.target;
	var value = /([a-z0-9]+)/i.exec(cube.css("backgroundColor"));
	$.when(updateColumn("tags_user", "tag_color", value[0], target)).done(function(data){
		$("#tag-"+target).css("background-color", "#"+value[0]);
		$(".color-cube").empty();
		cube.append("<span class='glyphicon glyphicon-ok color-selected'></span>");
	})
}).on('click', '.btn-tag-name', function(){
	var target = $("#edit-tag-name").data().target;
	var value = $("#edit-tag-name").val();
	$.when(updateColumn("tags_user", "rank_name", value, target)).done(function(data){
		$("#tag-"+target).text(value);
	})
}).on('click', '.delete-tag', function(){
	$(".sub-modal").hide(0);
	var target = $("#delete-tag").data().target;
	$.when(deleteEntry("tags_user", target)).done(function(){
		$("#edit-"+target).remove();
		$("#tag-"+target).remove();
	})
}).on('click', '.mid-button', function(){
	var clicked = $(this);
	var target = document.getElementById($(this).attr("id")).dataset.target;
	if($(this).hasClass("glyphicon-button-disabled")){
		var value = 1;
		$.when(updateColumn("tags_user", "missing_info_default", value, target)).done(function(data){
			$(".glyphicon-button-enabled").each(function(){
				var deactivate = $(this);
				var target = document.getElementById($(this).attr("id")).dataset.target;
				var value = 0;
				$.when(updateColumn("tags_user", "missing_info_default", value, target)).done(function(data){
					deactivate.removeClass("glyphicon-button-enabled");
					deactivate.addClass("glyphicon-button-disabled");
				})
			})
			clicked.removeClass("glyphicon-button-disabled");
			clicked.addClass("glyphicon-button-enabled");
		})
	}
})

function fetchUserTags(){
	return $.get("functions/fetch_user_tags.php");
}

function displayTargetTags(data, target_type){
	var tags = JSON.parse(data), addable = "", added = "", body = "";
	for(var i = 0; i < tags.length; i++){
		if(target_type == "user"){
			var compare = $(".label-deletable");
		} else {
			var compare = $("#task-"+/([0-9]+)/.exec(window.target)[0]).find(".label-deletable");
		}
		compare.each(function(){
			if(tags[i].rank_name == $(this).text()){
				addable = " toggled";
				added = " <span class='glyphicon glyphicon-ok remove-extension'></span>";
				return false;
			} else {
				addable = "";
				added = "";
			}
		})
		body += "<h4><span class='label col-xs-12 label-clickable label-addable"+addable+"' id='tag-"+tags[i].rank_id+"' data-tag='"+tags[i].rank_id+"' data-targettype='"+target_type+"' style='background-color:"+tags[i].color+"'>"+tags[i].rank_name+added+"</span></h4>";
	}
	body += "<h4><span class='label col-xs-12 label-default label-clickable label-new-tag' id='label-new' data-targettype='"+target_type+"'>Créer une étiquette</span></h4>";
	return body;
}

function createUserTag(tag_name){
	$.post("functions/create_user_tag.php", {name : tag_name}).done(function(data){
		if(top.location.pathname === "/Salsabor/tags"){
			$(".tag-input").replaceWith("<span class='label col-xs-7 label-salsabor label-clickable label-addable' id='tag-"+data+"' data-tag='"+data+"'>"+tag_name+"</span><span class='glyphicon glyphicon-pencil glyphicon-button glyphicon-button-alt col-xs-1 trigger-sub' id='edit-"+data+"' data-subtype='edit-tag' data-target='"+data+"' title='Editer l&apos;étiquette'></span>");
		} else {
			$(".tag-input").replaceWith("<h4><span class='label col-xs-12 label-salsabor label-clickable label-addable' id='tag-"+data+"' data-tag='"+data+"'>"+tag_name+"</span></h4>");
		}
	})
}

function attachTag(tag, target, target_type){
	return $.post("functions/attach_tag.php", {tag : tag, target : target, type : target_type});
}

function detachTag(tag, target, target_type){
	return $.post("functions/detach_tag.php", {tag : tag, target : target, type : target_type});
}