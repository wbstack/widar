<?PHP

error_reporting(E_ERROR|E_CORE_ERROR|E_COMPILE_ERROR); # |E_ALL
ini_set('display_errors', 'On');

$miser_mode = false ;

$out = array ( 'error' => 'OK' , 'data' => array() ) ;
$botmode = isset ( $_REQUEST['botmode'] ) ;
if ( $botmode ) {
	header ( 'application/json' ) ; // text/plain
} else {
	error_reporting(E_ERROR|E_CORE_ERROR|E_ALL|E_COMPILE_ERROR);
	ini_set('display_errors', 'On');
}

require_once ( 'php/oauth.php' ) ;
require_once ( 'php/common.php' ) ;

$oa = new MW_OAuth ( 'widar' , 'wikidata' , 'wikidata' ) ;

switch ( isset( $_GET['action'] ) ? $_GET['action'] : '' ) {
	case 'authorize':
		$oa->doAuthorizationRedirect();
		exit ( 0 ) ;
		return;
	
	case 'remove_claim' :
		removeClaim() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_claims':
		setClaims() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'merge_items':
		mergeItems() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'create_redirect':
		createRedirect() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_label':
		setLabel() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_desc':
		setDesc() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_alias':
		set_Alias() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_string':
		setString() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_monolang':
		setMonolang() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'get_rights':
		getRights() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'logout':
		logout() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;
		
		
	case 'set_sitelink':
		setSitelink() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_location':
		setLocationClaim() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_date':
		setDateClaim() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'set_quantity':
		setQuantityClaim() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'create_blank_item':
		createBlankItem() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;
	
	case 'create_item_from_page':
		createItemFromPage() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;
	
	case 'add_source':
		setSource() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'delete':
		deletePage() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;

	case 'add_row': // Adds a text row to a non-item page
		addRow() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;
		
	case 'append' :
		appendText() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;
	
	case 'generic' :
		genericAction() ;
		if ( $botmode ) bot_out() ;
		else print get_common_footer() ;
		exit ( 0 ) ;
		return ;
}


function ensureAuth () {
	global $oa , $botmode , $out ;
	$ch = null;

	// First fetch the username
	$res = $oa->doApiQuery( array(
		'format' => 'json',
		'action' => 'query',
		'meta' => 'userinfo',
	), $ch );

	if ( isset( $res->error->code ) && $res->error->code === 'mwoauth-invalid-authorization' ) {
		// We're not authorized!
		$msg = 'You haven\'t authorized this application yet! Go <a target="_blank" href="' . htmlspecialchars( $_SERVER['SCRIPT_NAME'] ) . '?action=authorize">here</a> to do that, then reload this page.' ;
		if ( $botmode ) $out['error'] = $msg ;
		else echo $msg . '<hr>';
		return false ;
	}

	if ( !isset( $res->query->userinfo ) ) {
		$msg = 'Bad API response[1]: <pre>' . htmlspecialchars( var_export( $res, 1 ) ) . '</pre>' ;
		if ( $botmode ) {
			$out['error'] = $msg ;
			return false ;
		} else {
			header( "HTTP/1.1 500 Internal Server Error" );
			echo $msg;
			exit(0);
		}
	}
	if ( isset( $res->query->userinfo->anon ) ) {
		$msg = 'Not logged in. (How did that happen?)' ;
		if ( $botmode ) {
			$out['error'] = $msg ;
			return false ;
		} else {
			header( "HTTP/1.1 500 Internal Server Error" );
			echo $msg;
			exit(0);
		}
	}
	
	return true ;
}

function setLabel () {
	global $oa , $botmode , $out ;
	
	// https://tools.wmflabs.org/widar/index.php?action=set_label&q=Q1980313&lang=en&label=New+Bach+monument+in+Leipzig&botmode=1

	if ( !ensureAuth() ) return ;
	show_header() ;

	$q = get_request ( 'q' , '' ) ;
	$lang = get_request ( 'lang' , '' ) ;
	$label = get_request ( 'label' , '' ) ;
	
	if ( $q == '' or $lang == '' ) { //or $label == '' ) {
		$msg = "Needs q, lang, label" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}

	if ( !$oa->setLabel ( $q , $label , $lang ) ) {
		$msg = "Problem setting label" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
	} else {
//		if ( !$botmode ) $out['q'] = $q ;
//		else print "<p>$site page '$page' now has Wikidata item ID <a href='//www.wikidata.org/wiki/$q'>$q</a>.</p>" ;
	}
}


