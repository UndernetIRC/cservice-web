<?
/* $Id: index.php,v 1.7 2004/02/15 07:59:47 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
require_once("../../../php_includes/SOAP/nusoap.php"); // uses the NuSOAP API released under GPL license (http://www.sf.net/projects/nusoap/)

/*
	SOAP Interface to CMaster
	-------------------------

	Original idea: Isomer.

	This is a new interface to access data from remote clients directly without using a Web browser.
	This require a SOAP capable client we do NOT provide at the present moment.

	Certain functions calls in the future may require you to pass your username/password each time
	so your rights to access some elements can be checked properly.

	More function will be implemented soon, here are the ones currently availabe, how you call them,
	and what they are returning ... so you can use them for more effective bot scripts or even
	windows query tools.

	The SOAP Interface URL is : 	http://www.yoursite.com/live/SOAP/ (or http://www.yoursite.com/live/SOAP/index.php),
	depending on your "/live" interface as defined in the 'website/php_includes/config.inc'.

--------------------------------------------------------------------------------------------------------------------------------
	channelAccessList():
		Parameters:		channel_name	: full name of the channel you want to check accesses on
					match_pattern	: wildcarded (*,?) pattern for usernames matching (default : *)
		Returned object:
					Array of objects
						Return_Value[0]->query_status		0: channel not found, 1: channel found, 2: not enough privileges
						Return_Value[0]->nb_match
						Return_Value[0]->channel_id

						n>=1:
						Return_Value[n]->username
						Return_Value[n]->level			channel's access level (1 to 500)
						Return_Value[n]->automode		0: none, 1: voice, 2: op
						Return_Value[n]->is_suspended		0 or 1 (hear "suspended on the channel", not "globally")
						Return_Value[n]->suspend_expires	unix timestamp
						Return_Value[n]->suspend_by		nick!user@host
						Return_Value[n]->suspend_level		suspension level (>500 = admin)
						etc...
--------------------------------------------------------------------------------------------------------------------------------
	checkApplication():
		Parameters:		channel_name	: full name of the channel you want to check application for.
		Returned object:
					Object
						Return_Value->query_status	0: app not found, 1: app found
						Return_Value->app_status	0,1,2,3,4,8 or 9.
						Return_Value->channel_id
						Return_Value->app_timestamp
						Return_Value->nb_objections
						Return_Value->applicant_username
						Return_Value->decision_timestamp
						Return_Value->decision_comment

	'app_status' codes are :
				0: Incoming (pending supporter confirmation)
				1: Pending (traffic check)
				2: Pending (notification)
				3: Accepted/Registered
				4: Cancelled by applicant

				8: Ready for review
				9: Rejected
--------------------------------------------------------------------------------------------------------------------------------
	setUserMaxlogins():
		Parameters:		dest_username	: user name of which you wish to change the MAXLOGINS settings
					new_maxlogins	: the NEX 'MAXLOGINS' value (from 1  to MAX_MAXLOGINS, defined in cmaster.inc)
					admin_user	: a valid username of someone having access to * at at least level MOD_MAXLOGINS_LEVEL)
					admin_pass	: the 'admin_user''s password.
		Returned object:
					Integer signed
						 1	Update successfull
						 0	Update query failed
						-1	Invalid admin_user and/or admin_pass
						-2	admin_user's admin level is too low
						-3	dest_username is invalid
--------------------------------------------------------------------------------------------------------------------------------
	getUserInfo():
		Parameters:		user_name	: the username to lookup or an empty string
					user_id		: the user_id to lookup or 0 (if both user_name and user_id are filled, user_id is taken unless it's <=0)
					admin_user	: a valid username of someone having access to * to see more info (or empty string)
					admin_pass	: the 'admin_user''s password if admin_user is specified (or empty string)
		Returned object:
					Object
						Return_Value->query_status
						Return_Value->id
						Return_Value->user_name
						Return_Value->invisible_status

						Return_Value->language_id
						Return_Value->last_hostmask
						Return_Value->last_seen
						Return_Value->email
						Return_Value->is_noreg
						Return_Value->is_fraud
						Return_Value->is_globally_suspended
						Return_Value->is_csc
						Return_Value->maxlogins
						Return_Value->admin_comment

						Return_Value->is_nopurge

						Return_Value->signup_ip
--------------------------------------------------------------------------------------------------------------------------------
	getUserAccess():
		Parameters:		user_name	: the username to lookup or an empty string
					user_id		: the user_id to lookup or 0 (if both user_name and user_id are filled, user_id is taken unless it's <=0)
					admin_user	: a valid username of someone having access to *.
					admin_pass	: the 'admin_user''s password.
		Returned object:
					Object
						Return_Value[0]->query_status
						Return_Value[0]->nb_match


						Return_Value[n]->channel_id
						Return_Value[n]->channel_name
						Return_Value[n]->channel_level
						Return_Value[n]->channel_automode
						Return_Value[n]->channel_is_suspended
						etc...
--------------------------------------------------------------------------------------------------------------------------------
	isNoreg():
		Parameters:		data		: username or channel name (everything starting with # is considered a channel)
							  THIS ACCEPT NO WILDCARDS it just check for a specific username, channel or email is in NOREG or not.
					admin_user	: a valid username of someone having access to *.
					admin_pass	: the 'admin_user''s password.
		Returned object:
					Object
						Return_Value->query_status
						Return_Value->is_noreg
						Return_Value->noreg_expires
						Return_Value->noreg_reason
						Return_Value->noreg_type
						Return_Value->added_by
--------------------------------------------------------------------------------------------------------------------------------
	isDomainLock():
		Parameters:		data		: can be a full email(user@site.com) or just the domain name (site.com)
							  THIS ACCEPT NO WILDCARDS it just check for a specific thing if its locked or not.
					admin_user	: a valid username of someone having access to *.
					admin_pass	: the 'admin_user''s password.
		Returned object:
					Object
						Return_Value->query_status
						Return_Value->is_locked
						Return_Value->lock_reason
						Return_Value->lock_type
						Return_Value->added_by
--------------------------------------------------------------------------------------------------------------------------------


Below a short example of how you can create a PHP SOAP Client using the 'nusoap' PHP library (thanks to HeKTik):


	$My_SOAP_Client = new soapclient("http://cservice.undernet.org/live/SOAP/index.php");

	// checking accesslist for * on #some_channel
	$My_Result1 = $My_SOAP_Client->call('channelAccessList',array('channel_name'=>'#some_channel','match_pattern'=>'*'));

	// checking application information for #other_channel
	$My_Result2 = $My_SOAP_Client->call('checkApplication',array('channel_name'=>'#other_channel'));

	// setting MAXLOGINS to 3 for newbie42, by admin1
	$My_Result3 = $My_SOAP_Client->call('setUserMaxlogins',array('dest_username'=>'newbie42','new_maxlogins'=>'3','admin_user'=>'admin1','admin_pass'=>'xxxxxx'));


*/

