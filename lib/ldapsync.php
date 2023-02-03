<?php

require_once(__DIR__.'/../lib/loader.php');


if(LDAP_SERVER == null) {
	die("LDAP Sync Not Configured!\n");
}

// connect to server
$ldapconn = ldap_connect(LDAP_SERVER);
if(!$ldapconn) {
	die("ldap_connect FAILED.\n");
}
echo "<=== ldap_connect OK ===>\n";

// set options and authenticate
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5);
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0 );
$ldapbind = ldap_bind($ldapconn, LDAP_USER.'@'.LDAP_DOMAIN, LDAP_PASS);
if(!$ldapbind) {
	die("ldap_bind FAILED.".ldap_error($ldapconn)."\n");
}
echo "<=== ldap_bind OK ===>\n";

// ldap search with paging support
$data = [];
$cookie = null;
do {
	$result = ldap_search(
		$ldapconn, LDAP_QUERY_ROOT, "(objectClass=user)",
		[] /*attributes*/, 0 /*attributes_only*/, -1 /*sizelimit*/, -1 /*timelimit*/, LDAP_DEREF_NEVER,
		[ ['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 750, 'cookie' => $cookie]] ]
	);
	if(!$result) {
		die("ldap_search FAILED: ".ldap_error($ldapconn)."\n");
	}
	ldap_parse_result($ldapconn, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
	$data = array_merge($data, ldap_get_entries($ldapconn, $result));
	if(isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
		$cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
	} else {
		$cookie = null;
	}
} while(!empty($cookie));
echo "<=== ldap_search OK - processing entries... ===>\n";

// iterate over result array
$ldapUsers = [];
$counter = 1;
foreach($data as $key => $account) {
	if(!is_numeric($key)) continue; // skip "count" entry
	#var_dump($account); /*die();*/ // debug

	$color = '#'.dechex(rand(20,210)).dechex(rand(20,210)).dechex(rand(20,210));
	$login = $account["samaccountname"][0];
	$firstname = "?";
	$lastname = "?";
	$fullname = "?";
	$mail = null;
	$phone = null;
	$mobile = null;
	$description = null;
	if(isset($account["givenname"][0]))
		$firstname = $account["givenname"][0];
	if(isset($account["sn"][0]))
		$lastname = $account["sn"][0];
	if(isset($account["displayname"][0]))
		$fullname = $account["displayname"][0];
	if(isset($account["mail"][0]))
		$mail = $account["mail"][0];
	if(isset($account["telephonenumber"][0]))
		$phone = $account["telephonenumber"][0];
	if(isset($account["mobile"][0]))
		$mobile = $account["mobile"][0];
	if(isset($account["description"][0]))
		$description = $account["description"][0];

	// check group
	$groupCheck = false;
	if(LDAP_SYNC_GROUP == null) {
		$groupCheck = true;
	} else if(isset($account["memberof"])) {
		for($n=0; $n<$account["memberof"]["count"]; $n++) {
			if($account["memberof"][$n] == LDAP_SYNC_GROUP) {
				$groupCheck = true;
				break;
			}
		}
	}
	if(!$groupCheck) {
		echo "--> skip user ".htmlspecialchars($login)." : not in required group\n";
		continue;
	}

	// add to found array
	$ldapUsers[] = $login;

	// check if user already exists
	$id = null;
	$checkResult = $db->getUserByLogin($login);
	if($checkResult != null) {
		$id = $checkResult->id;
		echo "--> found user in MASTERPLAN db with id: ".$id."\n";

		// update into db
		if($db->updateUserLdap($id, $checkResult->superadmin, $login, $firstname, $lastname, $fullname, $mail, $phone, $mobile, $description, 1))
			echo "--> updated successfully\n";
		else echo "--> ERROR updating: ".$db->getLastStatement()->error."\n";
	} else {
		echo "--> user not found in MASTERPLAN db - create new\n";

		// insert into db
		$currentDate = date('Y-m-d');
		if($db->insertUserLdap(0, $login, $firstname, $lastname, $fullname, $mail, $phone, $mobile, $currentDate, $description, 1, $color))
			echo "--> inserted successfully\n";
		else echo "--> ERROR inserting: ".$db->getLastStatement()->error."\n";
	}
	$counter ++;
}
ldap_close($ldapconn);

echo "<===== Check For Deleted Users... =====>\n";
foreach($db->getUsers() as $dbUser) {
	if($dbUser->ldap != 1) continue;
	$found = false;
	foreach($ldapUsers as $ldapUser) {
		if($dbUser->login == $ldapUser) {
			$found = true;
		}
	}
	if(!$found) {
		if($db->removeUser($dbUser->id)) echo "--> '".$dbUser->login."' deleted successfully\n";
		else echo "--> ERROR deleting '".$dbUser->login."': ".$db->getLastStatement()->error."\n";
	}
}
