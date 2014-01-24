<?php
class SMTPClient
{
function SMTPClient ($SmtpServer, $SmtpPort, $SmtpUser, $SmtpPass, $from, $to, $subject, $body)
{
$this->SmtpServer = $SmtpServer;
$this->SmtpUser = base64_encode ($SmtpUser);
$this->SmtpPass = base64_encode ($SmtpPass);
$this->from = $from;
$this->to = $to;
$this->subject = $subject;
$this->body = $body;
	if ($SmtpPort == "") 
	{
	$this->PortSMTP = 25;
		}else{
	$this->PortSMTP = $SmtpPort;
	}
}
                   
function SendMail ()
{

	$SMTPIN = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($SMTPIN === false) {
				    $errorcode = socket_last_error();
				    $errormsg = socket_strerror($errorcode);
    					$talk="Couldn't create socket (1): [$errorcode] $errormsg";
    					local_seclog("custom_mail() failed for " . $this->to . " with message:".$talk);
    					return $talk;
				    die;
					}
	if ( false === socket_bind($SMTPIN, BIND_OUT_IP))
					 {
					 $errorcode = socket_last_error();
			    		 $errormsg = socket_strerror($errorcode);
			    		 $talk="Couldn't create socket (2): [$errorcode] $errormsg";
			    		 local_seclog("custom_mail() failed for " . $this->to . " with message:".$talk);
					 return $talk;
				         die;
			   	
			   		 }

	if ($SMTPIN2 = socket_connect ($SMTPIN, $this->SmtpServer, $this->PortSMTP)) 
	{
           unset ($talk);
           socket_write ($SMTPIN, "EHLO ".$HTTP_HOST."\r\n");  
           $talk["hello"] ="EHLO ".socket_read ( $SMTPIN, 1024 ); 
                   
		   socket_write($SMTPIN, "auth login\r\n");
		   $talk["res"]="AUHT LOGIN ".socket_read($SMTPIN,1024);
			socket_write($SMTPIN, $this->SmtpUser."\r\n");
		    $talk["user"]="USER ".socket_read($SMTPIN,1024);
		    
		    socket_write($SMTPIN, $this->SmtpPass."\r\n");
			$talk["pass"]="PASS ".socket_read($SMTPIN,256);
			   	    
           socket_write ($SMTPIN, "MAIL FROM: <".$this->from.">\r\n");  
           $talk["From"] ="MAIL FROM ".socket_read ( $SMTPIN, 1024 );  
           socket_write ($SMTPIN, "RCPT TO: <".$this->to.">\r\n");  
           $talk["To"] = "RCPT TO ".socket_read ($SMTPIN, 1024); 
           
           socket_write($SMTPIN, "DATA\r\n");
			$talk["data"]="DATA ".socket_read( $SMTPIN,1024 );
           
			
			socket_write($SMTPIN, "To: <".$this->to.">\r\nFrom: <".$this->from.">\r\nSubject:".$this->subject."\r\n\r\n\r\n".$this->body."\r\n.\r\n");
			$talk["send"]="SEND ".socket_read($SMTPIN,256);

           socket_write ($SMTPIN, "QUIT\r\n");  
           socket_close($SMTPIN); 
	}  
return $talk;
}        
}
?>
