<?php

include 'func.php';

$dbname = "./db/doujin.db";
$dbh;

try {
	$dbh = new PDO("sqlite:$dbname");
}
catch( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = $_SERVER["QUERY_STRING"];
$id = explode( "/", $query );

if( isnum($id[0]) ) {
	$id = intval($id[0]);
} else {
	$id = 0;
}

$res = $dbh->query( "SELECT * FROM groups WHERE id = $id" );
if( $data = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$res->closeCursor();
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
print "<title>group:".$data["id"]."</title>";
?>

</head>

<body>

<div align="center">

<?php

if( ! $data ) {
	errmsg( "当該のグループは存在しません" );
	exit();
}

$res = $dbh->query( "SELECT * FROM items WHERE group_id = '$id'" );
print_items( $res );

?>

</div>

</body>
</html>
