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



?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script type="text/javascript" src="/doujin/edit.js"></script>
<?php
print "<title>edit: $id</title>";
?>

</head>

<body>

<div align="center">
<table border="0"><tr><td>

<?php

print "<form action=\"/doujin/edit/$id/\" name=\"editform\" method=\"POST\">\n";

#if( ! $data ) {
#	errmsg( "当該のアイテムは存在しません" );
#	exit();
#}

$cmd = "";
if( isset($_POST["cmd"]) ) {
	$cmd = $_POST["cmd"];
}


if( $cmd == "edit" ) {
	load_from_post( $sqldata_write, "metatitle" );
	load_from_post( $sqldata_write, "title" );
	load_from_post( $sqldata_write, "subtitle" );
	load_from_post( $sqldata_write, "type" );
	load_from_post( $sqldata_write, "size" );
	load_from_post( $sqldata_write, "page" );
	load_from_post( $sqldata_write, "date" );
	load_from_post( $sqldata_write, "event_id" );
	load_from_post( $sqldata_write, "new_date" );
	load_from_post( $sqldata_write, "new_event_id" );
	load_from_post( $sqldata_write, "revision" );
	load_from_post( $sqldata_write, "group_id" );
	load_from_post( $sqldata_write, "volume" );
	load_from_post( $sqldata_write, "series_id" );
	load_from_post( $sqldata_write, "form" );
	load_from_post( $sqldata_write, "lang" );
	load_from_post( $sqldata_write, "side" );
	load_from_post( $sqldata_write, "adult" );
	load_from_post( $sqldata_write, "copibon" );
	load_from_post( $sqldata_write, "collection" );
	load_from_post( $sqldata_write, "collabo" );
	
	load_from_post( $sqldata_write, "printed" );
	
	$nc = load_from_post_multi( $sqldata_write, "circle_name" );
	load_from_post_multimax( $sqldata_write, "author_name", $nc );
	load_from_post_multimax( $sqldata_write, "circle_id", $nc );
	load_from_post_multimax( $sqldata_write, "author_id", $nc );
	
	load_from_post_multi( $sqldata_write, "original_id");
	load_from_post_multi( $sqldata_write, "original_sub_id");
	
	$ng = load_from_post_multi( $sqldata_write, "guest_circle_name" );
	load_from_post_multimax( $sqldata_write, "guest_name", $ng );
	load_from_post_multimax( $sqldata_write, "guest_circle_id", $ng );
	load_from_post_multimax( $sqldata_write, "guest_id", $ng );
	
	load_from_post_multi( $sqldata_write, "tag");
	load_from_post_multi( $sqldata_write, "ref_id");

	//return;
	export_to_db( $id, $sqldata_write );
}

import_from_db($id, $sqldata, $metadata);

print_input_n($sqldata, "metatitle", "メタ名", 50);
print_input_n($sqldata, "title", "誌名", 50);
print_input_n($sqldata, "subtitle", "サブタイトル", 50);
print_select($sqldata, "type", $types, "種別");
print_select($sqldata, "size", $sizes, "サイズ");
print_input($sqldata, "page", "ページ数");
print_input($sqldata, "date", "(初版)発行日");
print_input($sqldata, "event_id", "イベントID");
print_input($sqldata, "new_date", "再販日");
print_input($sqldata, "new_event_id", "再販イベントID");
print_input($sqldata, "revision", "版名");
print_input($sqldata, "group_id", "グループID");
print_input($sqldata, "volume", "巻名");
print_input($sqldata, "series_id", "シリーズID");
print_select($sqldata, "form", $forms, "形態");
print_select($sqldata, "lang", $langs, "言語");
print_select($sqldata, "side", $sides, "綴じ・方向");
print_select($sqldata, "adult", $yesno, "成年向け");
print_select($sqldata, "copibon", $yesnoinfo, "コピー本");
print_select($sqldata, "collection", $yesnoinfo, "総集編");
print_select($sqldata, "collabo", $yesnoinfo, "合同誌");

print_input_n($sqldata, "printed", "印刷所", 50);

print_circleauthor( $sqldata, "circle_name", "circle_id", "author_name", "author_id", "発行者情報" );

