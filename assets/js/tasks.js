$(document).on('focus', '.name-input', function(){
	var id = $(this).attr("id");
	$.get("functions/fetch_user_list.php", {filter : "staff"}).done(function(data){
		var userList = JSON.parse(data);
		var autocompleteList = [];
		for(var i = 0; i < userList.length; i++){
			autocompleteList.push(userList[i].user);
		}
		$("#"+id).textcomplete([{
			match: /(^|\b)(\w{2,})$/,
			search: function(term, callback){
				callback($.map(autocompleteList, function(item){
					return item.toLowerCase().indexOf(term.toLocaleLowerCase()) === 0 ? item : null;
				}));
			},
			replace: function(item){
				return item;
			}
		}]);
	});
}).on('click', '.btn-comment', function(){
	var task_id = document.getElementById($(this).attr("id")).dataset.task;
	var comment = $("#comment-form-"+task_id+">textarea").val();
	var comment_author = $("#name-input-"+task_id).val();
	postComment(comment, comment_author, task_id);
})

function fetchTasks(limit){
	$.get("functions/fetch_tasks.php", {limit : limit}).done(function(data){
		if(limit == 0 || $(".sub-modal-notification").is(":visible")){
			displayTasks(data, limit);
		}
	});
}

function fetchComments(task_id){
	$.get("functions/fetch_comments.php", {task_id : task_id}).done(function(data){
		displayComments(task_id, data);
	})
}

function refreshTask(task){
	$("#task-description-"+task.id).html("<span class='glyphicon glyphicon-align-left'></span> "+task.description);
	$("#comments-count-"+task.id).html("<span class='glyphicon glyphicon-comment'></span> "+task.message_count);
}

