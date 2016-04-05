$(document).ready(function(){
	// Init by display all the active sessions
	/* The goal here is to fetch all the active sessions when the page is loaded, then to wait 15 minutes before going to see if new sessions were activated. Thus, every 15 minutes we have to only get the newly activated sessions, which means the sessions that will begin in less than 90 minutes away from the time we're checking. As the sessions could have been added in a deorganised manner, we will construct an array of currently displayed sessions by ID to cross check what can be ignored by subsequent fetches.

	The same goes the participations. We have to fetch the participations of only the sessions that are not collapsed. To do that, we create an array that will contain the non collapsed sessions, and every so often we'll refresh everything at once.
	*/
	var fetched = [];
	displaySessions(fetched);
	moment.locale('fr');
	window.openedSessions = [];
	refreshTick(window.openedSessions);
}).on('click', '.panel-heading-container', function(){
	var id = document.getElementById($(this).attr("id")).dataset.session;
	fetchRecords(id);
}).on('shown.bs.collapse', ".panel-body", function(){
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
	displayRecords(session_id);
	window.openedSessions.push(parseInt(session_id));
}).on('hidden.bs.collapse', ".panel-body", function(){
	var session_id = document.getElementById($(this).attr("id")).dataset.session;
	switch(window.openedSessions.length){
		case 0:
			break;

		case 1:
			window.openedSessions.length = 0;
			break;

		default:
			window.openedSessions = jQuery.grep(window.openedSessions, function(arr){
				return arr !== session_id;
			})
	}
})

function displaySessions(fetched){
	$.get("functions/fetch_active_sessions.php", {fetched : fetched}).done(function(data){
		var active_sessions = JSON.parse(data);
		var as_display = "";
		$(".active-sessions-container").append(as_display);
		for(var i = 0; i < active_sessions.length; i++){
			var cours_start = moment(active_sessions[i].start);
			if(cours_start > moment().format("DD/MM/YYYY HH:mm")){
				var relative_time = cours_start.toNow();
			} else {
				var relative_time = cours_start.fromNow();
			}
			as_display += "<div class='panel panel-session' id='session-"+active_sessions[i].id+"'>";
			// Panel heading
			as_display += "<a class='panel-heading-container' id='ph-session-"+active_sessions[i].id+"' data-session='"+active_sessions[i].id+"'>";
			as_display += "<div class='panel-heading'>";
			// Container fluid for session name and hour
			as_display += "<div class='container-fluid'>";
			as_display += "<p class='session-id col-lg-4'>"+active_sessions[i].title+"</p>";
			as_display += "<p class='session-date col-lg-8'><span class='glyphicon glyphicon-time'></span> Le "+cours_start.format("DD/MM")+" de "+cours_start.format("HH:mm")+" à "+moment(active_sessions[i].end).format("HH:mm")+" (<span class='relative-start'>"+relative_time+"</span>)</p>";
			as_display += "</div>";
			// Container fluid for session level, teacher...
			as_display += "<div class='container-fluid'>";
			as_display += "<p class='col-lg-2 col-lg-offset-4'><span class='glyphicon glyphicon-signal'></span> "+active_sessions[i].level+"</p>";
			as_display += "<p class='col-lg-3'><span class='glyphicon glyphicon-pushpin'></span> "+active_sessions[i].room+"</p>";
			as_display += "<p class='col-lg-3'><span class='glyphicon glyphicon-blackboard'></span> "+active_sessions[i].teacher+"</p>";
			as_display += "</div>";

			as_display += "</div>";
			as_display += "</a>";
			// Panel body
			as_display += "<div class='panel-body collapse' id='body-session-"+active_sessions[i].id+"' data-session='"+active_sessions[i].id+"'>";
			as_display += "</div></div>";
			fetched.push(active_sessions[i].id);
		}
		$(".active-sessions-container").append(as_display);
		var opened = $(".panel-session").length;
		switch(opened){
			case 0:
				$(".sub-legend").html("<span></span> Aucun cours n'est ouvert");
				break;

			case 1:
				$(".sub-legend").html("<span></span> cours est actuellement ouvert");
				$(".sub-legend>span").html(opened);
				break;

			default:
				$(".sub-legend").html("<span></span> cours sont actuellements ouverts");
				$(".sub-legend>span").html(opened);
				break;
		}
		/*console.log(fetched);*/
		/*setTimeout(displaySessions, 5000, fetched);*/
		setTimeout(displaySessions, 900000, fetched);
	})
}

function fetchRecords(session_id){
	$("#body-session-"+session_id).collapse("toggle");
}

