<?
	$min_lvl=800;

	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
        if (!acl(XWEBCTL)) {
        	echo "<b>Go away</b>!!!";
        	die;
        }
  	
  	if ($switch=="ON" && site_off()) {
  		echo "Already LOCKED";
  		die;
  	}
  	if ($switch=="OFF" && !site_off()) {
  		echo "Already UNLOCKED";
  		die;
  	}        
        
        if ($switch=="ON") { pg_safe_exec("INSERT INTO locks VALUES (1,now()::abstime::int4," . $user_id . ")"); }
        if ($switch=="OFF") { pg_safe_exec("DELETE FROM locks WHERE section='1'"); }
        
        header("Location: index.php");
        die;
        
?>
