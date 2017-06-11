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

$res = $dbh->query( "SELECT * FROM circles WHERE id = $id" );
if( $data = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$res->closeCursor();
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
print "<title>".$data["name"]."</title>";
?>

</head>

<body>

<div align="center">
<table border="1">

<?php

if( ! $data ) {
	errmsg( "当該のサークルは存在しません" );
	exit();
}

$aliases = array();
$members = array();
$members_inv = array();

$res = $dbh->query( "SELECT * FROM items WHERE circle_id GLOB '$id' OR circle_id GLOB '$id/*' OR circle_id GLOB '*/$id/*' OR circle_id GLOB '*/$id'" );
while( $item = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$circle_ids = explode( "/", $item["circle_id"] );
	$circle_name = explode( "/", $item["circle_name"] );
	$author_ids = explode( "/", $item["author_id"] );
	$author_name = explode( "/", $item["author_name"] );
	for( $i=0; $i<count($circle_ids); $i++ ) {
		if( $circle_ids[$i] == $id ) {
			if( $circle_name[$i] != $data["name"] ) {
				$aliases[$circle_name[$i]] = 1;
			}
			if( empty($members_inv[$author_ids[$i]]) ) {
				$members[$author_name[$i]] = $author_ids[$i];
				$members_inv[$author_ids[$i]] = $author_name[$i];
			}
		}
	}
}
$res = $dbh->query( "SELECT * FROM items WHERE guest_circle_id GLOB '$id' OR guest_circle_id GLOB '$id/*' OR guest_circle_id GLOB '*/$id/*' OR guest_circle_id GLOB '*/$id'" );
while( $item = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$circle_ids = explode( "/", $item["guest_circle_id"] );
	$circle_name = explode( "/", $item["guest_circle_name"] );
	$author_ids = explode( "/", $item["guest_id"] );
	$author_name = explode( "/", $item["guest_name"] );
	for( $i=0; $i<count($circle_ids); $i++ ) {
		if( $circle_ids[$i] == $id ) {
			if( $circle_name[$i] != $data["name"] ) {
				$aliases[$circle_name[$i]] = 1;
			}
			if( empty($members_inv[$author_ids[$i]]) ) {
				$members[$author_name[$i]] = $author_ids[$i];
				$members_inv[$author_ids[$i]] = $author_name[$i];
			}
		}
	}
}

print "<tr><td>サークル名</td><td>". $data["name"]."</td></tr>\n";

if( count( $keys=array_keys($aliases) ) > 0 ) {
	print "<tr><td>別名</td><td>";
	for( $i=0; $i < count($keys); $i++ ) {
		if( $i >= 1 ) { print "<br>"; }
		print $keys[$i];
	}
	print "</td></tr>\n";
}

if( count( $keys=array_keys($members) ) > 0 ) {
	print "<tr><td>メンバー</td><td>";
	for( $i=0; $i < count($keys); $i++ ) {
		if( $i >= 1 ) { print "<br>"; }
		print "<a href=\"/doujin/author/".$members[$keys[$i]]."/\">".$keys[$i]."</a>";
	}
	print "</td></tr>\n";
}

?>

</table>

<?php

print "<br><a href=\"/doujin/list/item/circle/$id/\">このサークルの作品</a>\n";

?>

</div>

</body>
</html>