function setDesc () {
	global $oa , $botmode , $out ;
	
	// https://tools.wmflabs.org/widar/index.php?action=set_label&q=Q1980313&lang=en&label=New+Bach+monument+in+Leipzig&botmode=1

	if ( !ensureAuth() ) return ;
	show_header() ;

	$q = get_request ( 'q' , '' ) ;
	$lang = get_request ( 'lang' , '' ) ;
	$label = get_request ( 'label' , '' ) ;
	
	if ( $q == '' or $lang == '' ) { //or $label == '' ) {
		$msg = "Needs q, lang, label" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}

	if ( !$oa->setDesc ( $q , $label , $lang ) ) {
		$msg = "Problem setting description" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
	} else {
//		if ( !$botmode ) $out['q'] = $q ;
//		else print "<p>$site page '$page' now has Wikidata item ID <a href='//www.wikidata.org/wiki/$q'>$q</a>.</p>" ;
	}
}


function set_Alias () {
	global $oa , $botmode , $out ;
	
	// https://tools.wmflabs.org/widar/index.php?action=set_label&q=Q1980313&lang=en&label=New+Bach+monument+in+Leipzig&botmode=1

	if ( !ensureAuth() ) return ;
	show_header() ;

	$q = get_request ( 'q' , '' ) ;
	$lang = get_request ( 'lang' , '' ) ;
	$label = get_request ( 'label' , '' ) ;
	$mode = get_request ( 'mode' , 'add' ) ;
	
	if ( $q == '' or $lang == '' or $label == '' ) {
		$msg = "Needs q, lang, label [, mode=add/set/remove]" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}

	if ( !$oa->set_Alias ( $q , $label , $lang , $mode ) ) {
		$msg = "Problem setting alias" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
	} else {
//		if ( !$botmode ) $out['q'] = $q ;
//		else print "<p>$site page '$page' now has Wikidata item ID <a href='//www.wikidata.org/wiki/$q'>$q</a>.</p>" ;
	}
}


function createBlankItem () {
	global $oa , $botmode , $out ;

	if ( !ensureAuth() ) return ;
	show_header() ;

	if ( !$oa->createItem() ) {
		$msg = "Problem creating item" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
	} else {
		$q = $oa->last_res->entity->id ;
		if ( $botmode ) $out['q'] = $q ;
		else print "<p>$site page '$page' now has Wikidata item ID <a href='//www.wikidata.org/wiki/$q'>$q</a>.</p>" ;
	}
}


function createItemFromPage() {
	global $oa , $botmode , $out ;

	if ( !ensureAuth() ) return ;
	show_header() ;

	$site = get_request ( 'site' , '' ) ;
	$page = get_request ( 'page' , '' ) ;
	
	if ( $site == '' or $page == '' ) {
		$msg = "Needs site and page" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$oa->createItemFromPage ( $site , $page ) ) {
		$msg = "Problem creating item" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		$out['res'] = $oa->last_res ;
	} else {
		$q = $oa->last_res->entity->id ;
		if ( $botmode ) $out['q'] = $q ;
		else print "<p>$site page '$page' now has Wikidata item ID <a href='//www.wikidata.org/wiki/$q'>$q</a>.</p>" ;
	}
}

