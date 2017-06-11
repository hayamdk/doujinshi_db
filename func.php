<?php

$types = array( "book" => "同人誌" );
$sizes = array( "a5" => "A5", "b5" => "B5", "b5l" => "B5(横)", "a4" => "A4", "undef" => "不定形" );
$forms = array( "manga" => "漫画", "illust" => "イラスト", "novel" => "小説", "crit" => "評論", "multi" => "その他、複合" );
$langs = array( "jpn" => "日本語", "eng" => "英語", "chi_t" => "中国語（繁体字）", "multi" => "複合" );
$sides = array( "right" => "右綴じ", "left" => "左綴じ", "top" => "上綴じ", "multi" => "その他、複合" );
$yesno = array( "no" => "いいえ", "yes" => "はい" );
$yesnoinfo = array( "noinfo" => "情報なし", "yes" => "はい" );

function check_exist_group( $id )
{
	global $dbh;
	
	if( ! isnum($id) ) {
		return 0;
	}

	$res = $dbh->query( "SELECT * FROM groups WHERE id = $id" );
	if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
		$res->closeCursor();
		return 1;
	}
	$res->closeCursor();
	return 0;
}

function check_exist_series( $id )
{
	global $dbh;
	
	if( ! isnum($id) ) {
		return 0;
	}

	$res = $dbh->query( "SELECT * FROM series WHERE id = $id" );
	if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
		$res->closeCursor();
		return 1;
	}
	$res->closeCursor();
	return 0;
}

function get_itemname( $id )
{
	return get_name( "item", $id, "title" );
}

function get_authorname( $id )
{
	return get_name( "author", $id, "name" );
}

function get_circlename( $id )
{
	return get_name( "circle", $id, "name" );
}

function get_original( $id )
{
	return get_name( "original", $id, "name" );
}

function get_event( $id )
{
	return get_name( "event", $id, "name" );
}

function get_name( $item, $id, $namefield )
{
	global $dbh;
	
	if( ! isnum($id) ) {
		return "error";
	}

	$res = $dbh->query( "SELECT * FROM {$item}s WHERE id = $id" );
	if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
		$res->closeCursor();
		return $row{ $namefield };
	}
	$res->closeCursor();
	return "";
}

function isnum($str)
{
	if( ! ctype_digit($str) ) {
		return 0;
	}
	$num = intval($str);
	if( $num <= 0 ) {
		return 0;
	}
	return 1;
}

function isdate($datestr)
{
	if( ! isnum($datestr) ) {
		return 0;
	}
	$datenum = intval( $datestr );
	if($datenum < 10000) {
		$datenum *= 100;
	}
	
	$day = $datenum % 100;
	$month = floor($datenum / 100) % 100;
	$year = floor($datenum / 10000);
	if( $day == 0 ) {
		$day = 1;
	}
	return checkdate( $month, $day, $year );
}


function add_group()
{
	global $dbh;
	
	try {
		$dbh->exec("BEGIN DEFERRED;");
		$res = $dbh->query( "INSERT INTO groups(id) VALUES(null)" );
		$res = $dbh->query( "SELECT * FROM groups ORDER BY id DESC" );
		if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
			$res->closeCursor();
			$dbh->exec("COMMIT;");
			meta_create( "group" , intval($row{"id"}) );
			return intval($row{"id"});
		}
	} catch (Exception $e) {
		$dbh->exec("ROLLBACK;");
		errmsg($e->getTraceAsString());
		exit();
	}
	$dbh->exec("ROLLBACK;");
	$res->closeCursor();
	return -1;
}

function add_series( $series_name )
{
	return add_item( "serie", $series_name );
}

function add_original( $author_name )
{
	return add_item( "original", $author_name );
}

function add_author( $author_name )
{
	return add_item( "author", $author_name );
}

function add_circle( $circle_name )
{
	return add_item( "circle", $circle_name );
}

function add_event( $event_name )
{
	return add_item( "event", $event_name );
}

function add_item($item, $name)
{
	global $dbh;
	$name = sql_escape($name);

	try {
		$dbh->exec("BEGIN DEFERRED;");
		$res = $dbh->query( "INSERT INTO {$item}s(name) VALUES('$name')" );
		$res = $dbh->query( "SELECT * FROM {$item}s WHERE name = '$name' ORDER BY id DESC" );
		if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
			$res->closeCursor();
			$dbh->exec("COMMIT;");
			meta_create( $item, intval($row{"id"}) );
			return intval($row{"id"});
		}
	} catch (Exception $e) {
		$dbh->exec("ROLLBACK;");
		errmsg($e->getTraceAsString());
		exit();
	}
	$dbh->exec("ROLLBACK;");
	$res->closeCursor();
	return -1;
}