function channelAccessList( $channel_name , $match_pattern = "*" ) {
	unset($rVal);
	$rVal = Array();
	$ccQuery = "SELECT id FROM channels WHERE registered_ts>0 AND lower(channels.name)='" . addslashes(strtolower($channel_name)) . "'";
	$ccRes = pg_safe_exec($ccQuery);
	if ($channel_name=="*" || pg_numrows($ccRes)==0) { // non registered channel!
		$rVal[0]->query_status = 0;
		$rVal[0]->nb_match = 0;
		$rVal[0]->channel_id = 0;
		return $rVal;
	} else { // channel exists...
		$ccRow = pg_fetch_object($ccRes);
		$rVal[0]->query_status = 1;
		$rVal[0]->channel_id = $ccRow->id;
		$cQuery = "SELECT users.user_name,levels.access,levels.flags,levels.suspend_expires>=date_part('epoch', CURRENT_TIMESTAMP)::int AS is_suspended,levels.suspend_expires,levels.suspend_level,levels.suspend_by FROM users,levels WHERE levels.channel_id='" . $ccRow->id . "' AND users.id=levels.user_id";
		if ($match_pattern != "*") {
			$match_string = str_replace("?","_",str_replace("*","%",str_replace("%","\%",str_replace("_","\_",$match_pattern))));
			$cQuery .= " AND lower(users.user_name) LIKE '" . strtolower($match_string) . "'";
		}
		$cQuery .= " ORDER BY levels.access DESC";
		$cRes = pg_safe_exec($cQuery);
		$cCount = pg_numrows($cRes);
		$rVal[0]->nb_match = $cCount;
		if ($cCount == 0) { return $rVal; } // no matching results.
		for ($x=0;$x<$cCount;$x++) {
			$cRow = pg_fetch_object($cRes,$x);
			$rVindex = $x+1;
			$rVal[$rVindex]->username = $cRow->user_name;
			$rVal[$rVindex]->level = $cRow->access;
			if ((int)$cRow->flags & 0x0001) {
				$rVal[$rVindex]->automode = 2;
			} else if ((int)$cRow->flags & 0x0008) {
				$rVal[$rVindex]->automode = 1;
			} else {
				$rVal[$rVindex]->automode = 0;
			}
			if ($cRow->is_suspended && $cRow->suspend_expires>0) {
				$rVal[$rVindex]->is_suspended = 1;
				$rVal[$rVindex]->suspend_expires = $cRow->suspend_expires;
				$rVal[$rVindex]->suspend_by = $cRow->suspend_by;
				$rVal[$rVindex]->suspend_level = $cRow->suspend_level;
			} else {
				$rVal[$rVindex]->is_suspended = 0;
				$rVal[$rVindex]->suspend_expires = 0;
				$rVal[$rVindex]->suspend_by = "";
				$rVal[$rVindex]->suspend_level = 0;
			}
		}
		return $rVal;
	}
}

