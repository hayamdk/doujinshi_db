<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>list</title>
</head>

<body>

<div align="center">


アイテムを検索する

<form action="/doujin/search/" method="GET">
検索条件:

<select name="cond">
<option value="title">タイトル</option>
<option value="circle">サークル</option>
<option value="author">著者</option>
</select>

<input type="text" name="word">

<button type="submit">検索</button>
</form>


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
$args = explode( "/", $query );

if( $args[0] == "item" && empty($args[1]) ) {
	print "全作品リスト<br><br>";
	$res = $dbh->query( "SELECT * FROM items ORDER BY date ASC" );
	print_items( $res );
} else if( $args[0] == "circle" ) {
} else if( $args[0] == "author" ) {
} else if( $args[0] == "item" ) {
	if( $args[1] == "circle" ) {
		$search = "circle";
		$id = $args[2];
		if( ! check_circleid($id) ) {
			errmsg("サークルが見つかりません");
		} else {
			print "サークル '".get_circlename($id)."' の作品リスト<br><br>";
			$res = $dbh->query( "SELECT * FROM items WHERE circle_id GLOB '$id' OR circle_id GLOB '$id/*' OR circle_id GLOB '*/$id/*' OR circle_id GLOB '*/$id'" );
			print_items( $res );
			
			print "<br><br>サークル '".get_circlename($id)."' のゲスト作品リスト<br><br>";
			$res = $dbh->query( "SELECT * FROM items WHERE guest_circle_id GLOB '$id' OR guest_circle_id GLOB '$id/*' OR guest_circle_id GLOB '*/$id/*' OR guest_circle_id GLOB '*/$id'" );
			print_items( $res );
		}
	} else if( $args[1] == "author" ) {
		$search = "circle";
		$id = $args[2];
		if( ! check_authorid($id) ) {
			errmsg("著者が見つかりません");
		} else {
			print "著者 '".get_authorname($id)."' の作品リスト<br><br>";
			$res = $dbh->query( "SELECT * FROM items WHERE author_id GLOB '$id' OR author_id GLOB '$id/*' OR author_id GLOB '*/$id/*' OR author_id GLOB '*/$id'" );
			print_items( $res );
			
			print "<br><br>著者 '".get_authorname($id)."' のゲスト作品リスト<br><br>";
			$res = $dbh->query( "SELECT * FROM items WHERE guest_id GLOB '$id' OR guest_id GLOB '$id/*' OR guest_id GLOB '*/$id/*' OR guest_id GLOB '*/$id'" );
			print_items( $res );
		}
	}
} else {
	errmsg("不正な引数です");
	exit();
}

?>

</div>

</body>
</html>