function removeClaim () {
	global $oa , $botmode , $miser_mode ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$id = trim ( get_request ( "id" , '' ) ) ;
	$baserev = get_request ( 'baserev' , '' ) ;
	
	if ( $id == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing claim removal...</div>" ;
		print "<ol>" ;
		myflush();
	}

	if ( !$botmode ) {
		print "<li>Removing $id ... " ;
		myflush() ;
	}
	
	if ( $miser_mode ) {
		if ( !$botmode ) {
			print " [delaying edit 5 seconds - temporary measure to not overload Wikidata-Wikipedia sync] " ;
			myflush() ;
		}
		sleep ( 5 ) ;
	}

	if ( isset ( $_REQUEST['test'] ) ) {
		print "$id<br/>$baserev" ;
//		print "<pre>" ; print_r ( $claim ) ; print "</pre>" ;
	}

	if ( $oa->removeClaim ( $id , $baserev ) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;
}


function createRedirect () {
	global $oa , $botmode , $miser_mode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$q_from = trim ( get_request ( "from" , '' ) ) ;
	$q_to = trim ( get_request ( "to" , '' ) ) ;
	
	if ( $q_from == '' or $q_to == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing merging...</div>" ;
		print "<ol>" ;
		myflush();
	}
	
	if ( $miser_mode ) {
		if ( !$botmode ) {
			print " [delaying edit 5 seconds - temporary measure to not overload Wikidata-Wikipedia sync] " ;
			myflush() ;
		}
		sleep ( 5 ) ;
	}

	if ( isset ( $_REQUEST['test'] ) ) {
		print "$q_from<br/>$q_to" ;
//		print "<pre>" ; print_r ( $claim ) ; print "</pre>" ;
	}

	$out['ft'] = array ( $q_from,$q_to ) ;
	if ( $oa->createRedirect($q_from,$q_to) ) {
		if ( !$botmode ) print "done.\n" ;
		else $out['res'] = $oa->last_res ;
	} else {
		$msg = "failed!" ;
		if ( $botmode ) {
			$out['error'] = $msg ;
			$out['res'] = $oa->last_res ;
		} else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;
}

function mergeItems () {
	global $oa , $botmode , $miser_mode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$q_from = trim ( get_request ( "from" , '' ) ) ;
	$q_to = trim ( get_request ( "to" , '' ) ) ;
	
	if ( $q_from == '' or $q_to == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing merging...</div>" ;
		print "<ol>" ;
		myflush();
	}
	
	if ( $miser_mode ) {
		if ( !$botmode ) {
			print " [delaying edit 5 seconds - temporary measure to not overload Wikidata-Wikipedia sync] " ;
			myflush() ;
		}
		sleep ( 5 ) ;
	}

	if ( isset ( $_REQUEST['test'] ) ) {
		print "$q_from<br/>$q_to" ;
//		print "<pre>" ; print_r ( $claim ) ; print "</pre>" ;
	}

	if ( $oa->mergeItems($q_from,$q_to) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;
}


function setClaims() {
	global $oa , $botmode , $miser_mode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$ids = explode ( "," , get_request ( "ids" , '' ) ) ;
	$prop = get_request ( 'prop' , '' ) ;
	$target = get_request ( 'target' , '' ) ;
	$qualifier_claim = get_request ( 'claim' , '' ) ;
	
	if ( count($ids) == 0 or $prop == '' or $target == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Batch-processing " . count($ids) . " items...</div>" ;
		print "<ol>" ;
		myflush();
	}

	foreach ( $ids AS $id ) {
		$id = trim ( $id ) ;
		if ( $id == '' && $qualifier_claim == '' ) continue ;
		if ( !$botmode ) {
			print "<li><a href='//www.wikidata.org/wiki/$id'>$id</a> : $prop => $target ... " ;
			myflush() ;
		}
		
		if ( $miser_mode ) {
			if ( !$botmode ) {
				print " [delaying edit 5 seconds - temporary measure to not overload Wikidata-Wikipedia sync] " ;
				myflush() ;
			}
			sleep ( 5 ) ;
		}

		$claim = array (
			"prop" => $prop ,
//			"q" => $id ,
			"target" => $target ,
			"type" => "item"
		) ;
		
		if ( $qualifier_claim == '' ) $claim['q'] = $id ;
		else $claim['claim'] = $qualifier_claim ;
	
		if ( $oa->setClaim ( $claim ) ) {
			if ( !$botmode ) print "done.\n" ;
			else if ( isset($oa->last_res) ) $out['res'] = $oa->last_res ;
		} else {
			$msg = "failed!" ;
			if ( $botmode ) $out['error'] = $msg ;
			else print "$msg\n" ;
		}
		if ( !$botmode )  {
			print "</li>" ;
			myflush() ;
		}
		
	}
	if ( !$botmode ) print "</ol>" ;

}


function setString() {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$id = trim ( get_request ( "id" , '' ) ) ;
	$prop = get_request ( 'prop' , '' ) ;
	$text = get_request ( 'text' , '' ) ;
	$qualifier_claim = get_request ( 'claim' , '' ) ;
	
	if ( ( $id == '' and $qualifier_claim == '' ) or $prop == '' or $text == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing items $id...</div>" ;
		print "<ol>" ;
		myflush();
	}

	if ( !$botmode ) {
		print "<li><a href='//www.wikidata.org/wiki/$id'>$id</a> : $prop => $text ... " ;
		myflush() ;
	}

	$claim = array (
		"prop" => $prop ,
//		"q" => $id ,
		"text" => $text ,
		"type" => "string"
	) ;

	if ( $qualifier_claim == '' ) $claim['q'] = $id ;
	else $claim['claim'] = $qualifier_claim ;

	if ( $oa->setClaim ( $claim ) ) {
		if ( !$botmode ) print "done.\n" ;
		else if ( isset($oa->last_res) ) $out['res'] = $oa->last_res ;
	} else {
		$msg = "failed!" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;

}

function setMonolang() {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$id = trim ( get_request ( "id" , '' ) ) ;
	$prop = get_request ( 'prop' , '' ) ;
	$text = get_request ( 'text' , '' ) ;
	$language = get_request ( 'language' , '' ) ;
	$qualifier_claim = get_request ( 'claim' , '' ) ;
	
	if ( $id == '' or $prop == '' or $text == '' or $language == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing items $id...</div>" ;
		print "<ol>" ;
		myflush();
	}

	if ( !$botmode ) {
		print "<li><a href='//www.wikidata.org/wiki/$id'>$id</a> : $prop => $text ... " ;
		myflush() ;
	}

	$claim = array (
		"prop" => $prop ,
//		"q" => $id ,
		"text" => $text ,
		"language" => $language ,
		"type" => "monolingualtext"
	) ;

	if ( $qualifier_claim == '' ) $claim['q'] = $id ;
	else $claim['claim'] = $qualifier_claim ;
	
//	print_r ( $claim ) ;

	if ( $oa->setClaim ( $claim ) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		$out['error2'] = $oa->error ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;
}


function setSitelink() {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$q = get_request ( 'q' , '' ) ;
	$site = get_request ( 'site' , '' ) ;
	$title = get_request ( 'title' , '' ) ;
	
	if ( $q == '' or $site == '' or $title == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}


	if ( $oa->setSitelink ( $q , $site , $title ) ) {
		if ( !$botmode ) print "done.\n" ;
		else $out['res'] = $oa->last_res ;
	} else {
		$msg = "failed!" ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
}



function setSource () {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$statement = get_request ( 'statement' , '' ) ;
	$snaks = get_request ( 'snaks' , '' ) ; // JSON text!
	
	if ( $statement == '' or $snaks == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}


	if ( $oa->setSource ( $statement , $snaks ) ) {
		if ( !$botmode ) print "done.\n" ;
		else $out['res'] = $oa->last_res ;
	} else {
		$msg = "failed!" ;
		$out['res'] = $oa->last_res ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
}



function setLocationClaim() {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$id = trim ( get_request ( "id" , '' ) ) ;
	$prop = get_request ( 'prop' , '' ) ;
	$lat = get_request ( 'lat' , '' ) ;
	$lon = get_request ( 'lon' , '' ) ;
	$qualifier_claim = get_request ( 'claim' , '' ) ;
	
	if ( $id == '' or $prop == '' or $lat == '' or $lon == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing items $id...</div>" ;
		print "<ol>" ;
		myflush();
	}

	if ( !$botmode ) {
		print "<li><a href='//www.wikidata.org/wiki/$id'>$id</a> : $prop => $text ... " ;
		myflush() ;
	}

	$claim = array (
		"prop" => $prop ,
//		"q" => $id ,
		"lat" => $lat ,
		"lon" => $lon ,
		"type" => "location"
	) ;

	if ( $qualifier_claim == '' ) $claim['q'] = $id ;
	else $claim['claim'] = $qualifier_claim ;
	
//	print_r ( $claim ) ;

	if ( $oa->setClaim ( $claim ) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		$out['error2'] = $oa->error ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;

}

function genericAction () {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;
	
	$j = json_decode ( get_request ( 'json' , '' ) ) ;

	if ( $oa->genericAction ( $j ) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		$out['error2'] = $oa->error ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
}

function setQuantityClaim() {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$id = trim ( get_request ( "id" , '' ) ) ;
	$prop = get_request ( 'prop' , '' ) ;
	$amount = get_request ( 'amount' , '' ) ;
	$upper = get_request ( 'upper' , '' ) ;
	$lower = get_request ( 'lower' , '' ) ;
	$unit = get_request ( 'unit' , 1 ) * 1 ;
	$qualifier_claim = get_request ( 'claim' , '' ) ;
	
	if ( $id == '' or $prop == '' or $amount == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing items $id...</div>" ;
		print "<ol>" ;
		myflush();
	}

	if ( !$botmode ) {
		print "<li><a href='//www.wikidata.org/wiki/$id'>$id</a> : $prop => $amount ... " ;
		myflush() ;
	}
	
	if ( $upper == '' and $lower == '' ) {
		$upper = $amount ;
		$lower = $amount ;
	}

	$claim = array (
		"prop" => $prop ,
		"amount" => $amount*1 ,
		"upper" => $upper*1 ,
		"lower" => $lower*1 ,
		"unit" => $unit ,
		"type" => "quantity"
	) ;

	if ( $qualifier_claim == '' ) $claim['q'] = $id ;
	else $claim['claim'] = $qualifier_claim ;
	
//	print_r ( $claim ) ;

	if ( $oa->setClaim ( $claim ) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		$out['error2'] = $oa->error ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;
}

function setDateClaim() {
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$id = trim ( get_request ( "id" , '' ) ) ;
	$prop = get_request ( 'prop' , '' ) ;
	$date = get_request ( 'date' , '' ) ;
	$prec = get_request ( 'prec' , '' ) ;
	$qualifier_claim = get_request ( 'claim' , '' ) ;
	
	if ( $id == '' or $prop == '' or $date == '' or $prec == '' ) {
		$msg = "Parameters incomplete." ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "<pre>$msg</pre>" ;
		return ;
	}
	
	if ( !$botmode ) {
		print "<div>Processing items $id...</div>" ;
		print "<ol>" ;
		myflush();
	}

	if ( !$botmode ) {
		print "<li><a href='//www.wikidata.org/wiki/$id'>$id</a> : $prop => $text ... " ;
		myflush() ;
	}

	$claim = array (
		"prop" => $prop ,
//		"q" => $id ,
		"date" => $date ,
		"prec" => $prec ,
		"type" => "date"
	) ;

	if ( $qualifier_claim == '' ) $claim['q'] = $id ;
	else $claim['claim'] = $qualifier_claim ;
	
//	print_r ( $claim ) ;

	if ( $oa->setClaim ( $claim ) ) {
		if ( !$botmode ) print "done.\n" ;
	} else {
		$msg = "failed!" ;
		$out['error2'] = $oa->error ;
		if ( $botmode ) $out['error'] = $msg ;
		else print "$msg\n" ;
	}
	if ( !$botmode )  {
		print "</li>" ;
		myflush() ;
	}

	if ( !$botmode ) print "</ol>" ;

}

function addRow () { // ASSUMING BOTMODE
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$page = trim ( get_request ( "page" , '' ) ) ;
	$row = trim ( get_request ( "row" , '' ) ) ;
	$text = file_get_contents ( 'http://www.wikidata.org/w/index.php?action=raw&title='.urlencode($page) ) ;
	$text = trim ( $text ) . "\n" . $row ;
	
	if ( ! $oa->setPageText ( $page , $text ) ) {
		$out['error'] = $oa->error ;
	}
	
}

function deletePage () { // ASSUMING BOTMODE
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$page = trim ( get_request ( "page" , '' ) ) ;
	$reason = trim ( get_request ( "reason" , '' ) ) ;
	
	if ( ! $oa->deletePage ( $page , $reason ) ) {
		$out['error'] = $oa->error ;
	}
	
}


function appendText () { // ASSUMING BOTMODE
	global $oa , $botmode , $out ;
	
	if ( !ensureAuth() ) return ;
	show_header() ;

	$page = trim ( get_request ( "page" , '' ) ) ;
	$text = get_request ( "text" , '' ) ;
	$header = get_request ( "header" , '' ) ;
	$summary = get_request ( 'summary' , '' ) ;
	$section = get_request ( 'section' , '' ) ;
	
	if ( ! $oa->addPageText ( $page , $text , $header , $summary , $section ) ) {
		$out['error'] = $oa->error ;
	}
	
}


function getRights () {
	global $oa , $botmode , $out ;
	show_header() ;
	
	$res = $oa->getConsumerRights() ;
	
	if ( $botmode ) {
		$out['result'] = $res ;
	} else {
		print "<pre>" ; print_r ( $res ) ; print "</pre>" ;
	}
	
}

function logout () {
	global $oa , $botmode , $out ;
	show_header() ;
	
	$oa->logout() ;
	
	if ( $botmode ) {
	} else {
		print "<pre>Logged out</pre>" ;
	}
}

function bot_out () {
	global $out , $oa ;
	if ( isset ( $oa->error ) ) $out['error'] = $oa->error ;
	if ( isset($_REQUEST['callback']) ) print $_REQUEST['callback']."(" ;
	print json_encode ( $out ) ;
	if ( isset($_REQUEST['callback']) ) print ");" ;
}


function show_header() {
	global $botmode ;
	if ( $botmode ) return ;
	print get_common_header ( '' , 'WiDaR' ) ;
	print "<div style='float:right'><a href='//en.wikipedia.org/wiki/Widar' title='Víðarr, slaying the dragon of missing claims'><img border=0 src='https://upload.wikimedia.org/wikipedia/commons/thumb/9/95/Vidar_by_Collingwood.jpg/150px-Vidar_by_Collingwood.jpg' /></a></div>" ;
	print "<h1><i>Wi</i>ki<i>Da</i>ta <i>R</i>emote editor</h1>" ;
}

show_header() ;

if ( $botmode ) {
	bot_out() ;
} else {
	print "<div style='margin-bottom:20px'>This is a tool that is used by other tools; it does not have an interface of its own. It can perform batch edits on WikiData under your user name using <a target='_blank' href='https://blog.wikimedia.org/2013/11/22/oauth-on-wikimedia-wikis/'>OAuth</a>.</div>" ;
	print "<div>" ;
	
	
	$res = $oa->getConsumerRights() ;
//	print "<pre>" ;print_r ( $res ) ;print "</pre>" ;
	if ( isset ( $res->error ) ) {
		print "You have not authorized Widar to perform edits on Wikidata on your behalf. <a href='".htmlspecialchars( $_SERVER['SCRIPT_NAME'] )."?action=authorize'>Authorize WiDaR now</a>." ;
	} else {
		print "You have authorized WiDaR to edit as " . $res->query->userinfo->name . ". Congratulations! You can always log out <a href='?action=logout'>here</a>." ;
	}
	
	
	print "</div>" ;
	
	
	
	//print "<div><a href='".htmlspecialchars( $_SERVER['SCRIPT_NAME'] )."?action=edit'>Edit</a></div>" ;

	print "<div><h3>Tools using WiDaR</h3>
	<ul>
	<li><a href='/wikidata-todo/autolist.html'>AutoList</a> and <a href='/wikidata-todo/autolist2.php'>AutoList 2</a></li>
	<li><a href='/reasonator'>Reasonator</a></li>
	<li><a href='/wikidata-todo/creator.html'>Wikidata item creator</a></li>
	<li><a href='/wikidata-game/'>The Wikidata Game</a></li>
	</ul>
	</div>" ;

	print get_common_footer() ;
}

?>