function refreshTick(openedSessions){
	/** To have up-to-date info on every non collapsed session, this function ensures the info is refreshed every so often. Of course, when something big such as a deletion is done, displayRecords can be called independently as it won't affect the global tick.
	**/
	/*console.log(openedSessions);*/
	for(var i = 0; i < openedSessions.length; i++){
		displayRecords(openedSessions[i]);
	}
	// The tick is set to every 60 seconds.
	setTimeout(refreshTick, 60000, openedSessions);
}

function displayRecords(session_id){
	if($("#body-session-"+session_id).hasClass("in")){
		$.get("functions/fetch_records_session.php", {session_id : session_id}).done(function(data){
			console.log("showing");
			var records_list = JSON.parse(data);
			$("#body-session-"+session_id).empty();
			var contents = "<div class='row session-list-container' id='session-"+session_id+">";
			contents += "<ul class='records-inside-list records-product-list'>";
			for(var i = 0; i < records_list.length; i++){
				var record_status;
				switch(records_list[i].status){
					case '0':
						record_status = "status-pre-success";
						break;

					case '2':
						record_status = "status-success";
						break;

					case '3':
						record_status = "status-over";
						break;
				}
				contents += "<li class='panel-item panel-record "+record_status+" container-fluid col-lg-2' id='session-record-"+records_list[i].id+"'>";
				contents += "<div class='small-user-pp'><img src='"+records_list[i].photo+"'></div>";
				contents += "<p class='col-lg-12 panel-item-title bf'>"+records_list[i].user+"</p>";
				contents += "<p class='col-lg-6 session-record-details'><span class='glyphicon glyphicon-time'></span> "+moment(records_list[i].date).format("HH:mm:ss")+"</p>";
				contents += "<p class='col-lg-6 session-record-details'><span class='glyphicon glyphicon-credit-card'></span> "+records_list[i].card+"</p>";
				contents += "<p class='col-lg-12 session-record-details'><span class='glyphicon glyphicon-queen'></span> "+records_list[i].product_name+"</p>";
				if(records_list[i].status == '2'){
					contents += "<p class='col-lg-3 panel-item-options' id='option-validate'><span class='glyphicon glyphicon-remove glyphicon-button' onclick='unvalidateRecord("+records_list[i].id+")' title='Annuler la validation'></span></p>";
				} else {
					contents += "<p class='col-lg-3 panel-item-options' id='option-validate'><span class='glyphicon glyphicon-ok glyphicon-button' onclick='validateRecord("+records_list[i].id+")' title='Valider le passage'></span></p>";
				}
				contents += "<p class='col-lg-3 panel-item-options'><span class='glyphicon glyphicon-arrow-right glyphicon-button' title='Changer le produit'></span></p>";
				contents += "<p class='col-lg-3 panel-item-options'><span class='glyphicon glyphicon-pushpin glyphicon-button' title='Changer le cours'></span></p>";
				contents += "<p class='col-lg-3 panel-item-options'><span class='glyphicon glyphicon-trash glyphicon-button' onclick='deleteRecord("+records_list[i].id+")' title='Supprimer le passage (IRREVERSIBLE)'></span></p>";
			}
			contents += "</ul>";
			contents += "</div>";
			$("#body-session-"+session_id).append(contents);
		})
	}
}

function validateRecord(record_id){
	$.post("functions/validate_record.php", {record_id : record_id}).done(function(product_id){
		$("#session-record-"+record_id).removeClass("status-pre-success");
		$("#session-record-"+record_id).removeClass("status-over");
		$("#session-record-"+record_id).addClass("status-success");
		$("#session-record-"+record_id+">#option-validate").html("<span class='glyphicon glyphicon-remove glyphicon-button' onclick='unvalidateRecord("+record_id+")' title='Annuler la validation'></span>")
		computeRemainingHours(product_id);
	})
}

function unvalidateRecord(record_id){
	$.post("functions/unvalidate_record.php", {record_id : record_id}).done(function(result){
		var data = JSON.parse(result);
		console.log(data);
		var status = data.status, product_id = data.product_id;
		$("#session-record-"+record_id).removeClass("status-success");
		if(status == 0){
			$("#session-record-"+record_id).addClass("status-pre-success");
			computeRemainingHours(product_id);
		} else {
			$("#session-record-"+record_id).addClass("status-over");
		}
		$("#session-record-"+record_id+">#option-validate").html("<span class='glyphicon glyphicon-ok glyphicon-button' onclick='validateRecord("+record_id+")' title='Valider le passage'></span>");
	})
}

function deleteRecord(record_id){
	if($("#session-record-"+record_id).hasClass("status-success")){
		unvalidateRecord(record_id);
	}
	$.post("functions/delete_record.php", {record_id}).done(function(){
		$("#session-record-"+record_id).remove();
	})
}