function checkApplication( $channel_name ) {
	unset($rVal);
	$cQuery = "SELECT channels.name,users.user_name,channels.id as c_id,channels.registered_ts,pending.created_ts,pending.decision_ts,pending.decision,pending.status FROM pending,channels,users WHERE users.id=pending.manager_id AND lower(channels.name)='" . addslashes(strtolower($channel_name)) . "' AND channels.id=pending.channel_id";
	$cRes = pg_safe_exec($cQuery);
	if (pg_numrows($cRes)==0) { // nothing matching the table-join with 'pending' applications .. either REGGED or NOT FOUND...
		$rRes = pg_safe_exec("SELECT * FROM channels WHERE lower(name)='" . addslashes(strtolower($channel_name)) . "' AND registered_ts>0");
		if (pg_numrows($rRes)>0) {
			// Registered channel
			$rRow = pg_fetch_object($rRes);
			$rVal->query_status = 1;
			$rVal->app_status = 3;
			$rVal->channel_id = $rRow->id;
			$rVal->app_timestamp = $rRow->registered_ts;
			$rVal->nb_objections = 0;

			// get the owner username
			$oQuery = "SELECT users.user_name FROM users,levels WHERE levels.channel_id='" . $rRow->id . "' AND levels.access=500 AND users.id=levels.user_id LIMIT 1";
			$oRes = pg_safe_exec($oQuery);
			if ($oRow = pg_fetch_object($oRes)) {
				$rVal->applicant_username = $oRow->user_name;
			} else {
				$rVal->applicant_username = "(!)NO_OWNER(!)";
			}

			$rVal->decision_timestamp = $rRow->registered_ts;
			$rVal->decision_comment = "* Registered *";
			return $rVal;
		} else {
			// No application found!
			$rVal->query_status = 0;
			$rVal->app_status = 0;
			$rVal->channel_id = 0;
			$rVal->app_timestamp = 0;
			$rVal->nb_objections = 0;
			$rVal->applicant_username = "";
			$rVal->decision_timestamp = 0;
			$rVal->decision_comment = "-";
			return $rVal;
		}
	}
	$cRow = pg_fetch_object($cRes,0);
	$ocQuery = "SELECT COUNT(channel_id) AS count FROM objections WHERE channel_id='" . $cRow->c_id . "' AND admin_only!='Y'";
	$ocRes = pg_safe_exec($ocQuery);
	$ocRow = pg_fetch_object($ocRes);

	if ($cRow->decision_ts==0 || $cRow->decision_ts>(time()-5*86400)) { //no decision or decision younger than 5 days ...
		// Application found!
	        if ($cRow->decision_ts>0) { $dcomment = $cRow->decision; } else { $dcomment = "-"; }
		$rVal->query_status = 1;
		$rVal->app_status = $cRow->status;
		$rVal->channel_id = $cRow->c_id;
		$rVal->app_timestamp = $cRow->created_ts;
		$rVal->nb_objections = $ocRow->count;
		$rVal->applicant_username = $cRow->user_name;
		$rVal->decision_timestamp = $cRow->decision_ts;
		$rVal->decision_comment = strip_tags($dcomment);
		return $rVal;
	} else {
	        if ($cRow->registered_ts>0) { // channel is registered ?
	                // Registered channel
			$rVal->query_status = 1;
			$rVal->app_status = 3;
			$rVal->channel_id = $cRow->c_id;
			$rVal->app_timestamp = $cRow->registered_ts;
			$rVal->nb_objections = 0;
			$rVal->applicant_username = $cRow->user_name;
			$rVal->decision_timestamp = $cRow->registered_ts;
			$rVal->decision_comment = "* Registered *";
			return $rVal;
	        } else {
			// No application found!
			$rVal->query_status = 0;
			$rVal->app_status = 0;
			$rVal->channel_id = 0;
			$rVal->app_timestamp = 0;
			$rVal->nb_objections = 0;
			$rVal->applicant_username = "";
			$rVal->decision_timestamp = 0;
			$rVal->decision_comment = "-";
			return $rVal;
	        }
	}
}

