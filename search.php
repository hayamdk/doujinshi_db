<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>search</title>
</head>

<body>

<div align="center">

アイテムを検索する

<form action="/doujin/search/" method="GET">
検索条件:

<?php

include 'func.php';

$conds = array( "title" => "タイトル", "circle" => "サークル", "author" => "著者" );

print "<select name=\"cond\">\n";
foreach( $conds as $key => $value ){
	if( isset($_GET["cond"]) && $_GET["cond"] == $key ) {
		print "<option value=\"$key\" selected>".html_escape($value)."</option>\n";
	} else {
		print "<option value=\"$key\">".html_escape($value)."</option>\n";
	}
}
print "</select>\n";

if( ! empty($_GET["word"]) ) {
	print "<input type=\"text\" name=\"word\" value=\"". html_escape($_GET["word"]) ."\">";
} else {
	print "<input type=\"text\" name=\"word\">";
}

?>

<button type="submit">検索</button>
</form>

<?php

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

if( !empty($_GET["cond"]) ) {
	$cond = $_GET["cond"];
}
if( !empty($_GET["word"]) ) {
	$word = $_GET["word"];
} else {
	$word = "";
}

if( empty($cond) ) {
	# do nothing
} else if( $cond == "title" ) {
	$word_html = htmlentities($word, ENT_QUOTES, "UTF-8");
	$word = sql_escape_like( $word );
	
	print "検索結果（タイトルに<b>'$word_html'</b>を含む）：<br><br>";
	
	$res = $dbh->query( "SELECT * FROM items WHERE metatitle LIKE '%$word%' ESCAPE '\\' OR title LIKE '%$word%' ESCAPE '\\' OR subtitle LIKE '%$word%' ESCAPE '\\'" );
	print_items( $res );
} else if( $cond == "circle" ) {
	$word_html = htmlentities($word, ENT_QUOTES, "UTF-8");
	$word = sql_escape_multilike( $word );
	
	print "検索結果（サークル名に<b>'$word_html'</b>を含む）：<br><br>";
	
	$res = $dbh->query( "SELECT * FROM items WHERE circle_name LIKE '%$word%' ESCAPE '\\' OR guest_circle_name LIKE '%$word%' ESCAPE '\\'" );
	print_items( $res );
} else if( $cond == "author" ) {
	$word_html = htmlentities($word, ENT_QUOTES, "UTF-8");
	$word = sql_escape_multilike( $word );
	
	print "検索結果（著者名に<b>'$word_html'</b>を含む）：<br><br>";
	
	$res = $dbh->query( "SELECT * FROM items WHERE author_name LIKE '%$word%' ESCAPE '\\' OR guest_name LIKE '%$word%' ESCAPE '\\'" );
	print_items( $res );
}

?>

</div>

</body>
</html>