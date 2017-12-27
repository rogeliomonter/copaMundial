<?php
// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "rmonter@copamundial", "pwd" => "Hqckint0sh!", "Database" => "copaMundialFutbol", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:copamundial.database.windows.net,1433";

$conn = sqlsrv_connect($serverName, $connectionInfo);
?>