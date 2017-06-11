<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>regist</title>
</head>
<body>


<?php

include 'func.php';


$dbname = "./db/doujin.db";
$dbname_md = "./db/metadata.db";

###### doujin db
$dbh;
try {
	$dbh = new PDO("sqlite:$dbname");
}
catch( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

###### metadata db
$dbh_md;
try {
	$dbh_md = new PDO("sqlite:$dbname_md");
}
catch( PDOException $e ) {
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}
$dbh_md->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cmd = $_POST["cmd"];

if( $cmd == "addgroup_confirm" || $cmd == "addgroup" || $cmd == "addseries_confirm" || $cmd == "addseries" ) {
	$n_items = 0;
	$items = array();
	for( $i=0; $i<99; $i++ ) {
		if( ! empty($_POST["item{$i}_id"]) ) {
			$n_items++;
			$items[$n_items-1] = $_POST["item{$n_items}_id"];
		}
	}
	
	for( $i=0; $i < $n_items; $i++ ) {
		if( ! check_itemid( $items[$i] ) ) {
			errmsg("作品ID ".$items[$i]." は存在しません");
			exit();
		}
	}
	
	if( $cmd == "addseries_confirm" || $cmd == "addseries" ) {
		$series_name = $_POST["series_name"];
		if( empty($series_name) ) {
			errmsg("シリーズ名を入力してください");
			exit();
		}
	}
	
	if( $cmd == "addgroup_confirm" ) {
		print "<div align=\"center\">以下のアイテムをグループ化します<br><br>";
	} else if( $cmd == "addgroup" ) {
		print "<div align=\"center\">以下のアイテムをグループ化しました<br><br>";
	} else if( $cmd == "addseries_confirm" ) {
		print "<div align=\"center\">以下のアイテムをシリーズ化します<br><br>シリーズ名:$series_name<br><br>";
	} else if( $cmd == "addseries" ) {
		print "<div align=\"center\">以下のアイテムをシリーズ化しました<br><br>シリーズ名:$series_name<br><br>";
	}
	
	if( $cmd == "addgroup" || $cmd == "addseries" ) {
		if( $cmd == "addgroup" ) {
			$gid = add_group();
		} else {
			$gid = add_series( $series_name );
		}
		
		try {
			$dbh->exec("BEGIN DEFERRED;");
			for( $i=0; $i < $n_items; $i++ ) {
				if( $cmd == "addgroup" ) {
					$res = $dbh->query( "UPDATE items SET group_id = $gid WHERE id = ".$items[$i].";" );
				} else {
					$res = $dbh->query( "UPDATE items SET series_id = $gid WHERE id = ".$items[$i].";" );
				}
				if( ! $res ) {
					throw new Exception('アイテム情報のアップデートに失敗しました');
				}
			}
		} catch (Exception $e) {
			$dbh->exec("ROLLBACK;");
			errmsg($e->getTraceAsString());
			exit();
		}
		$dbh->exec("COMMIT;");
	}
	
	if( $cmd == "addgroup_confirm" || $cmd == "addseries_confirm" ) {
		print "<form action=\"./regist.php\" method=\"POST\">\n";
	}
	for( $i=0; $i < $n_items; $i++ ) {
		$in = $i + 1;
		print "<a href=\"/doujin/item/". $items[$i] ."/\">". get_itemname($items[$i]) . "</a><br>";
		print html_hidden_value( "item".$in."_id", $items[$i] )."\n";
	}
	if( $cmd == "addgroup_confirm" ) {
		print "<br><button type=\"submit\" name=\"cmd\" value=\"addgroup\">登録</button></form></div>\n";
	}
	if( $cmd == "addseries_confirm" ) {
		print "<br><button type=\"submit\" name=\"cmd\" value=\"addseries\">登録</button>".
					html_hidden_value( "series_name", $series_name )."</form></div>\n";
	}
	
	exit();
}

$metatitle = $_POST["metatitle"];
$title = $_POST["title"];
$subtitle = $_POST["subtitle"];

$type = $_POST["type"];
$size = $_POST["size"];
$page = $_POST["page"];

$date = $_POST["date"];
$event = $_POST["event"];
if( $cmd == "regist" ) {
	$event_id = $_POST["event_id"];
}

$new_date = $_POST["new_date"];
$revision = $_POST["revision"];
$new_event = $_POST["new_event"];
if( $cmd == "regist" ) {
	$new_event_id = $_POST["new_event_id"];
}

$volume = $_POST["volume"];

$form = $_POST["form"];
$lang = $_POST["lang"];

$side = $_POST["side"];
$adult = $_POST["adult"];
$copibon = $_POST["copibon"];
$collection = $_POST["collection"];
$collabo = $_POST["collabo"];
$group_id = $_POST["group_id"];
$series_id = $_POST["series_id"];

############## circles, authors
$n_authors = 0;
$circles = array();
$authors = array();
$circle_ids = array();
$author_ids = array();
for( $i=0; $i<99; $i++ ) {
	if( isset($_POST["circle$i"]) || isset($_POST["author$i"]) ) {
		if( $_POST["circle$i"] != "" || $_POST["author$i"] != "" ) {
			$circles[$n_authors] = $_POST["circle$i"];
			$authors[$n_authors] = $_POST["author$i"];
			if( $cmd == "regist" ) {
				$circle_ids[$n_authors] = $_POST["circle{$i}_id"];
				$author_ids[$n_authors] = $_POST["author{$i}_id"];
			}
			$n_authors++;
		}
	}
}

############## originals
$n_originals = 0;
$originals = array();
$original_ids = array();
for( $i=0; $i<99; $i++ ) {
	if( isset($_POST["original$i"]) ) {
		if( $_POST["original$i"] != "" ) {
			$originals[$n_originals] = $_POST["original$i"];
			if( $cmd == "regist" ) {
				$original_ids[$i] = $_POST["original{$i}_id"];
			}
			$n_originals++;
		}
	}
}

############## originals_sub
$n_originals_sub = 0;
$originals_sub = array();
$original_sub_ids = array();
for( $i=0; $i<99; $i++ ) {
	if( ! empty($_POST["original_sub$i"]) ) {
			$originals_sub[$n_originals_sub] = $_POST["original_sub$i"];
			if( $cmd == "regist" ) {
				$original_sub_ids[$i] = $_POST["original_sub{$i}_id"];
			}
			$n_originals_sub++;
	}
}

############## guests
$n_gauthors = 0;
$gcircles = array();
$gauthors = array();
$gcircle_ids = array();
$gauthor_ids = array();
for( $i=0; $i<99; $i++ ) {
	if( isset($_POST["guest_circle$i"]) || isset($_POST["guest_author$i"]) ) {
		if( $_POST["guest_circle$i"] != "" || $_POST["guest_author$i"] != "" ) {
			$gcircles[$n_gauthors] = $_POST["guest_circle$i"];
			$gauthors[$n_gauthors] = $_POST["guest_author$i"];
			if( $cmd == "regist" ) {
				$gcircle_ids[$n_gauthors] = $_POST["guest_circle{$i}_id"];
				$gauthor_ids[$n_gauthors] = $_POST["guest_author{$i}_id"];
			}
			$n_gauthors++;
		}
	}
}

############## tags
$n_tags = 0;
$tags = array();
for( $i=0; $i<99; $i++ ) {
	if( isset($_POST["tag$i"]) ) {
		if( $_POST["tag$i"] != "" ) {
			$tags[$n_tags] = $_POST["tag$i"];
			$n_tags++;
		}
	}
}

############## refs
$n_refs = 0;
$refs = array();
for( $i=0; $i<99; $i++ ) {
	if( isset($_POST["ref{$i}_id"]) ) {
		if( $_POST["ref{$i}_id"] != "" ) {
			$refs[$n_refs] = $_POST["ref{$i}_id"];
			$n_refs++;
		}
	}
}

####################### html

$ok = 1;

###### check #######

if( ! "$title" ) {
	$ok = 0; errmsg("誌名を入力してください");
}
if( ! $types[$type] ) {
	$ok = 0; errmsg("種別が不正です");
}
if( ! $sizes[$size] ) {
	$ok = 0; errmsg("サイズが不正です");
}
if( ! isnum($page) ) {
	$ok = 0; errmsg("ページ数が不正です（1以上）");
}
if( ! isdate($date) ) {
	$ok = 0; errmsg("発行日が不正です");
}

if( !empty($new_date) && ! isdate($new_date) ) {
	$ok = 0; errmsg("再販日が不正です");
}

if( ! $forms[$form] ) {
	$ok = 0; errmsg("形態が不正です");
}
if( ! $langs[$lang] ) {
	$ok = 0; errmsg("言語が不正です");
}
if( ! $sides[$side] ) {
	$ok = 0; errmsg("綴じ情報が不正です");
}
if( ! $yesno[$adult] ) {
	$ok = 0; errmsg("成年向けフラグが不正です");
}
if( ! $yesnoinfo[$copibon] ) {
	$ok = 0; errmsg("コピー本フラグが不正です");
}
if( ! $yesnoinfo[$collection] ) {
	$ok = 0; errmsg("総集編フラグが不正です");
}
if( ! $yesnoinfo[$collabo] ) {
	$ok = 0; errmsg("合同誌フラグが不正です");
}

if( !empty( $group_id ) && !isnum($group_id) ) {
	$ok = 0; errmsg("グループIDが不正です");
}
if( !empty( $series_id ) && !isnum($series_id) ) {
	$ok = 0; errmsg("シリーズIDが不正です");
}

if( $_POST["cmd"] == "regist" ) {
	if( $event_id == "new" ) {
		if( ! $event ) {
			$ok = 0; errmsg("イベント名を入力してください");
		}
	} else if( $event_id ) {
		if( ! check_eventid( $event_id ) ) {
			$ok = 0; errmsg("イベントIDが不正です");
		}
	}
	
	if( $new_event_id == "new" ) {
		if( ! $new_event ) {
			$ok = 0; errmsg("再販イベント名を入力してください");
		}
	} else if( $new_event_id ) {
		if( ! check_eventid( $new_event_id ) ) {
			$ok = 0; errmsg("再販イベントのIDが不正です");
		}
	}
	
	for( $i=0; $i<$n_authors; $i++ ) {
		$inum = $i+1;
		if( $circle_ids[$i] != "" && $circle_ids[$i] != "new" && !check_circleid($circle_ids[$i]) ) {
			$ok = 0; errmsg("サークルID($circle_ids[$i])は存在しません");
		}
		if( $author_ids[$i] != "" && $author_ids[$i] != "new" && !check_authorid($author_ids[$i]) ) {
			$ok = 0; errmsg("著者ID($author_ids[$i])は存在しません");
		}
		if( $circle_ids[$i] == "new" && $circles[$i] == "" ) {
			$ok = 0; errmsg("サークル名({$inum}番目)を入力してください");
		}
		if( $author_ids[$i] == "new" && $authors[$i] == "" ) {
			$ok = 0; errmsg("著者名({$inum}番目)を入力してください");
		}
		if( $circle_ids[$i] == "" && $author_ids[$i] == "" ) {
			$ok = 0; errmsg("サークル名か著者名({$inum}番目)を入力してください");
		}
	}
	
	$madokabon = 0;
	for( $i=0; $i<$n_originals; $i++ ) {
		$inum = $i+1;
		if( $original_ids[$i] != "new" && ! check_originalid( $original_ids[$i] ) ) {
			$ok = 0; errmsg("原作ID($original_ids[$i])は存在しません");
		}
		if( $original_ids[$i] == "new" && $originals[$i] == "" ) {
			$ok = 0; errmsg("原作名({$inum}番目)を入力してください");
		}
		
		if( $original_ids[$i] == "new" && $originals[$i] == "魔法少女まどか☆マギカ" ) {
			$madokabon = 1;
		} else if( $original_ids[$i] == 1 ) {
			$madokabon = 1;
		}
	}
	for( $i=0; $i<$n_originals_sub; $i++ ) {
		$inum = $i+1;
		if( $original_sub_ids[$i] != "new" && ! check_originalid( $original_sub_ids[$i] ) ) {
			$ok = 0; errmsg("原作ID（サブ）($original_sub_ids[$i])は存在しません");
		}
		if( $original_sub_ids[$i] == "new" && $originals_sub[$i] == "" ) {
			$ok = 0; errmsg("原作名(サブ)({$inum}番目)を入力してください");
		}
		
		if( $original_sub_ids[$i] == "new" && $originals_sub[$i] == "魔法少女まどか☆マギカ" ) {
			$madokabon = 1;
		} else if( $original_sub_ids[$i] == 1 ) {
			$madokabon = 1;
		}
	}
	if( !$madokabon ) {
		$ok = 0; errmsg("'魔法少女まどか☆マギカ' が原作に含まれるアイテムのみ追加できます");
	}
	
	for( $i=0; $i<$n_gauthors; $i++ ) {
		$inum = $i+1;
		if( $gcircle_ids[$i] != "" && $gcircle_ids[$i] != "new" && !check_circleid($gcircle_ids[$i]) ) {
			$ok = 0; errmsg("ゲストサークルID($gcircle_ids[$i])は存在しません");
		}
		if( $gauthor_ids[$i] != "" && $gauthor_ids[$i] != "new" && !check_authorid($gauthor_ids[$i]) ) {
			$ok = 0; errmsg("ゲスト著者ID($gauthor_ids[$i])は存在しません");
		}
		if( $gcircle_ids[$i] == "new" && $gcircles[$i] == "" ) {
			$ok = 0; errmsg("ゲストサークル名({$inum}番目)を入力してください");
		}
		if( $gauthor_ids[$i] == "new" && $gauthors[$i] == "" ) {
			$ok = 0; errmsg("ゲスト名({$inum}番目)を入力してください");
		}
		if( $gcircle_ids[$i] == "" && $gauthor_ids[$i] == "" ) {
			$ok = 0; errmsg("ゲストサークル名かゲスト名({$inum}番目)を入力してください");
		}
	}
	
	for( $i=0; $i<$n_refs; $i++ ) {
		if( ! check_itemid( $refs[$i] ) ) {
			errmsg("作品ID ".$refs[$i]." は存在しません");
			exit();
		}
	}
	
	if( !empty($group_id) && !check_exist_group($group_id) ) {
		errmsg("グループID ".$group_id." は存在しません");
		exit();
	}
	if( !empty($series_id) && !check_exist_series($series_id) ) {
		errmsg("シリーズID ".$series_id." は存在しません");
		exit();
	}
}

if($ok) {
	if( $cmd == "regist" ) {
		print "以下の内容で登録しました";
	} else {
		print "以下の内容で登録します";
	}
}
print "<br><br>";

####################

### regist

if( $_POST["cmd"] == "regist" && $ok ) {

	if( $event_id == "new" ) {
		$event_id = "".add_event( $event );
		if( $event_id <= 0 ) {

			errmsg("イベントの追加に失敗しました");
			exit();
		}
	}
	if( $new_event_id == "new" ) {
		$new_event_id ="".add_event( $new_event );
		if( $new_event_id <= 0 ) {
			errmsg("再販イベントの追加に失敗しました");
			exit();
		}
	}
	
	$already_circle = array();
	for( $i=0; $i<$n_authors; $i++ ) {
		if( $circle_ids[$i] == "new" ) {
			if( empty($already_circle[$circles[$i]]) ) {
				$circle_ids[$i] = add_circle( $circles[$i] );
				$already_circle[$circles[$i]] = $circle_ids[$i];
			} else {
				$circle_ids[$i] = $already_circle[$circles[$i]];
			}
			if( $circle_ids[$i] <= 0 ) {
				errmsg("サークルの追加に失敗しました");
				exit();
			}
		}
		if( $author_ids[$i] == "new" ) {
			$author_ids[$i] = add_author( $authors[$i] );
			if( $author_ids[$i] <= 0 ) {
				errmsg("著者の追加に失敗しました");
				exit();
			}
		}
	}
	
	for( $i=0; $i<$n_originals; $i++ ) {
		if( $original_ids[$i] == "new" ) {
			$original_ids[$i] = add_original( $originals[$i] );
			if( $original_ids[$i] <= 0 ) {
				errmsg("原作の追加に失敗しました");
				exit();
			}
		}
	}
	
	for( $i=0; $i<$n_originals_sub; $i++ ) {
		if( $original_sub_ids[$i] == "new" ) {
			$original_sub_ids[$i] = add_original( $originals_sub[$i] );
			if( $original_sub_ids[$i] <= 0 ) {
				errmsg("原作の追加に失敗しました");
				exit();
			}
		}
	}
	
	for( $i=0; $i<$n_gauthors; $i++ ) {
		if( $gcircle_ids[$i] == "new" ) {
			if( empty($already_circle[$gcircles[$i]]) ) {
				$gcircle_ids[$i] = add_circle( $gcircles[$i] );
				$already_circle[$gcircles[$i]] = $gcircle_ids[$i];
			} else {
				$gcircle_ids[$i] = $already_circle[$gcircles[$i]];
			}
			if( $gcircle_ids[$i] <= 0 ) {
				errmsg("ゲストサークルの追加に失敗しました");
				exit();
			}
		}
		if( $gauthor_ids[$i] == "new" ) {
			$gauthor_ids[$i] = add_author( $gauthors[$i] );
			if( $gauthor_ids[$i] <= 0 ) {
				errmsg("ゲストの追加に失敗しました");
				exit();
			}
		}
	}
	
	############### SQL query
	
	$querytext = "INSERT INTO items(
		metatitle,
		title,
		subtitle,
		type,
		size,
		page,
		date,
		event_id,
		new_date,
		new_event_id,
		revision,
		group_id,
		volume,
		series_id,
		form,
		lang,
		adult,
		side,
		copibon,
		collection,
		collabo,
		circle_id,
		circle_name,
		author_id,
		author_name,
		original_id,
		original_sub_id,
		guest_circle_id,
		guest_circle_name,
		guest_id,
		guest_name,
		tag,
		ref_id
		) VALUES( ";
	$querytext .= sqlstr( $metatitle ).",";
	$querytext .= sqlstr( $title ).",";
	$querytext .= sqlstr( $subtitle ).",";
	$querytext .= sqlstr( $type ).",";
	$querytext .= sqlstr( $size ).",";
	$querytext .= sqlnum( $page ).",";
	$querytext .= sqlnum( $date ).",";
	$querytext .= sqlnum( $event_id ).",";
	$querytext .= sqlnum( $new_date ).",";
	$querytext .= sqlnum( $new_event_id ).",";
	$querytext .= sqlstr( $revision ).",";
	$querytext .= sqlnum( $group_id ).",";
	$querytext .= sqlstr( $volume ).",";
	$querytext .= sqlnum( $series_id ).",";
	$querytext .= sqlstr( $form ).",";
	$querytext .= sqlstr( $lang ).",";
	$querytext .= sqlstr( $adult ).",";
	$querytext .= sqlstr( $side ).",";
	$querytext .= sqlstr( $copibon ).",";
	$querytext .= sqlstr( $collection ).",";
	$querytext .= sqlstr( $collabo ).",";

	$querytext .= "'";
	for( $i=0; $i<$n_authors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $circle_ids[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_authors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $circles[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_authors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $author_ids[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_authors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $authors[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_originals; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $original_ids[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_originals_sub; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $original_sub_ids[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_gauthors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $gcircle_ids[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_gauthors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $gcircles[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_gauthors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $gauthor_ids[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_gauthors; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $gauthors[$i] );
	}
	$querytext .= "',";
	
	$querytext .= "'";
	for( $i=0; $i<$n_tags; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $tags[$i] );
	}
	$querytext .= "',";

	$querytext .= "'";
	for( $i=0; $i<$n_refs; $i++ ) {
		if( $i > 0 ) { $querytext .= "/"; }
		$querytext .= sql_escape_multi( $refs[$i] );
	}
	$querytext .= "'";
	$querytext .=")";
	
	#print $querytext."<br>";
	
	try {
		$dbh->exec("BEGIN DEFERRED;");
		$dbh->exec( $querytext );
		$res = $dbh->query( "SELECT * FROM items ORDER BY id DESC" );
		$row = $res->fetch( PDO::FETCH_ASSOC );
		meta_create( "item", $row["id"] );
	}
	catch( PDOException $e ) {
		$dbh->exec("ROLLBACK;");
		echo 'Connection failed: ' . $e->getMessage();
		exit();
	}
	$dbh->exec("COMMIT;");
	
	###############
}
###

print "<form method=\"POST\" action=\"regist.php\">";
print "誌名: $metatitle <b>$title</b> <i>$subtitle</i><br>".
			html_hidden_value("metatitle",$metatitle).
			html_hidden_value("title",$title).
			html_hidden_value("subtitle",$subtitle)."\n";
print "種別: <b>$types[$type]</b><br>".
			html_hidden_value("type",$type)."\n";
print "サイズ: <b>$sizes[$size]</b><br>".
			html_hidden_value("size",$size)."\n";
print "ページ数: <b>$page</b><br>".
			html_hidden_value("page",$page)."\n";

print "発行日: <b>$date</b><br>".
			html_hidden_value("date",$date)."\n";

if( ! $event ) {
	$eid = "";
} else {
	$eid = check_event($event);
	if( $eid == -1 ) {
		$eid = "new";
	}
}
print "イベント: <b>$event</b>".html_hidden_value("event",$event).
			" ( ID:<input type=\"text\" name=\"event_id\" value=\"$eid\" size=\"8\" > )<br>\n";

print "再販日: <b>$new_date</b><br>".
			html_hidden_value("new_date",$new_date)."\n";
			
if( ! $new_event ) {
	$eid = "";
} else {
	$eid = check_event($new_event);
	if( $eid == -1 ) {
		$eid = "new";
	}
}
print "再販イベント: <b>$new_event</b>".html_hidden_value("new_event",$new_event).
			" ( ID:<input type=\"text\" name=\"new_event_id\" value=\"$eid\" size=\"8\" > )<br>\n";

print "版数: <b>$revision</b><br>".
			html_hidden_value("revision",$revision)."\n";
print "グループID: <b>$group_id</b>".html_hidden_value("group_id",$group_id)."<br>\n";
print "巻数: <b>$volume</b><br>".
			html_hidden_value("volume",$volume)."\n";
print "シリーズID: <b>$series_id</b>".html_hidden_value("series_id",$series_id)."<br>\n";

print "形態: <b>$forms[$form]</b><br>".
			html_hidden_value("form",$form)."\n";
print "言語: <b>$langs[$lang]</b><br>".
			html_hidden_value("lang",$lang)."\n";
print "綴じ: <b>$sides[$side]</b><br>".
			html_hidden_value("side",$side)."\n";
print "成年向け: <b>$yesno[$adult]</b><br>".
			html_hidden_value("adult",$adult)."\n";
print "コピー本: <b>$yesnoinfo[$copibon]</b><br>".
			html_hidden_value("copibon",$copibon)."\n";
print "総集編: <b>$yesnoinfo[$collection]</b><br>".
			html_hidden_value("collection",$collection)."\n";
print "合同誌: <b>$yesnoinfo[$collabo]</b><br>".
			html_hidden_value("collabo",$collabo)."\n";

print "サークル／著者:<br>\n";
for( $i=0; $i<$n_authors; $i++ ) {
	$cid = check_circle($circles[$i]);
	$aid = check_author($authors[$i]);
	if( $cid == -1 ) {
		$cid = "new";
	}
	if( $aid == -1 ) {
		$aid = "new";
	}
	
	if( empty($circles[$i]) ) {
		$cid = "";
	}
	if( empty($authors[$i]) ) {
		$aid = "";
	}
	
	print "&nbsp;<b>$circles[$i]</b>".
			html_hidden_value("circle{$i}",$circles[$i]);
	print "\n ( ID:<input type=\"text\" name=\"circle{$i}_id\" value=\"$cid\" size=\"8\" > )\n";
	print " / <b>$authors[$i]</b>".
			html_hidden_value("author{$i}",$authors[$i])."
			 ( ID:<input type=\"text\" name=\"author{$i}_id\" value=\"$aid\" size=\"8\" > )<br>\n";
}

print "原作:<br>\n";
for( $i=0; $i<$n_originals; $i++ ) {
	$oid = check_original( $originals[$i] );
	if( $oid == -1 ) {
		$oid = "new";
	}
	print "&nbsp;<b>$originals[$i]</b>".
			html_hidden_value("original{$i}", $originals[$i]).
			" ( ID:<input type=\"text\" name=\"original{$i}_id\" value=\"$oid\"  size=\"8\" > )<br>\n";
}

print "原作(サブ):<br>\n";
for( $i=0; $i<$n_originals_sub; $i++ ) {
	$oid = check_original( $originals_sub[$i] );
	if( $oid == -1 ) {
		$oid = "new";
	}
	print "&nbsp;<b>$originals_sub[$i]</b>".
			html_hidden_value("original_sub{$i}", $originals_sub[$i]).
			" ( ID:<input type=\"text\" name=\"original_sub{$i}_id\" value=\"$oid\"  size=\"8\" > )<br>\n";
}

print "ゲストサークル／著者:<br>\n";
for( $i=0; $i<$n_gauthors; $i++ ) {
	$cid = check_circle($gcircles[$i]);
	$aid = check_author($gauthors[$i]);
	if( $cid == -1 ) {
		$cid = "new";
	}
	if( $aid == -1 ) {
		$aid = "new";
	}
	
	if( empty($gcircles[$i]) ) {
		$cid = "";
	}
	if( empty($gauthors[$i]) ) {
		$aid = "";
	}
	
	print "&nbsp;<b>$gcircles[$i]</b>".
			html_hidden_value("guest_circle{$i}",$gcircles[$i]).
			"\n ( ID:<input type=\"text\" name=\"guest_circle{$i}_id\" value=\"$cid\" size=\"8\" > )\n";
	print " / <b>$gauthors[$i]</b>".
			html_hidden_value("guest_author{$i}",$gauthors[$i])."
			 ( ID:<input type=\"text\" name=\"guest_author{$i}_id\" value=\"$aid\" size=\"8\" > )<br>\n";
}

print "タグ:<br>\n";
for( $i=0; $i<$n_tags; $i++ ) {
	print "&nbsp;<b>$tags[$i]</b>".html_hidden_value("tag{$i}",$tags[$i])."<br>\n";
}

print "参照:<br>\n";
for( $i=0; $i<$n_refs; $i++ ) {
	print "&nbsp;<b>$refs[$i]</b><br>".
			html_hidden_value("ref{$i}_id",$refs[$i])."\n";
}

if( $ok ) {
	if( $cmd == "confirm" ) {
		print "<br><button type=\"submit\" name=\"cmd\" value=\"regist\">以上の内容で登録する</button>\n";
	} else if( $cmd == "regist" ) {
		print "<br><a href=\"regist.html\">戻る</a>\n";
	}
} else {
	print "<br>";
	errmsg("入力内容に不備があります。前のページに戻って入力しなおしてください。");
}
print "</form>\n";

?>


</body>
</html>