print_listinput( $sqldata, "original_id", "ジャンルID" );
print_listinput( $sqldata, "original_sub_id", "サブジャンルID" );

print_circleauthor( $sqldata, "guest_circle_name", "guest_circle_id", "guest_name", "guest_id", "ゲスト情報" );

print_listinput( $sqldata, "tag", "タグ" );

print_listinput( $sqldata, "ref_id", "参照" );

############################################################

function load_from_post_multi( &$sqldata, $name )
{
	$sqldata[$name] = array();
	for( $i=0; $i<1000000; $i++ ) {
		if( isset($_POST[$name."_$i"]) ) {
			$sqldata[$name][$i] = $_POST[$name."_$i"];
		} else {
			if( $i== 0 ) {
				$sqldata[$name][$i] = "";
				return 1;
			}
			return $i;
		}
	}
}

function load_from_post_multimax( &$sqldata, $name, $max )
{
	$sqldata[$name] = array();
	for( $i=0; $i<$max; $i++ ) {
		if( isset($_POST[$name."_$i"]) ) {
			$sqldata[$name][$i] = $_POST[$name."_$i"];
		} else {
			$sqldata[$name][$i] = "";
		}
	}
}

function load_from_post( &$sqldata, $name )
{
	if( isset($_POST[$name]) ) {
		$sqldata[ $name ] = $_POST[ $name ];
	} else {
		$sqldata[ $name ] = "undefined";
	}
}

function export_to_db( $id, $sqldata )
{
	global $dbh, $dbh_md;
	
	$sql_query = "UPDATE items set ";
	
	$keys = array_keys($sqldata);
	for( $i=0; $i<count($keys); $i++ ) {
		if( $i > 0 ) {
			$sql_query .= ",";
		}
		$sql_query .= $keys[$i];
		$value = $sqldata[ $keys[$i] ];
		
		$sql_query .= "=";
		
		if( $value == "" ) {
			$sql_query .= "null";
		} else {
			$sql_query .= "'";
			if( is_array( $value ) ) {
				for( $j=0; $j<count($value); $j++ ) {
					if( $j > 0 ) {
						$sql_query .= "/";
					}
					$sql_query .= sql_escape_multi( $value[$j] );
				}
			} else {
				$sql_query .= sql_escape($value);
			}
			$sql_query .= "'";
		}
	}
	$sql_query .= " where id=$id";
	#print "<br>$sql_query<br>\n";
	$res = $dbh->exec( $sql_query );
	if( $res ) {
		meta_update_date( "item", $id, time() );
		print "<div align=\"center\">アイテム情報を保存しました</div><br><br>\n";
	} else {
		errmsg("アイテム情報の保存に失敗しました");
	}

}

function import_from_db( $id, &$sqldata, &$metadata )
{
	global $dbh, $dbh_md;
	
	$res = $dbh->query( "SELECT * FROM items WHERE id = $id" );
	if( $sqldata = $res->fetch( PDO::FETCH_ASSOC ) ) {
		$res->closeCursor();
	}

	$res_md = $dbh_md->query( "SELECT * FROM items_metadata WHERE id = $id" );
	if( $metadata = $res_md->fetch( PDO::FETCH_ASSOC ) ) {
		$res_md->closeCursor();
	}
	
	sql_multi_split( $sqldata, "circle_id" );
	sql_multi_split( $sqldata, "circle_name" );
	sql_multi_split( $sqldata, "author_id" );
	sql_multi_split( $sqldata, "author_name" );
	sql_multi_split( $sqldata, "original_id" );
	sql_multi_split( $sqldata, "original_sub_id" );
	sql_multi_split( $sqldata, "guest_circle_id" );
	sql_multi_split( $sqldata, "guest_circle_name" );
	sql_multi_split( $sqldata, "guest_id" );
	sql_multi_split( $sqldata, "guest_name" );
	sql_multi_split( $sqldata, "tag" );
	sql_multi_split( $sqldata, "ref_id" );
	
	return array( $sqldata, $metadata );
}

function print_input( $sqldata, $name, $namestr )
{
	print_input_n( $sqldata, $name, $namestr, 15 );
}

