<?
/* $Id: testClient.php,v 1.2 2003/01/06 07:02:17 nighty Exp $ */
require("../../../php_includes/cmaster.inc");
require_once("../../../php_includes/SOAP/nusoap.php"); // uses the NuSOAP API released under GPL license (http://www.sf.net/projects/nusoap/)

/*
	SOAP Test client
	** Uncomment the next die() statement to enable/test **
*/
//die("This page is disabled in production mode!");

echo "<html><head><title>SOAP testClient</title></head>\n";
echo "<body bgcolor=#ffffff>\n";
echo "<a href=\"testClient.php\"><h2>SOAP testClient</h2></a>\n";
echo "<br>SOAP Server: <b>" . IFACE_URL . "/SOAP/</b><br>\n";
echo "If you need more information about NuSOAP, please visit <a href=\"http://www.sf.net/projects/nusoap/\" target=_blank>http://www.sf.net/projects/nusoap/</a><br><br>";

echo "<hr noshade size=1 width=100%>\n";
echo "function <b>checkApplication( </b><i>channel_name</i><b> )</b><br>\n";
echo "<form name=checkApp method=get>\n";
echo "<input type=hidden name=action value=1>\n";
echo "<i>channel_name</i>&nbsp;<input type=text name=channel_name size=30 maxlength=255 value=\"";
if ($_GET["action"]==1) { echo str_replace("\"","&quot;",stripslashes($_GET["channel_name"])); } else { echo "#"; }
echo "\"><br>\n";
echo "<br><br><input type=submit value=Go!>\n";
echo "</form>\n";

echo "<hr noshade size=1 width=100%>\n";
echo "function <b>channelAccessList( </b><i>channel_name</i><b>, </b><i>match_pattern</i><b> )</b><br>\n";
echo "<form name=channelAccess method=get>\n";
echo "<input type=hidden name=action value=2>\n";
echo "<i>channel_name</i>&nbsp;<input type=text name=channel_name size=30 maxlength=255 value=\"";
if ($_GET["action"]==2) { echo str_replace("\"","&quot;",stripslashes($_GET["channel_name"])); } else { echo "#"; }
echo "\"><br>\n";
echo "<i>match_pattern</i>&nbsp;<input type=text name=match_pattern size=30 maxlength=255 value=\"";
if ($_GET["action"]==2) { echo str_replace("\"","&quot;",stripslashes($_GET["match_pattern"])); } else { echo "*"; }
echo "\"> (accepted wildcards: <b>*</b> and <b>?</b>)<br>\n";
echo "<br><br><input type=submit value=Go!>\n";
echo "</form>\n";


echo "<hr noshade size=1 width=100%>\n";
echo "function <b>setUserMaxlogins( </b><i>dest_username</i><b>, </b><i>new_maxlogins</i><b>, </b><i>admin_user</i><b>, </b><i>admin_pass</i><b> )</b><br>\n";
echo "<form name=userMaxlogins method=post action=testClient.php?action=3>\n";
//echo "<input type=hidden name=action value=3>\n";
echo "<i>dest_username</i>&nbsp;<input type=text name=dest_username size=30 maxlength=12 value=\"";
if ($_GET["action"]==3) { echo str_replace("\"","&quot;",stripslashes($_POST["dest_username"])); } else { echo ""; }
echo "\"><br>\n";
echo "<i>new_maxlogins</i>&nbsp;<input type=text name=new_maxlogins size=30 maxlength=3 value=\"";
if ($_GET["action"]==3) { echo ($new_maxlogins+0); } else { echo "1"; }
echo "\"> (valid values ranges from 1 to " . MAX_MAXLOGINS . ")<br>\n";
echo "<i>admin_user</i>&nbsp;<input type=text name=admin_user size=30 maxlength=12 value=\"";
if ($_GET["action"]==3) { echo str_replace("\"","&quot;",stripslashes($_POST["admin_user"])); } else { echo ""; }
echo "\"><br>\n";
echo "<i>admin_pass</i>&nbsp;<input type=password name=admin_pass size=30 maxlength=255><br>\n";
echo "<br><br><input type=submit value=Go!>\n";
echo "</form>\n";


echo "<hr noshade size=1 width=100%>\n";

switch ($_GET["action"]) {
	case 1:
		$SOAP_Client = new soapclient(IFACE_URL . "/SOAP/");
		$SOAP_Result = $SOAP_Client->call('checkApplication',array('channel_name'=>stripslashes($_GET["channel_name"])));
		break;

	case 2:
		$SOAP_Client = new soapclient(IFACE_URL . "/SOAP/");
		$SOAP_Result = $SOAP_Client->call('channelAccessList',array('channel_name'=>stripslashes($_GET["channel_name"]),'match_pattern'=>stripslashes($_GET["match_pattern"])));
		break;

	case 3:
		$SOAP_Client = new soapclient(IFACE_URL . "/SOAP/");
		$SOAP_Result = $SOAP_Client->call('setUserMaxlogins',array('dest_username'=>stripslashes($_POST["dest_username"]),'new_maxlogins'=>stripslashes($_POST["new_maxlogins"]),'admin_user'=>stripslashes($_POST["admin_user"]),'admin_pass'=>stripslashes($_POST["admin_pass"])));
		break;
}

if ($_GET["action"]>0) {
	echo "<h3><font color=#ff1111>SOAP Result :</font></h3>\n";
	if (is_array($SOAP_Result)) {
		print_r($SOAP_Result);
	} else {
		echo $SOAP_Result;
	}
}

echo "</body></html>\n\n";
?>
