<?php
	// Include confi.php
	include_once('confi.php');	
	$error_flag = false;
	$type = isset($_GET['type']) ? $_GET['type'] :  "";
	if(!empty($type)){
		switch($type) {
			case "stadium":
				$id = isset($_GET['id']) ? mysql_real_escape_string($_GET['id']) :  "";
				if(!empty($id)){
					if ($id == "all") {
						$qur = mysql_query("select venueName_es_MX as `venue`, cityName_es_MX as `city`, stadiumCapacity from `venue` join `cities` where cityLocation = city_id");
					} else if ($id<10 && $id >0){
						$qur = mysql_query("select venueName_es_MX, cityName_es_MX, stadiumCapacity from `venue` join `cities` where cityLocation = city_id and venue_id = " . $id);
					}
					$result = array();
					while($r = mysql_fetch_array($qur)){
						extract($r);
						$result[] = array("venue" => $venue, "city" => $city, 'capacity' => $stadiumCapacity); 
					}
				} else {
					$error_flag = 1;
				}
				break;
			case "group":
				$group = isset($_GET['group']) ? mysql_real_escape_string($_GET['group']) :  "";
				if(!empty($group)){
					$qur = mysql_query("select concat(group_id,group_position) as `group`, contryName_es_MX, fifa_code from `groups` join `team` on groups.team = team.fifa_code where group_id='" . $group . "' order by group_id, group_position");
					$result = array();
					while($r = mysql_fetch_array($qur)){
						extract($r);
						$result[] = array("group" => $group, "team" => $contryName_es_MX, 'code' => $fifa_code); 
					}
				} else {
					$error_flag = 1;
				}				 
				break;
		}
	}
	if(!$error_flag) {
		$json = array("status" => 1, "data" => $result);
	}else{
		$json = array("status" => 0, "msg" => "An error has ocurred. ", "err_msg" => mysql_error());
	}
	@mysql_close($conn);

	/* Output header */
	header('Content-type: application/json');
	echo json_encode($json);