function displayTasks(data, limit){
	var tasks = JSON.parse(data);
	for(var i = 0; i < tasks.length; i++){
		if($("#task-"+tasks[i].id).length > 0){
			refreshTask(tasks[i]);
		} else {
			if(i == 0){
				if(limit != 0){
					$(".smn-body").empty();
				} else {
					$(".tasks-container").empty();
				}
			}
			// Status handling
			var notifMessage = "", notifClass = "", link = "", linkTitle = "";
			if(tasks[i].status == '0'){
				notifClass = "task-new";
			} else {
				notifClass = "task-old";
			}
			notifMessage += "<div id='task-"+tasks[i].id+"' data-task='"+tasks[i].id+"' data-state='"+tasks[i].status+"' class='panel task-line "+notifClass+"'>";
			notifMessage += "<div class='panel-heading panel-heading-task container-fluid' id='ph-task-"+tasks[i].id+"' data-trigger='"+tasks[i].id+"'>";

			notifMessage += "<div class='col-lg-1'>";
			notifMessage += "<div class='notif-pp'>";
			notifMessage += "<image src='"+tasks[i].photo+"'>";
			notifMessage += "</div>";
			notifMessage += "</div>";

			notifMessage += "<div class='col-sm-11'>";
			notifMessage += "<div class='row'>";

			notifMessage += "<p class='task-title col-sm-10' id='task-title-"+tasks[i].id+"'>";

			// Token handling
			switch(tasks[i].type){
				case "USR":
					switch(tasks[i].subtype){
						case "MAI":
							notifMessage += "<span class='glyphicon glyphicon-envelope'></span> <a href='user/"+tasks[i].user_id+"' target='_blank'><strong>"+tasks[i].user+"</strong></a> n'a pas d'adresse mail enregistrée.";
							link += "user/"+tasks[i].user_id;
							linkTitle += "Aller à l&apos;utilisateur";
							break;
					}
					break;

				default:
					break;
			}

			notifMessage += "</p>";

			notifMessage += "<a href='"+link+"' class='link-glyphicon' target='_blank'><span class='glyphicon glyphicon-share-alt col-sm-1 glyphicon-button-alt glyphicon-button-big' title='"+linkTitle+"'></span></a>";
			if(tasks[i].status == 1){
				notifMessage += "<span class='glyphicon glyphicon-ok-circle col-sm-1 glyphicon-button-alt glyphicon-button-big toggle-read' title='Marquer comme non traitée'></span>";
			} else {
				notifMessage += "<span class='glyphicon glyphicon-ok-sign col-sm-1 glyphicon-button-alt glyphicon-button-big toggle-read' title='Marquer comme traitée'></span>";
			}
			notifMessage += "</div>";

			notifMessage += "<div class='container-fluid'>";
			notifMessage += "<p class='task-hour col-sm-12'> créée "+moment(tasks[i].date).format("[le] ll [à] HH:mm")+"</p>";
			notifMessage += "<p id='task-description-"+tasks[i].id+"'><span class='glyphicon glyphicon-align-left'></span> "+tasks[i].description+"</p>";
			notifMessage += "<div class='col-sm-1' id='comments-count-"+tasks[i].id+"'>";
			notifMessage += "<span class='glyphicon glyphicon-comment'></span> "+tasks[i].message_count;
			notifMessage += "</div>";

			notifMessage += "<div class='col-sm-3'>";
			if(tasks[i].deadline != null){
				notifMessage += "<span class='glyphicon glyphicon-time'></span> "+tasks[i].deadline;
			} else {
				notifMessage += "<span class='glyphicon glyphicon-time'></span> Ajouter une date limite";
			}
			notifMessage += "</div>";

			notifMessage += "</div>";
			notifMessage += "</div>";
			notifMessage += "</div>";

			// Commentaires de la notification
			notifMessage += "<div class='panel-body panel-task-body collapse' id='body-task-"+tasks[i].id+"' data-task='"+tasks[i].id+"'>";
			notifMessage += "<p><span class='glyphicon glyphicon-comment'></span> Commentaires</p>";
			notifMessage += "<div class='comment-unit comment-form' id='comment-form-"+tasks[i].id+"'>";
			notifMessage += "<textarea rows='2' class='form-control' placeholder='&Eacute;crire un commentaire...'></textarea>";
			notifMessage += "<div class='input-group'>";
			notifMessage += "<input class='form-control name-input' id='name-input-"+tasks[i].id+"' type='text' placeholder='Auteur du commentaire'>";
			notifMessage += "<span class='input-group-btn'><button class='btn btn-primary btn-comment' id='comment-task-"+tasks[i].id+"' data-task='"+tasks[i].id+"'>Envoyer</button></span>";
			notifMessage += "</div>";
			notifMessage += "</div>";
			notifMessage += "<div class='task-comments' id='task-comments-"+tasks[i].id+"'></div>";
			notifMessage += "</div>";

			if(limit == 0){
				$(".tasks-container").append(notifMessage);
			} else {
				$(".smn-body").append(notifMessage);
			}
		}
	}
	setTimeout(fetchTasks, 10000, limit);
}

function displayComments(task_id, data){
	$("#task-comments-"+task_id).empty();
	var messages = "";
	var message_list = JSON.parse(data);
	for(var i = 0; i < message_list.length; i++){
		messages += "<div class='comment-unit'>";
		messages += "<a href='user/"+message_list[i].author_id+"' class='link-alt message-author'>"+message_list[i].author+"</a>";
		messages += "<div class='message-container'>"+message_list[i].comment+"</div>";
		messages += "<p class='message-details'>"+moment(message_list[i].date).format("[le] ll [à] HH:mm")+"</p>";
		messages += "</div>";
	}
	$("#task-comments-"+task_id).append(messages);
	setTimeout(fetchComments, 10000, task_id);
}

function postComment(comment, author, task_id){
	$.post("functions/post_comment.php", {comment : comment, user_id : author, task_id : task_id}).done(function(e){
		console.log(e);
		$("#comment-form-"+task_id+">textarea").val('');
		$("#name-input-"+task_id).val('');
		fetchComments(task_id);
	})
}