function setUserMaxlogins( $dest_username, $new_maxlogins, $admin_user, $admin_pass ) {
	global $user_id;
	unset($rVal);
	$cUser = validateUser($admin_user,$admin_pass,1);
	if ($cUser->id==0) { return(-1); }
	if ($cUser->admlvl<MOD_MAXLOGINS_LEVEL) { return(-2); } // minimum level to set the maxlogins value for someone. (see cmaster.inc)
	if ($new_maxlogins<1) { $new_maxlogins = 1; }
	if ($new_maxlogins>MAX_MAXLOGINS) { $new_maxlogins = MAX_MAXLOGINS; }
	if (strtolower($dest_username)==strtolower($admin_user)) { $log_line = 0; } else { $log_line = 1; }
	$dQuery = "SELECT id FROM users WHERE lower(user_name)='" . strtolower(trim($dest_username)) . "'";
	$dRes = pg_safe_exec($dQuery);
	if (pg_numrows($dRes)==0) { return(-3); }
	$dUser = pg_fetch_object($dRes);
	$sQuery = "UPDATE users SET maxlogins='" . $new_maxlogins . "',last_updated=date_part('epoch', CURRENT_TIMESTAMP)::int,last_updated_by='SOAP Interface (" . $admin_user . ")' WHERE id='" . $dUser->id . "'";
	if ($log_line) {
		$user_id = $cUser->id;
		log_user($dUser->id,3,"- Maxlogins (SOAP)");
	}
	$sRes = pg_safe_exec($sQuery);
	if (!$sRes) { return 0; } else { return 1; }
}

function validateUser( $user_name , $cleartext_password , $admin_check = 0) { // this procedure is not used and is here for further reference.
	unset($retUser);
	$retUser->id = 0;
	$retUser->name = $user_name;
	$retUser->admlvl = 0;
	if (!preg_match("/^[A-Za-z0-9_^`-]+$/",$user_name)) { return $retUser; } // safety sanity check.
	$vQuery = "SELECT id,password FROM users WHERE lower(user_name)='" . strtolower($user_name) . "'";
	$vRes = pg_safe_exec($vQuery);
	if (!$vRes) { return $retUser; }
	$vRow = pg_fetch_object($vRes);
	$passOK = 0;
	if ($vRow->password == "") { $passOK = 1; }
	if (!$passOK) {
		$salt = substr($vRow->password,0,8);
		$crypt_test = md5( $salt . $cleartext_password );
		if ($vRow->password == $salt . $crypt_test) { $passOK = 1; }
	}
	if ($passOK == 0) { return $retUser; }
	$retUser->id = $vRow->id;
	if ($admin_check > 0) {
		$aQuery = "SELECT access FROM levels WHERE channel_id=1 AND user_id='" . $vRow->id . "'";
		$aRes = pg_safe_exec($aQuery);
		if (!$aRes) { return $retUser; }
		$aRow = pg_fetch_object($aRes);
		$retUser->admlvl = $aRow->access;
	}
	return $retUser; // returns an object contains user information (id, username, admin_level)
}

std_connect();					// open connexion to database (needed by the procedures)

$server = new soap_server;			// create a new SOAP server object

$server->register('channelAccessList');		// register publically available functions
$server->register('checkApplication');
$server->register('setUserMaxlogins');

$server->service($HTTP_RAW_POST_DATA);		// gather SOAP client data and pass it to the SERVER to get the result.

?>
