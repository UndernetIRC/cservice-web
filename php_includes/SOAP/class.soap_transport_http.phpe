<?php



/**
* transport class for sending/receiving data via HTTP and HTTPS
* NOTE: PHP must be compiled with the CURL extension for HTTPS support
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  v 0.6.3
* @access public
*/
class soap_transport_http extends nusoap_base {

	var $username = '';
	var $password = '';
	var $url = '';
    var $proxyhost = '';
    var $proxyport = '';
	var $scheme = '';
	var $protocol_version = '1.0';
	var $encoding = '';
	var $outgoing_headers = array();
	var $incoming_headers = array();
	var $outgoing_payload = '';
	var $incoming_payload = '';
	
	/**
	* constructor
	*/
	function soap_transport_http($url){
		$this->url = $url;
		$u = parse_url($url);
		foreach($u as $k => $v){
			$this->debug("$k = $v");
			$this->$k = $v;
		}
		if(isset($u['query']) && $u['query'] != ''){
            $this->path .= '?' . $u['query'];
		}
		if(!isset($u['port']) && $u['scheme'] == 'http'){
			$this->port = 80;
		}
	}
	
	function connect($timeout){
		
		// proxy
		if($this->proxyhost != '' && $this->proxyport != ''){
			$host = $this->proxyhost;
			$port = $this->proxyport;
			$this->debug("using http proxy: $host, $port");
		} else {
			$host = $this->host;
			$port = $this->port;
		}
		// ssl
		if($this->scheme == 'https'){
			$host = 'ssl://'.$host;
			$port = 443;
		}
		
		$this->debug("connection params: $host, $port");
		// timeout
		if($timeout > 0){
			$fp = fsockopen($host, $port, $this->errno, $this->error_str, $timeout);
		} else {
			$fp = fsockopen($host, $port, $this->errno, $this->error_str);
		}
		
		// test pointer
		if(!$fp) {
			$this->debug('Couldn\'t open socket connection to server '.$this->url.', Error: '.$this->error_str);
			$this->setError('Couldn\'t open socket connection to server: '.$this->url.', Error: '.$this->error_str);
			return false;
		}
		return $fp;
	}
	
