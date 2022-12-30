<?
	$min_lvl=800;
	/* $Id: newregs.php,v 1.2 2003/08/31 19:52:16 nighty Exp $ */
	require("../../../php_includes/cmaster.inc");
	std_connect();
        $user_id = std_security_chk($auth);
        $admin = std_admin();
        if (!acl(XWEBCTL)) {
        	echo "<b>Go away</b>!!!";
        	die;
        }
  	
  	if ($switch=="ON" && newregs_off()) {
  		echo "Already LOCKED";
  		die;
  	}
  	if ($switch=="OFF" && !newregs_off()) {
  		echo "Already OFF";
  		die;
  	}        
        
        if ($switch=="ON") { pg_safe_exec("INSERT INTO locks VALUES (2,date_part('epoch', CURRENT_TIMESTAMP)::int," . $user_id . ")"); }
        if ($switch=="OFF") { pg_safe_exec("DELETE FROM locks WHERE section='2'"); }
        
        header("Location: redir.php?RET=index.php");
        die;
        
?>