function print_input_n( $sqldata, $name, $namestr, $size )
{
	if( empty($htmlname) ) {
		$htmlname = $name;
	}
	$value = html_escape( $sqldata[$name] );
	print "$namestr <input type=\"text\" name=\"{$name}\" value=\"". $sqldata[$name] ."\" size=\"$size\"><br>\n";
}

function print_circleauthor( $sqldata, $cname, $cid, $aname, $aid, $namestr )
{
	print "<br>\n$namestr<br>\n";
	print "<div id=\"div_{$cname}\">\n";
	if( ($n=count($sqldata[$cname])) <= 0 ) {
		$n = 1;
	}
	for( $i=0; $i < $n; $i++ ) {
		print "<div id=\"div_{$cname}_{$i}\">\n";
		print "<input type=\"text\" name=\"{$cname}_{$i}\" value=\"". html_escape($sqldata[$cname][$i]) ."\" size=\"25\">";
		print "<input type=\"text\" name=\"{$cid}_{$i}\" value=\"". html_escape($sqldata[$cid][$i]) ."\" size=\"10\">";
		print "<input type=\"text\" name=\"{$aname}_{$i}\" value=\"". html_escape($sqldata[$aname][$i]) ."\" size=\"25\">";
		print "<input type=\"text\" name=\"{$aid}_{$i}\" value=\"". html_escape($sqldata[$aid][$i]) ."\" size=\"10\">";
		print "<input type=\"button\" value=\"⏎\" onclick=\"add_line({$i}, ['{$cname}', 25, '{$cid}', 10, '{$aname}', 25, '{$aid}', 10]);\">";
		print "<input type=\"button\" value=\"×\" onclick=\"delete_line({$i}, ['{$cname}', 25, '{$cid}', 10, '{$aname}', 25, '{$aid}', 10]);\">";
		print "<br>\n</div>\n";
	}
	print "</div>\n";
}

function print_listinput( $sqldata, $name, $namestr )
{
	print "<br>\n$namestr<br>\n";
	print "<div id=\"div_{$name}\">\n";
	for( $i=0; $i < count($sqldata[$name]); $i++ ) {
		print "<div id=\"div_{$name}_{$i}\">\n";
		print "<input type=\"text\" name=\"{$name}_{$i}\" value=\"". html_escape($sqldata[$name][$i]) ."\" size=\"25\">";
		print "<input type=\"button\" value=\"⏎\" onclick=\"add_line({$i}, ['{$name}', 25]);\">";
		print "<input type=\"button\" value=\"×\" onclick=\"delete_line({$i}, ['{$name}', 25]);\">";
		print "<br>\n</div>\n";
	}
	print "</div>\n";
}

function print_select( $sqldata, $name, $list, $namestr )
{
	$keys = array_keys($list);
	$name_e = html_escape($name);
	print "$namestr <select name=\"".$name_e."\">\n";
	for( $i=0; $i < count($keys); $i++ ) {
		$value = html_escape($keys[$i]);
		$value_str = html_escape($list[$keys[$i]]);
		if( $sqldata[$name] == $keys[$i] ) {
			print "<option value=\"". $value ."\" selected>". $value_str ."</option>\n";
		} else {
			print "<option value=\"". $value ."\">". $value_str ."</option>\n";
		}
	}
	print "</select><br>\n";
}

function sql_multi_split( &$sqldata, $name )
{
	$t = explode("/", $sqldata[$name]);
	$sqldata[$name] = array();
	for( $i=0; $i < count($t); $i++ ) {
		$sqldata[$name][$i] = sql_multi_decode( $t[$i] );
	}
}

function sql_multi_decode($str)
{
	$str = preg_replace_callback(
        '/\|(.)/',
        function ($matches) {
			if( $matches[1] == "|" ) {
				return "|";
			} else if( $matches[1] == "(" ) {
				return "/";
			}
		},
        $str
    );
    return $str;
}

?>
<br><br>
<button type="submit" name="cmd" value="edit">登録</button>
</form>
</td></tr></table>
<br>

<?php

//$ct = $metadata["created_date"];
//$mt = $metadata["modified_date"];

//print "登録日: ". datestr($ct) ."<br>\n";
//print "最終更新日: ". datestr($mt) ."<br>\n";

?>

</div>

</body>
</html>