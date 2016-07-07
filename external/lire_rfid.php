<?php
require_once "../Salsabor/functions/db_connect.php";
include "../Salsabor/functions/tools.php";
$db = PDOFactory::getConnection();

$data = explode('*', $_GET["carte"]);
$tag_rfid = $data[0];
$reader_token = $data[1];

//prepareParticipation($db, $tag_rfid, $reader_token);
prepareParticipationBeta($db, $tag_rfid, $reader_token);

function prepareParticipation($db, $user_tag, $reader_token){
	$today = date("Y-m-d H:i:s");
	//$limit = date("Y-m-d H:i:s", strtotime($today.'+20MINUTES'));
	if($reader_token == "192.168.0.3"){
		$status = "1";
		$new = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, status)
					VALUES('$user_tag', '$reader_token', '$today', '$status')");
		echo $ligne = $today.";".$user_tag.";".$reader_token."$";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session = $db->query("SELECT cours_intitule, cours_id FROM cours c
								JOIN rooms r ON c.cours_salle = r.room_id
								JOIN readers re ON r.room_reader = re.reader_id
								WHERE ouvert = '1' AND reader_token = '$reader_token'")->fetch(PDO::FETCH_GROUP);
		$cours_name = $session["cours_intitule"];
		$session_id = $session["cours_id"];
		$user_details = $db->query("SELECT user_id, mail FROM users WHERE user_rfid = '$user_tag'")->fetch(PDO::FETCH_ASSOC);

		if(preg_match("/@/", $user_details["mail"], $matches)){
			$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('MAI', '$user_details[user_id]', '$today', '1')");
		}

		// Ok, we got everything, let's look for potential duplicates
		$duplicates = $db->query("SELECT * FROM participations WHERE user_rfid = '$user_tag' AND cours_id = '$session_id'")->rowCount();

		if($duplicates > 0){
			echo $ligne = $today.";".$user_tag.";".$reader_token."$-3";
		} else {
			addParticipation($db, $cours_name, $session_id, $user_details["user_id"], $reader_token, $user_tag);
		}
	}
}

function prepareParticipationBeta($db, $user_tag, $reader_token){
	$today = date("Y-m-d H:i:s");
	if($reader_token == "192.168.0.3"){
		$status = "1";
		$new = $db->query("INSERT INTO participations(user_rfid, room_token, passage_date, status)
					VALUES('$user_tag', '$reader_token', '$today', '$status')");
		echo "$";
	} else {
		// If the tag is not for associating, we search a product that could be used for this session.
		// First, we get the name of the session and the ID of the user.
		// For the session, we have to find it based on the time of the record and the position.
		$session = $db->query("SELECT cours_id FROM cours c
								JOIN rooms r ON c.cours_salle = r.room_id
								WHERE ouvert = '1' AND room_reader = '$reader_token'")->fetch(PDO::FETCH_COLUMN);
		$session_id = $session["cours_id"];
		$user_details = $db->query("SELECT user_id, mail FROM users WHERE user_rfid = '$user_tag'")->fetch(PDO::FETCH_ASSOC);

		if(!preg_match("/@/", $user_details["mail"], $matches)){
			$notification = $db->query("INSERT IGNORE INTO team_notifications(notification_token, notification_target, notification_date, notification_state)
								VALUES('MAI', '$user_details[user_id]', '$today', '1')");
		}

		addParticipationBeta($db, $today, $session_id, $user_details["user_id"], $reader_token, $user_tag);
		/*addParticipation($db, $cours_name, $session_id, $user_details["user_id"], $reader_token, $tag);*/
	}
}
// The reader expects this:
//echo $ligne = $today.";".$tag_rfid.";".$ip_rfid."$";