function check_originalid( $id )
{
	return check_id( "original", $id );
}

function check_authorid( $id )
{
	return check_id( "author", $id );
}

function check_circleid( $id )
{
	return check_id( "circle", $id );
}

function check_eventid( $id )
{
	return check_id( "event", $id );
}

function check_itemid( $id )
{
	return check_id( "item", $id );
}

function check_id( $item, $id )
{
	global $dbh;
	
	if( ! isnum($id) ) {
		return 0;
	}

	$res = $dbh->query( "SELECT * FROM {$item}s WHERE id = $id" );
	if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
		$res->closeCursor();
		return 1;
	}
	$res->closeCursor();
	return 0;
}

function errmsg($str)
{
	print "<font color=\"#ff0000\"><b>$str</b></font><br>\n";
}

function html_hidden_value( $name, $value )
{
	return "<input type=\"hidden\" name=\"". 
		htmlentities($name, ENT_QUOTES, "UTF-8") ."\" value=\"".
		htmlentities($value, ENT_QUOTES, "UTF-8")."\">";
}

function check_circle( $circle_name )
{
	return check_circle_author( $circle_name, "circle", 1 );
}

function check_author( $author_name )
{
	return check_circle_author( $author_name, "author", 1 );
}

function check_original( $original )
{
	return check_circle_author( $original, "original", 0 );
}

function check_event( $event )
{
	return check_circle_author( $event, "event", 0 );
}

function check_circle_author($name, $str, $usealias)
{
	global $dbh;
	$name = sql_escape($name);
	
	try {
		$res = $dbh->query( "SELECT * FROM {$str}s WHERE name = '$name'" );
		if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
			$res->closeCursor();
			return intval($row["id"]);
		}
		$res->closeCursor();
		
		if( $usealias ) {
			$res = $dbh->query( "SELECT * FROM items WHERE {$str}_name like '%$name%'" );
			if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
				$list = explode( "/", $row["{$str}_name"]);
				$idlist = explode( "/", $row["{$str}_id"]);
				for( $i=0; $i<count($list); $i++ ) {
					if( $list[$i] == $name ) {
						$res->closeCursor();
						return intval( $idlist[$i] );
					}
				}
			}
			$res->closeCursor();
		}
		return -1;
	}
	catch( PDOException $e ) {
		echo 'Connection failed: ' . $e->getMessage();
		exit();
	}
}

function sql_escape( $str )
{
	$str_e = $str;
	$str_e = str_replace( "'", "''", $str_e );
	return $str_e;
}

function sql_escape_like( $str )
{
	$str_e = $str;
	$str_e = str_replace( "'", "''", $str_e );
	$str_e = str_replace( "\\", "\\\\", $str_e );
	$str_e = str_replace( "%", "\\%", $str_e );
	$str_e = str_replace( "_", "\\_", $str_e );
	return $str_e;
}

function sql_escape_multilike( $str )
{
	$str_e = $str;
	$str_e = str_replace( "'", "''", $str_e );
	$str_e = str_replace( "|", "||", $str_e );
	$str_e = str_replace( "/", "|(", $str_e );
	$str_e = str_replace( "\\", "\\\\", $str_e );
	$str_e = str_replace( "%", "\\%", $str_e );
	$str_e = str_replace( "_", "\\_", $str_e );
	return $str_e;
}

function sql_escape_multi( $str )
{
	$str_e = $str;
	$str_e = str_replace( "'", "''", $str_e );
	$str_e = str_replace( "|", "||", $str_e );
	$str_e = str_replace( "/", "|(", $str_e );
	return $str_e;
}

function sql_escape_multiglob( $str )
{
	$str_e = $str;
	$str_e = str_replace( "'", "''", $str_e );
	$str_e = str_replace( "|", "||", $str_e );
	$str_e = str_replace( "/", "|(", $str_e );
	$str_e = str_replace( "[", "[[]", $str_e );
	$str_e = str_replace( "]", "[]]", $str_e );
	$str_e = str_replace( "?", "[?]", $str_e );
	return $str_e;
}

function sqlstr($str)
{
	$str = sql_escape($str);
	if( ! $str ) {
		return "null";
	} else {
		return "'$str'";
	}
}

