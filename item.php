<?php

include 'func.php';

$dbname = "./db/doujin.db";
$dbname_md = "./db/metadata.db";
$dbh;
$dbh_md;

try {
	$dbh = new PDO("sqlite:$dbname");
}
catch( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
	$dbh_md = new PDO("sqlite:$dbname_md");
}
catch( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}
$dbh_md->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = $_SERVER["QUERY_STRING"];
$id = explode( "/", $query );

if( isnum($id[0]) ) {
	$id = intval($id[0]);
} else {
	$id = 0;
}

$res = $dbh->query( "SELECT * FROM items WHERE id = $id" );
if( $data = $res->fetch( PDO::FETCH_ASSOC ) ) {
	$res->closeCursor();
}

$res_md = $dbh_md->query( "SELECT * FROM items_metadata WHERE id = $id" );
if( $metadata = $res_md->fetch( PDO::FETCH_ASSOC ) ) {
	$res_md->closeCursor();
}

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
print "<title>".$data["title"]."</title>";
?>

</head>

<body>

<div align="center">
<table border="1">

<?php

if( ! $data ) {
	errmsg( "当該のアイテムは存在しません" );
	exit();
}

if( is_localaddr() ) {
	print "<a href=\"/doujin/edit/$id/\">編集</a><br><br>\n";
}

print "<tr><td>誌名</td><td>";
if( $data["metatitle"] ) {
	print $data["metatitle"]."<BR>";
}
print $data["title"]." <i>".$data["subtitle"]."</i></td></tr>\n";

print "<tr><td>種別</td><td>". $types[$data["type"]]."</td></tr>\n";
print "<tr><td>サイズ</td><td>".$sizes[$data["size"]]."</td></tr>\n";
print "<tr><td>ページ数</td><td>". $data["page"]."</td></tr>\n";
print "<tr><td>発行日</td><td>". $data["date"]."</td></tr>\n";
if( ! empty( $data["event_id"] ) ) {
	print "<tr><td>イベント</td><td>". get_event( $data["event_id"] )."</td></tr>\n";
}
print "<tr><td>再販日</td><td>". $data["new_date"]."　</td></tr>\n";
if( ! empty( $data["new_event_id"] ) ) {
	print "<tr><td>再販イベント</td><td>". get_event( $data["new_event_id"] )."</td></tr>\n";
}
if( $data["group_id"] ) {
	print "<tr><td>版数</td><td><a href=\"/doujin/group/".$data["group_id"]."/\">".$data["revision"]."</a></td></tr>\n";
} else {
	print "<tr><td>版数</td><td>". $data["revision"]."　</td></tr>\n";
}
if( $data["group_id"] ) {
	print "<tr><td>巻数</td><td><a href=\"/doujin/series/".$data["series_id"]."/\">".$data["volume"]."</a></td></tr>\n";
} else {
	print "<tr><td>巻数</td><td>". $data["volume"]."　</td></tr>\n";
}
print "<tr><td>形態</td><td>". $forms[$data["form"]]."</td></tr>\n";
print "<tr><td>言語</td><td>". $langs[$data["lang"]]."</td></tr>\n";
print "<tr><td>綴じ</td><td>". $sides[$data["side"]]."</td></tr>\n";
print "<tr><td>成年向け</td><td>". $yesno[$data["adult"]]."</td></tr>\n";
print "<tr><td>コピー本</td><td>". $yesnoinfo[$data["copibon"]]."</td></tr>\n";
print "<tr><td>総集編</td><td>". $yesnoinfo[$data["collection"]]."</td></tr>\n";
print "<tr><td>合同誌</td><td>". $yesnoinfo[$data["collabo"]]."</td></tr>\n";
print "<tr><td>印刷所</td><td>". $data["printed"]."　</td></tr>\n";

$circles = explode("/", $data["circle_name"]);
$authors = explode("/", $data["author_name"]);
$circle_ids = explode("/", $data["circle_id"]);
$author_ids = explode("/", $data["author_id"]);
$n = count( $authors );
print "<tr><td>サークル／著者</td><td>";
for( $i=0; $i<$n; $i++ ) {
	print "<a href=\"/doujin/circle/".$circle_ids[$i]."\">".$circles[$i]."</a> ／ ".
		"<a href=\"/doujin/author/".$author_ids[$i]."\">".$authors[$i]."</a><br>\n";
}

print "<tr><td>原作</td><td>";
if( !empty($data["original_id"]) ) {
	$originals = explode("/", $data["original_id"]);
	$n = count( $originals );
	for( $i=0; $i<$n; $i++ ) {
		if( $i >= 1 ) { print "<br>\n"; }
		print get_original($originals[$i]);
	}
}
print "　</td></tr>\n";

print "<tr><td>原作(サブ)</td><td>";
if( !empty($data["original_sub_id"]) ) {
	$originals = explode("/", $data["original_sub_id"]);
	$n = count( $originals );
	for( $i=0; $i<$n; $i++ ) {
		if( $i >= 1 ) { print "<br>\n"; }
		print get_original($originals[$i]);
	}
}
print "　</td></tr>\n";

print "<tr><td>ゲスト</td><td>";
if( $data["guest_circle_name"] || $data["guest_name"] ) {
	$gcircles = explode("/", $data["guest_circle_name"]);
	$gauthors = explode("/", $data["guest_name"]);
	$gcircle_ids = explode("/", $data["guest_circle_id"]);
	$gauthor_ids = explode("/", $data["guest_id"]);
	$n = count( $gauthors );
	for( $i=0; $i<$n; $i++ ) {
		if( $i >= 1 ) { print "<br>\n"; }
		print "<a href=\"/doujin/circle/".$gcircle_ids[$i]."\">".$gcircles[$i]."</a> ／ ".
			"<a href=\"/doujin/author/".$gauthor_ids[$i]."\">".$gauthors[$i]."</a>\n";
	}
}
print "　</td></tr>\n";

$tags = explode("/", $data["tag"]);
$n = count( $tags );
print "<tr><td>タグ</td><td>";
for( $i=0; $i<$n; $i++ ) {
	print $tags[$i]."<br>\n";
}

print "<tr><td>参照</td><td>";
if( $data["ref_id"] ) {
	$refs = explode("/", $data["ref_id"]);
	$n = count( $refs );
	for( $i=0; $i<$n; $i++ ) {
		if( $refs[$i] ) {
			if( $i >= 1 ) { print "<br>\n"; }
			print "<a href=\"/doujin/item/".$refs[$i]."/\">".get_itemname($refs[$i])."</a>";
		}
	}
}
print "　</td></tr>\n";

?>

</table>
<br>

<?php

$ct = $metadata["created_date"];
$mt = $metadata["modified_date"];

print "登録日: ". datestr($ct) ."<br>\n";
print "最終更新日: ". datestr($mt) ."<br>\n";

?>

</div>

</body>
</html>