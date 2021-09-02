<?php

include("db.php");

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array( ':prefix' => $_REQUEST['term']."%"));
$retval = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) 
{
    $retval[] = $row['name'];
}

echo(json_encode($retval, JSON_PRETTY_PRINT));
// Json pretty print helps to format json string
//json_encode is used to encode a value to JSON format