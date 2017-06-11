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

$res = $dbh->query( "SELECT * FROM authors WHERE id = $id" );
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
	errmsg( "当該の著者は存在しません" );
	exit();
}

$aliases = array();
$circles = array();
$circles_inv = array();

$res = $dbh->query( "SELECT * FROM items WHERE author_id GLOB '$id' OR author_id GLOB '$id/*' OR author_id GLOB '*/$id/*' OR author_id GLOB '*/$id'" );
while( $item = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$circle_ids = explode( "/", $item["circle_id"] );
	$circle_name = explode( "/", $item["circle_name"] );
	$author_ids = explode( "/", $item["author_id"] );
	$author_name = explode( "/", $item["author_name"] );
	for( $i=0; $i<count($author_ids); $i++ ) {
		if( $author_ids[$i] == $id ) {
			if( $author_name[$i] != $data["name"] && !empty($author_name[$i]) ) {
				$aliases[$author_name[$i]] = 1;
			}
			if( !empty($circle_name[$i]) && empty($circles_inv[$circle_ids[$i]]) ) {
				$circles_inv[$circle_ids[$i]] = $circle_name[$i];
				$circles[$circle_name[$i]] = $circle_ids[$i];
			}
		}
	}
}
$res = $dbh->query( "SELECT * FROM items WHERE guest_id GLOB '$id' OR guest_id GLOB '$id/*' OR guest_id GLOB '*/$id/*' OR guest_id GLOB '*/$id'" );
while( $item = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$circle_ids = explode( "/", $item["guest_circle_id"] );
	$circle_name = explode( "/", $item["guest_circle_name"] );
	$author_ids = explode( "/", $item["guest_id"] );
	$author_name = explode( "/", $item["guest_name"] );
	for( $i=0; $i<count($author_ids); $i++ ) {
		if( $author_ids[$i] == $id ) {
			if( $author_name[$i] != $data["name"] && !empty($author_name[$i]) ) {
				$aliases[$author_name[$i]] = 1;
			}
			if( !empty($circle_name[$i]) && empty($circles_inv[$circle_ids[$i]]) ) {
				$circles_inv[$circle_ids[$i]] = $circle_name[$i];
				$circles[$circle_name[$i]] = $circle_ids[$i];
			}
		}
	}
}

print "<tr><td>著者</td><td>". $data["name"]."</td></tr>\n";

if( count( $keys=array_keys($aliases) ) > 0 ) {
	print "<tr><td>別名</td><td>";
	for( $i=0; $i < count($keys); $i++ ) {
		if( $i >= 1 ) { print "<br>"; }
		print $keys[$i];
	}
	print "</td></tr>\n";
}

if( count( $keys=array_keys($circles) ) > 0 ) {
	print "<tr><td>所属サークル</td><td>";
	for( $i=0; $i < count($keys); $i++ ) {
		if( $i >= 1 ) { print "<br>"; }
		print "<a href=\"/doujin/circle/".$circles[$keys[$i]]."/\">".$keys[$i]."</a>";
	}
	print "</td></tr>\n";
}

?>

</table>

<?php

print "<br><a href=\"/doujin/list/item/author/$id/\">この著者の作品</a>\n";

?>

</div>

</body>
</html>