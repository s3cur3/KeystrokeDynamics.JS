<?php
$allLinesInLog = file( '/opt/bitnami/apache2/logs/error_log' );

echo "<html><head><meta http-equiv=\"refresh\" content=\"20\"></head><body style=\"font-family:monospace;\">";

for( $i = sizeof($allLinesInLog) - 12; $i < sizeof($allLinesInLog); $i++ ) {
    echo $allLinesInLog[$i], "<br />";
}

echo "<br /><br /><br /><br /><br />";

$allLinesInLog = file( '/opt/bitnami/mysql/data/mysqld.log' );

for( $i = sizeof($allLinesInLog) - 12; $i < sizeof($allLinesInLog); $i++ ) {
    echo $allLinesInLog[$i], "<br />";
}
echo "</body></html>";
?>