<?php
$serverName = "copamundial.database.windows.net";
$connectionOptions = array(
    "Database" => "copaMundialFutbol",
    "Uid" => "rmonter@copamundial",
    "PWD" => "Hqckint0sh!"
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions); 
$tsql= "select venueName_es_MX as venue, cityName_es_MX as city, stadiumCapacity from [dbo].[venue] inner join [dbo].[cities] on cityLocation = city_id";
$getResults= sqlsrv_query($conn, $tsql);
echo ("Reading data from table" . PHP_EOL);
if ($getResults == FALSE)
    echo (sqlsrv_errors());
while ($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)) {
 echo ($row['venue'] . " " . $row['city'] . PHP_EOL);
}
sqlsrv_free_stmt($getResults);
?>