function sqlnum($value)
{
	if( ! isnum($value) ) {
		return "null";
	} else {
		return "$value";
	}
}

function print_items( $res )
{
	global $sizes, $yesno;
	
	$str = "";
	$lines = 0;

	$str .= "<table border=\"1\"><tr><td>タイトル</td><td>ページ数</td><td>発行日</td><td>発行イベント</td><td>サイズ</td><td>サークル／著者</td><td>版数</td><td>シリーズ</td><td>R-18</td></tr>\n";
	while( $data = $res->fetch( PDO::FETCH_ASSOC ) ) {
		$str .= "<tr><td><a href=\"/doujin/item/".$data["id"]."/\"><b>".$data["title"]."</b></a></td>".
				"<td>".$data["page"]."</td>".
				"<td>".$data["date"]."</td>";
		if( ! empty($data["event_id"]) ) {
			$str .= "<td>".get_event($data["event_id"])."</td>";
		} else { $str .= "<td>　</td>"; };
		$str .= "<td>".$sizes[$data["size"]]."</td>".
				"<td>";
				
		$circles = explode( "/", $data["circle_name"] );
		$circle_ids = explode( "/", $data["circle_id"] );
		$authors = explode( "/", $data["author_name"] );
		$author_ids = explode( "/", $data["author_id"] );
		
		for( $i=0; $i<count($authors) && $i<2; $i++ ) {
			if( $i > 0 ) {
				$str .= "、　";
			}
			$str .= "<a href=\"/doujin/circle/".$circle_ids[$i]."/\">".$circles[$i]."</a> ／ <a href=\"/doujin/author/".$author_ids[$i]."/\">".$authors[$i]."</a>";
		}
		if( count($authors) > 2 ) {
			$str .= "　ほか";
		}
				
		$str .= "</td>";
		if( $data["group_id"] ) {
			$str .= "<td><a href=\"/doujin/group/".$data["group_id"]."/\">".$data["revision"]."</a></td>\n";
		} else {
			$str .= "<td>".$data["revision"]."　</td>\n";
		}
		if( $data["series_id"] ) {
			$str .= "<td><a href=\"/doujin/series/".$data["series_id"]."/\">".$data["volume"]."</a></td>\n";
		} else {
			$str .= "<td>".$data["volume"]."　</td>\n";
		}
		$str .= "<td>".$yesno[$data["adult"]]."</td></tr>\n";
		
		$lines++;
	}
	$str .= "</table>";
	
	print "<b>{$lines}</b>件を表示しています<br>";
	print "$str";
}

##################################### update metadata #########

function meta_create( $name, $id )
{
	global $dbh_md;
	
	$t = time();
	
	try {
		$dbh_md->exec("BEGIN DEFERRED;");
		$dbh_md->exec( "INSERT INTO {$name}s_metadata(id, created_date) VALUES($id, $t )" );
		$res = $dbh_md->query( "SELECT * FROM {$name}s_metadata ORDER BY id DESC" );
		if( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {
			$res->closeCursor();
			meta_update_date( $name, $id, $t );
			$dbh_md->exec("COMMIT;");
			return 1;
		}
	} catch (Exception $e) {
		$dbh_md->exec("ROLLBACK;");
		errmsg($e->getTraceAsString());
		exit();
	}
	$dbh_md->exec("ROLLBACK;");
	$res->closeCursor();
	return 0;
}

function meta_update_date( $name, $id, $t )
{
	global $dbh_md;
	
	try {
		$res = $dbh_md->exec( "UPDATE {$name}s_metadata SET modified_date = $t WHERE id = $id" );
		if( $res ) {
			return 1;
		}
	} catch (Exception $e) {
		errmsg($e->getTraceAsString());
		exit();
	}
	return 0;
}

function datestr( $t )
{
	date_default_timezone_set('Asia/Tokyo');
	return date("Y-m-d H:i:s", $t); 
}

#####################

function html_escape( $str )
{
	return htmlentities($str, ENT_QUOTES, "UTF-8");
}

function is_localaddr()
{
	if( preg_match("/^(192\.168|10\.|172\.(1[6-9]|2[0-9]|3[01])\.)/", $_SERVER["REMOTE_ADDR"]) ) {
		return true;
	}
	if( $_SERVER["REMOTE_ADDR"] == '127.0.0.1' ) {
		return true;
	}
	return false;
}

?>