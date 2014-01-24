<?
include("../../php_includes/cmaster.inc");
$cTheme = get_theme_info();
?>
<!-- $Id: legal.php,v 1.4 2002/05/20 23:58:03 nighty Exp $ //-->
<HTML><HEAD><TITLE>Legal Info</TITLE>
<?
	std_theme_styles();
	echo "</head>\n";
	std_theme_body();
?>
<FONT FACE="Arial,Helvetica,sans-serif" size="-1">
<H1>Privacy Policy</h1>
A number of users have asked us about our privacy policy and pointed
out that the 100% clean list of email addresses that we have would
be worth quite a lot on the open market. Indeed, this is quite true,
however, <?=NETWORK_NAME?> and <?=NETWORK_NAME?> CService is not a commercial entity,
and no-one associated with <?=NETWORK_NAME?> is expecting to make any money
out of it. We run it with the expectation that we will loose money
and we accept it. IRC Networks are possibly one of the last true
examples of altruism on the Internet. We do what we do out of the
kindness of out hearts. Honestly.<br>
<br>
That rant aside, our privacy policy is simple. Your email address
will never be passed on to anyone outside of <?=NETWORK_NAME?> Administration.
You have provided us with your email address for one reason and one
reason only. This is to provide us with a means of contacting you
and verifying your identity. Your email address is not publically
viewable and will remain that way. Your email address will not
be transferred by way of sale or other exchange or other means to
any other party other than the administration of Undernet or
verified law enforcement agents who formally require it of us.<br>
<br>
<H1>Linking</h1>
Linking.. what is it all about? We at <?=NETWORK_NAME?> Channel Service
(CService) are all experienced Internet users. Yes, we know that the
World Wide Web (WWW) is built up by people linking from one page to
another. However, we formally require you to only link to
<?

	if (substr_count(IFACE_URL,"/")==2 || (substr_count(IFACE_URL,"/")==3 && strrpos(IFACE_URL,"/")==strlen(IFACE_URL))) {

		echo "<b>" . IFACE_URL . "</b>";

	} else {
		if (substr_count(IFACE_URL,"/")<2) {
			echo "<b>Invalid 'IFACE_URL'</b>";
		} else {
			$new_url = IFACE_URL;
			$r_slash = substr_count($new_url,"/");
			$rc = strlen(IFACE_URL);
			while ($r_slash>2) {
				$new_url = substr(IFACE_URL,0,$rc);
				$rc--;
				$r_slash = substr_count($new_url,"/");
			}
			echo "<b>" . substr(IFACE_URL,0,$rc+2) . "</b>";
		}
	}


?>. You may not link to any other page or
URL on our site. You definately may not reproduce any pages, in whole
or part, from this site at any other site.<BR>
<BR>
This probably seems a little harsh, however we hope you understand our
reasons for doing so. Our site and our policies are constantly changed
to meet the needs of our users and ensure that users have access to the
best quality of information available. We cannot allow the copying of
documents since if the documents are updated, the copied documents
might not be updated, meaning that if users view the documents from
another page, the information they see may be out of date. We also know
that you only mean to help users by copying information or deep-linking,
but the best way that you can help our users is to ensure that you
comply with this policy<BR>
<br>
If you have any questions about either of these policies or how it
affects you, please feel free to email <A HREF="mailto:<?=NETWORK_EMAIL?>"><?=NETWORK_NAME?> Channel Service</a>.
</BODY></HTML>

