<?php
	// Include confi.php
	include_once('confi.php');	
	$error_flag = false;
	$type = isset($_GET['type']) ? $_GET['type'] :  "";
	if(!empty($type)){
		switch($type) {
			case "stadium":
				$id = isset($_GET['id']) ? ($_GET['id']) :  "";
				if(!empty($id)){
					if ($id == "all") {
						$tsql ='select 
								c.city_id,
								v.venueName_es_MX as venue, 
								c.cityName_es_MX as city, 
								v.stadiumCapacity, 
								c.weather_id as weather_code,
								t.timezone
								from venue v
								inner join cities c on v.cityLocation = c.city_id
								inner join timezone t on t.timezone_id = c.timezone';
					} else if ($id<10 && $id >0){
						$tsql ="select venueName_es_MX as venue, cityName_es_MX as city, stadiumCapacity from venue inner join cities on cityLocation = city_id where venue_id = " . $id;
					}
					$getResults = sqlsrv_query($conn, $tsql);
					$result = array();
					while($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
						//extract($r);
						$result[] = array("city_id" => utf8_encode($row['city_id']), "venue" => utf8_encode($row['venue']), "city" => utf8_encode($row['city']), 'capacity' => utf8_encode($row['stadiumCapacity']), 'weather_code' => utf8_encode($row['weather_code']), 'timezone' => utf8_encode($row['timezone'])); 
					}
				} else {
					$error_flag = 1;
				}
				break;
			case "group":
				$group = isset($_GET['group']) ? ($_GET['group']) :  "";
				if(!empty($group)){
					if($group == "list") {
						$tsql = "select distinct group_id from groups";
						$getResults = sqlsrv_query($conn, $tsql);
						$result = array();
						while($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
							//extract($r);					
							$tsql2 = "select 
								concat(g.group_id,g.group_position) as groupId, 
								contryName_es_MX, 
								fifa_code 
								from groups g 
								inner join team 
								on g.team = team.fifa_code 
								where group_id='" . $row['group_id'] . "' order by group_id, group_position";
							$getResults2 = sqlsrv_query($conn, $tsql2);
							$result2 = array();
							while($row2 = sqlsrv_fetch_array($getResults2, SQLSRV_FETCH_ASSOC)){
								//extract($r);
								$result2[] = array("group" => utf8_encode($row2['groupId']), "team" => utf8_encode($row2['contryName_es_MX']), 'code' => utf8_encode($row2['fifa_code'])); 
							}
							$result[] = array("group" => utf8_encode($row['group_id']), "team" => $result2); 
						}
					} 
					else {
						$tsql = "select concat(g.group_id,g.group_position) as group_id, contryName_es_MX, fifa_code from groups g inner join team on g.team = team.fifa_code where group_id='".$group."' order by group_id, group_position";
						$getResults = sqlsrv_query($conn, $tsql);
						$result = array();
						while($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
							//extract($r);
							$result[] = array("group" => utf8_encode($row['group_id']), "team" => utf8_encode($row['contryName_es_MX']), 'code' => utf8_encode($row['fifa_code'])); 
						}
					}
				} else {
					$error_flag = 1;
				}				 
				break;
			case "weather":
				$id = isset($_GET['id']) ? ($_GET['id']) :  "";
				if(!empty($id)){
					$tsql = 'select IIF(weather_data_age > DATEADD(mi, -15, CURRENT_TIMESTAMP), 1, 0) as result
							from cities
							WHERE city_id = ' . $id;
					$getResults = sqlsrv_query($conn, $tsql);
					$recent;
					while($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
						//extract($r);
						$recent = $row['result'];
					} 
					//$recent = 0;
					if ($recent == 0) {
						$tsql = 'select c.weather_id as id
								from cities c
								WHERE city_id = (?)';
						$params = array($id);
						$getResults = sqlsrv_query($conn, $tsql, $params);
						$weather_id;
						while($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
							//extract($r);
							$weather_id = $row['id'];
							//echo $weather_id;
						}
						$url = 'https://api.openweathermap.org/data/2.5/weather?id=' . $weather_id . '&appid=7495c95edc81fd582b4c5084f3124745&units=metric&lang=es';
						$json = file_get_contents($url);
						$data = json_decode($json,true);
						
						$tsql = 'update cities
								set temperature = (?), weather_desc=(?), weather_desc_code = (?), weather_data_age = CURRENT_TIMESTAMP
								where city_id = (?)';
						$params = array($data['main']['temp'], $data['weather'][0]['description'], $data['weather'][0]['id'], $id);
						sqlsrv_query($conn, $tsql, $params);
					}

					$tsql = 'select c.temperature, 
							c.weather_desc as description, 
							d.weather_def_desc as icon
							from cities c
							inner join weather_icon i
							on c.weather_desc_code = i.weather_icon_code
							inner join weather_def d
							on i.weather_icon_fk_desc = d.weather_def_id
							where city_id = (?)';
						$params = array($id);
						$getResults = sqlsrv_query($conn, $tsql, $params);
					$result = array();
					while($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)){
						//extract($r);
						$result[] = array("temperature" => utf8_encode($row['temperature']), "description" => utf8_encode($row['description']), "icon" => utf8_encode($row['icon'])); 
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
	sqlsrv_free_stmt($getResults);

	/* Output header */
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($json);
?>