	/**
	* send the SOAP message via HTTP
	*
	* @param    string $data message data
	* @param    integer $timeout set timeout in seconds
	* @return	string data
	* @access   public
	*/
	function send($data, $timeout=0) {
		$this->debug('entered send() with data of length: '.strlen($data));
		// get connnection
		if(!$fp = $this->connect($timeout)){
			return false;
		}
		$this->debug('socket connected');
		
		// start building outgoing payload:
		// swap url for path if going through a proxy
		if($this->proxyhost != '' && $this->proxyport != ''){
			$this->outgoing_payload = "POST $this->url ".strtoupper($this->scheme)."/$this->protocol_version\r\n";
		} else {
			$this->outgoing_payload = "POST $this->path ".strtoupper($this->scheme)."/$this->protocol_version\r\n";
		}
		// make payload
		$this->outgoing_payload .=
			"User-Agent: $this->title/$this->version\r\n".
			"Host: ".$this->host."\r\n";
		// http auth
		$credentials = '';
		if($this->username != '') {
			$this->debug('setting http auth credentials');
			$this->outgoing_payload .= 'Authorization: Basic '.base64_encode("$this->username:$this->password")."\r\n";
		}
		// set content type
		$this->outgoing_payload .= 'Content-Type: text/xml; charset="'.$this->soap_defencoding."\"\r\nContent-Length: ".strlen($data)."\r\n";
		// http encoding
		if($this->encoding != '' && function_exists('gzdeflate')){
			$this->outgoing_payload .= "Accept-Encoding: $this->encoding\r\n".
			"Connection: close\r\n";
			set_magic_quotes_runtime(0);
		}
		// set soapaction
		$this->outgoing_payload .= "SOAPAction: \"$this->soapaction\""."\r\n\r\n";
		// add data
		$this->outgoing_payload .= $data;
		
		// send payload
		if(!fputs($fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
			$this->setError('couldn\'t write message data to socket');
			$this->debug('Write error');
		}
		$this->debug('wrote data to socket');
		
		// get response
	    $this->incoming_payload = '';
		//$strlen = 0;
		while( $data = fread($fp, 32768) ){
			$this->incoming_payload .= $data;
			//$strlen += strlen($data);
	    }
		$this->debug('received '.strlen($this->incoming_payload).' bytes of data from server');
		
		// close filepointer
		fclose($fp);
		$this->debug('closed socket');
		
		// connection was closed unexpectedly
		if($this->incoming_payload == ''){
			$this->setError('no response from server');
			return false;
		}
		
		$this->debug('received incoming payload: '.strlen($this->incoming_payload));
		$data = $this->incoming_payload."\r\n\r\n\r\n\r\n";
		
		// remove 100 header
		if(ereg('^HTTP/1.1 100',$data)){
			if($pos = strpos($data,"\r\n\r\n") ){
				$data = ltrim(substr($data,$pos));
			} elseif($pos = strpos($data,"\n\n") ){
				$data = ltrim(substr($data,$pos));
			}
		}//
		
		// separate content from HTTP headers
		if( $pos = strpos($data,"\r\n\r\n") ){
			$lb = "\r\n";
		} elseif( $pos = strpos($data,"\n\n") ){
			$lb = "\n";
		} else {
			$this->setError('no proper separation of headers and document');
			return false;
		}
		$header_data = trim(substr($data,0,$pos));
		$header_array = explode($lb,$header_data);
		$data = ltrim(substr($data,$pos));
		$this->debug('found proper separation of headers and document');
		$this->debug('cleaned data, stringlen: '.strlen($data));
		// clean headers
		foreach($header_array as $header_line){
			$arr = explode(':',$header_line);
			if(count($arr) >= 2){
				$headers[trim($arr[0])] = trim($arr[1]);
			}
		}
		//print "headers: <pre>$header_data</pre><br>";
		//print "data: <pre>$data</pre><br>";
		
		// decode transfer-encoding
		if(isset($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] == 'chunked'){
			//$timer->setMarker('starting to decode chunked content');
			if(!$data = $this->decodeChunked($data)){
				$this->setError('Decoding of chunked data failed');
				return false;
			}
			//$timer->setMarker('finished decoding of chunked content');
			//print "<pre>\nde-chunked:\n---------------\n$data\n\n---------------\n</pre>";
		}
		
		// decode content-encoding
		if(isset($headers['Content-Encoding']) && $headers['Content-Encoding'] != ''){
			if($headers['Content-Encoding'] == 'deflate' || $headers['Content-Encoding'] == 'gzip'){
    			// if decoding works, use it. else assume data wasn't gzencoded
    			if(function_exists('gzinflate')){
					//$timer->setMarker('starting decoding of gzip/deflated content');
					if($headers['Content-Encoding'] == 'deflate' && $degzdata = @gzinflate($data)){
    					$data = $degzdata;
					} elseif($headers['Content-Encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10))){
						$data = $degzdata;
					} else {
						$this->setError('Errors occurred when trying to decode the data');
					}
					//$timer->setMarker('finished decoding of gzip/deflated content');
					//print "<xmp>\nde-inflated:\n---------------\n$data\n-------------\n</xmp>";
    			} else {
					$this->setError('The server sent deflated data. Your php install must have the Zlib extension compiled in to support this.');
				}
			}
		}
		
		if(strlen($data) == 0){
			$this->debug('no data after headers!');
			$this->setError('no data present after HTTP headers');
			return false;
		}
		$this->debug('end of send()');
		return $data;
	}


	/**
	* send the SOAP message via HTTPS 1.0 using CURL
	*
	* @param    string $msg message data
	* @param    integer $timeout set timeout in seconds
	* @return	string data
	* @access   public
	*/
	function sendHTTPS($data, $timeout=0) {
	   	//global $t;
		//$t->setMarker('inside sendHTTPS()');
		$this->debug('entered sendHTTPS() with data of length: '.strlen($data));
		// init CURL
		$ch = curl_init();
		//$t->setMarker('got curl handle');
		// set proxy
		if($this->proxyhost && $this->proxyport){
			$host = $this->proxyhost;
			$port = $this->proxyport;
		} else {
			$host = $this->host;
			$port = $this->port;
		}
		// set url
		$hostURL = ($port != '') ? "https://$host:$port" : "https://$host";
		// add path
		$hostURL .= $this->path;
		curl_setopt($ch, CURLOPT_URL, $hostURL);
		// set other options
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// encode
		if(function_exists('gzinflate')){
			curl_setopt($ch, CURLOPT_ENCODING, 'deflate');
		}
		// persistent connection
		//curl_setopt($ch, CURL_HTTP_VERSION_1_1, true);
		
		// set timeout
		if($timeout != 0){
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}
		
		$credentials = '';
		if($this->username != '') {
			$credentials = 'Authorization: Basic '.base64_encode("$this->username:$this->password").'\r\n';
		}
		
		if($this->encoding != ''){
			if(function_exists('gzdeflate')){
				$encoding_headers = "Accept-Encoding: $this->encoding\r\n".
				"Connection: close\r\n";
				set_magic_quotes_runtime(0);
			}
		}
		
		if($this->proxyhost && $this->proxyport){
			$this->outgoing_payload = "POST $this->url HTTP/$this->protocol_version\r\n";
		} else {
			$this->outgoing_payload = "POST $this->path HTTP/$this->protocol_version\r\n";
		}
		
		$this->outgoing_payload .=
			"User-Agent: $this->title v$this->version\r\n".
			"Host: ".$this->host."\r\n".
			$encoding_headers.
			$credentials.
			"Content-Type: text/xml; charset=\"$this->soap_defencoding\"\r\n".
			"Content-Length: ".strlen($data)."\r\n".
			"SOAPAction: \"$this->soapaction\""."\r\n\r\n".
			$data;

		// set payload
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);
		//$t->setMarker('set curl options, executing...');
		// send and receive
		$this->incoming_payload = curl_exec($ch);
		//$t->setMarker('executed transfer');
		$data = $this->incoming_payload;

        $cErr = curl_error($ch);

		if($cErr != ''){
        	$err = 'cURL ERROR: '.curl_errno($ch).': '.$cErr.'<br>';
			foreach(curl_getinfo($ch) as $k => $v){
				$err .= "$k: $v<br>";
			}
			$this->setError($err);
			curl_close($ch);
	    	return false;
		} else {
			//echo '<pre>';
			//var_dump(curl_getinfo($ch));
			//echo '</pre>';
		}
		// close curl
		curl_close($ch);
		//$t->setMarker('closed curl');
		
		// remove 100 header
		if(ereg('^HTTP/1.1 100',$data)){
			if($pos = strpos($data,"\r\n\r\n") ){
				$data = ltrim(substr($data,$pos));
			} elseif($pos = strpos($data,"\n\n") ){
				$data = ltrim(substr($data,$pos));
			}
		}//
		
		// separate content from HTTP headers
		if( $pos = strpos($data,"\r\n\r\n") ){
			$lb = "\r\n";
		} elseif( $pos = strpos($data,"\n\n") ){
			$lb = "\n";
		} else {
			$this->setError('no proper separation of headers and document');
			return false;
		}
		$header_data = trim(substr($data,0,$pos));
		$header_array = explode($lb,$header_data);
		$data = ltrim(substr($data,$pos));
		$this->debug('found proper separation of headers and document');
		$this->debug('cleaned data, stringlen: '.strlen($data));
		// clean headers
		foreach($header_array as $header_line){
			$arr = explode(':',$header_line);
			$headers[trim($arr[0])] = trim($arr[1]);
		}
		if(strlen($data) == 0){
			$this->debug('no data after headers!');
			$this->setError('no data present after HTTP headers.');
			return false;
		}
		
		// decode transfer-encoding
		if($headers['Transfer-Encoding'] == 'chunked'){
			if(!$data = $this->decodeChunked($data)){
				$this->setError('Decoding of chunked data failed');
				return false;
			}
		}
		// decode content-encoding
		if($headers['Content-Encoding'] != ''){
			if($headers['Content-Encoding'] == 'deflate' || $headers['Content-Encoding'] == 'gzip'){
    			// if decoding works, use it. else assume data wasn't gzencoded
    			if(function_exists('gzinflate')){
					if($headers['Content-Encoding'] == 'deflate' && $degzdata = @gzinflate($data)){
    					$data = $degzdata;
					} elseif($headers['Content-Encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10))){
						$data = $degzdata;
					} else {
						$this->setError('Errors occurred when trying to decode the data');
					}
    			} else {
					$this->setError('The server sent deflated data. Your php install must have the Zlib extension compiled in to support this.');
				}
			}
		}
		// set decoded payload
		$this->incoming_payload = $header_data."\r\n\r\n".$data;
		return $data;
	}
	
	/**
	* if authenticating, set user credentials here
	*
	* @param    string $user
	* @param    string $pass
	* @access   public
	*/
	function setCredentials($username, $password) {
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	* set the soapaction value
	*
	* @param    string $soapaction
	* @access   public
	*/
	function setSOAPAction($soapaction) {
		$this->soapaction = $soapaction;
	}
	
	/**
	* use http encoding
	*
	* @param    string $enc encoding style. supported values: gzip, deflate, or both
	* @access   public
	*/
	function setEncoding($enc='gzip, deflate'){
		$this->encoding = $enc;
		$this->protocol_version = '1.1';
	}
	
	/**
	* set proxy info here
	*
	* @param    string $proxyhost
	* @param    string $proxyport
	* @access   public
	*/
	function setProxy($proxyhost, $proxyport) {
		$this->proxyhost = $proxyhost;
		$this->proxyport = $proxyport;
	}
	
	/**
	* decode a string that is encoded w/ "chunked' transfer encoding
 	* as defined in RFC2068 19.4.6
	*
	* @param    string $buffer
	* @returns	string
	* @access   public
	*/
	function decodeChunked($buffer){
		// length := 0
		$length = 0;
		$new = '';
		
		// read chunk-size, chunk-extension (if any) and CRLF
		// get the position of the linebreak
		$chunkend = strpos($buffer,"\r\n") + 2;
		$temp = substr($buffer,0,$chunkend);
		$chunk_size = hexdec( trim($temp) );
		$chunkstart = $chunkend;
		// while (chunk-size > 0) {
		while ($chunk_size > 0) {
			
			$chunkend = strpos( $buffer, "\r\n", $chunkstart + $chunk_size);
		  	
			// Just in case we got a broken connection
		  	if ($chunkend == FALSE) {
		  	    $chunk = substr($buffer,$chunkstart);
				// append chunk-data to entity-body
		    	$new .= $chunk;
		  	    $length += strlen($chunk);
		  	    break;
			}
			
		  	// read chunk-data and CRLF
		  	$chunk = substr($buffer,$chunkstart,$chunkend-$chunkstart);
		  	// append chunk-data to entity-body
		  	$new .= $chunk;
		  	// length := length + chunk-size
		  	$length += strlen($chunk);
		  	// read chunk-size and CRLF
		  	$chunkstart = $chunkend + 2;
			
		  	$chunkend = strpos($buffer,"\r\n",$chunkstart)+2;
			if ($chunkend == FALSE) {
				break; //Just in case we got a broken connection
			}
			$temp = substr($buffer,$chunkstart,$chunkend-$chunkstart);
			$chunk_size = hexdec( trim($temp) );
			$chunkstart = $chunkend;
		}
        // Update headers
        //$this->Header['content-length'] = $length;
        //unset($this->Header['transfer-encoding']);
		return $new;
	}
	
}



?>