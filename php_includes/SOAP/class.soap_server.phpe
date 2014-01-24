<?php



/**
*
* soap_server allows the user to create a SOAP server
* that is capable of receiving messages and returning responses
*
* NOTE: WSDL functionality is experimental
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  v 0.6.3
* @access   public
*/
class soap_server extends nusoap_base {

	var $service = ''; // service name
    var $operations = array(); // assoc array of operations => opData
    var $responseHeaders = false;
	var $headers = '';
	var $request = '';
	var $charset_encoding = 'UTF-8';
	var $fault = false;
	var $result = 'successful';
	var $wsdl = false;
	var $externalWSDLURL = false;
    var $debug_flag = 0;
	
	/**
	* constructor
    * the optional parameter is a path to a WSDL file that you'd like to bind the server instance to.
	*
    * @param string $wsdl path or URL to a WSDL file
	* @access   public
	*/
	function soap_server($wsdl=false){

		// turn on debugging?
		global $debug;
		if(isset($debug)){
			$this->debug_flag = 1;
		}

		// wsdl
		if($wsdl){
			$this->wsdl = new wsdl($wsdl);
			$this->externalWSDLURL = $wsdl;
			if($err = $this->wsdl->getError()){
				die('WSDL ERROR: '.$err);
			}
		}
	}

	/**
	* processes request and returns response
	*
	* @param    string $data usually is the value of $HTTP_RAW_POST_DATA
	* @access   public
	*/
	function service($data){
		// print wsdl
		global $QUERY_STRING;
		if(isset($_SERVER['QUERY_STRING'])){
			$qs = $_SERVER['QUERY_STRING'];
		} elseif(isset($GLOBALS['QUERY_STRING'])){
			$qs = $GLOBALS['QUERY_STRING'];
		} elseif(isset($QUERY_STRING) && $QUERY_STRING != ''){
			$qs = $QUERY_STRING;
		}
		// gen wsdl
		if(isset($qs) && ereg('wsdl', $qs) ){
			if($this->externalWSDLURL){
				header('Location: '.$this->externalWSDLURL);
				exit();
			} else {
				header("Content-Type: text/xml\r\n");
				print $this->wsdl->serialize();
				exit();
			}
		}
		
		// print web interface
		if($data == '' && $this->wsdl){
			print $this->webDescription();
		} else {
			
			// $response is the serialized response message
			$response = $this->parse_request($data);
			$this->debug('server sending...');
			$payload = $response;
            // add debug data if in debug mode
			if(isset($this->debug_flag) && $this->debug_flag == 1){
            	$payload .= "<!--\n".str_replace('--','- -',$this->debug_str)."\n-->";
            }
			// print headers
			if($this->fault){
				$header[] = "HTTP/1.0 500 Internal Server Error\r\n";
				$header[] = "Status: 500 Internal Server Error\r\n";
			} else {
				$header[] = "Status: 200 OK\r\n";
			}
			$header[] = "Server: $this->title Server v$this->version\r\n";
			$header[] = "Connection: Close\r\n";
			$header[] = "Content-Type: text/xml; charset=$this->charset_encoding\r\n";
			$header[] = "Content-Length: ".strlen($payload)."\r\n\r\n";
			reset($header);
			foreach($header as $hdr){
				header($hdr);
			}
			$this->response = join("\r\n",$header).$payload;
			print $payload;
		}
	}

	/**
	* parses request and posts response
	*
	* @param    string $data XML string
	* @return	string XML response msg
	* @access   private
	*/
	function parse_request($data='') {
		$this->debug('entering parseRequest() on '.date('H:i Y-m-d'));
        $dump = '';
		// get headers
		if(function_exists('getallheaders')){
			$this->headers = getallheaders();
			foreach($this->headers as $k=>$v){
				$dump .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
			// get SOAPAction header
			if(isset($this->headers['SOAPAction'])){
				$this->SOAPAction = str_replace('"','',$this->headers['SOAPAction']);
			}
			// get the character encoding of the incoming request
			if(strpos($this->headers['Content-Type'],'=')){
				$enc = str_replace('"','',substr(strstr($this->headers["Content-Type"],'='),1));
				if(eregi('^(ISO-8859-1|US-ASCII|UTF-8)$',$enc)){
					$this->xml_encoding = $enc;
				} else {
					$this->xml_encoding = 'us-ascii';
				}
			}
			$this->debug('got encoding: '.$this->charset_encoding);
		} elseif(is_array($_SERVER)){
			$this->headers['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->SOAPAction = isset($_SERVER['SOAPAction']) ? $_SERVER['SOAPAction'] : '';
		}
		$this->request = $dump."\r\n\r\n".$data;
		// parse response, get soap parser obj
		$parser = new soap_parser($data,$this->charset_encoding);
		// if fault occurred during message parsing
		if($err = $parser->getError()){
			// parser debug
			$this->debug("parser debug: \n".$parser->debug_str);
			$this->result = 'fault: error in msg parsing: '.$err;
			$this->fault('Server',"error in msg parsing:\n".$err);
			// return soapresp
			return $this->fault->serialize();
		// else successfully parsed request into soapval object
		} else {
			// get/set methodname
			$this->methodname = $parser->root_struct_name;
			$this->debug('method name: '.$this->methodname);
			// does method exist?
			if(!function_exists($this->methodname)){
				// "method not found" fault here
				$this->debug("method '$this->methodname' not found!");
				$this->debug("parser debug: \n".$parser->debug_str);
				$this->result = 'fault: method not found';
				$this->fault('Server',"method '$this->methodname' not defined in service '$this->service'");
				return $this->fault->serialize();
			}
			if($this->wsdl){
				if(!$this->opData = $this->wsdl->getOperationData($this->methodname)){
				//if(
			    	$this->fault('Server',"Operation '$this->methodname' is not defined in the WSDL for this service");
					return $this->fault->serialize();
			    }
			}
			$this->debug("method '$this->methodname' exists");
			// evaluate message, getting back parameters
			$this->debug('calling parser->get_response()');
			$request_data = $parser->get_response();
			// parser debug
			$this->debug("parser debug: \n".$parser->debug_str);
			// verify that request parameters match the method's signature
			if($this->verify_method($this->methodname,$request_data)){
				// if there are parameters to pass
	            $this->debug('params var dump '.$this->varDump($request_data));
				if($request_data){
					$this->debug("calling '$this->methodname' with params");
					if (! function_exists('call_user_func_array')) {
						$this->debug('calling method using eval()');
						$funcCall = $this->methodname.'(';
						foreach($request_data as $param) {
							$funcCall .= "\"$param\",";
						}
						$funcCall = substr($funcCall, 0, -1).')';
						$this->debug('function call:<br>'.$funcCall);
						@eval("\$method_response = $funcCall;");
					} else {
						$this->debug('calling method using call_user_func_array()');
						$method_response = call_user_func_array("$this->methodname",$request_data);
					}
	                $this->debug('response var dump'.$this->varDump($method_response));
				} else {
					// call method w/ no parameters
					$this->debug("calling $this->methodname w/ no params");
					$m = $this->methodname;
					$method_response = @$m();
				}
				$this->debug("done calling method: $this->methodname, received $method_response of type".gettype($method_response));
				// if we got nothing back. this might be ok (echoVoid)
				if(isset($method_response) && $method_response != '' || is_bool($method_response)) {
					// if fault
					if(get_class($method_response) == 'soap_fault'){
						$this->debug('got a fault object from method');
						$this->fault = $method_response;
						return $method_response->serialize();
					// if return val is soapval object
					} elseif(get_class($method_response) == 'soapval'){
						$this->debug('got a soapval object from method');
						$return_val = $method_response->serialize();
					// returned other
					} else {
						$this->debug('got a(n) '.gettype($method_response).' from method');
						$this->debug('serializing return value');
						if($this->wsdl){
							if(sizeof($this->opData['output']['parts']) > 1){
						    	$opParams = $method_response;
						    } else {
						    	$opParams = array($method_response);
						    }
						    $return_val = $this->wsdl->serializeRPCParameters($this->methodname,'output',$opParams);
						} else {
						    $return_val = $this->serialize_val($method_response);
						}
					}
					$this->debug('return val:'.$this->varDump($return_val));
				} else {
					$return_val = '';
					$this->debug('got no response from method');
				}
				$this->debug('serializing response');
				$payload = '<'.$this->methodname."Response>".$return_val.'</'.$this->methodname."Response>";
				$this->result = 'successful';
				if($this->wsdl){
					//if($this->debug_flag){
	                	$this->debug("WSDL debug data:\n".$this->wsdl->debug_str);
	                //	}
					// Added: In case we use a WSDL, return a serialized env. WITH the usedNamespaces.
					return $this->serializeEnvelope($payload,$this->responseHeaders,$this->wsdl->usedNamespaces,$this->opData['style']);
				} else {
					return $this->serializeEnvelope($payload,$this->responseHeaders);
				}
			} else {
				// debug
				$this->debug('ERROR: request not verified against method signature');
				$this->result = 'fault: request failed validation against method signature';
				// return fault
				$this->fault('Server',"Operation '$this->methodname' not defined in service.");
				return $this->fault->serialize();
			}
		}
	}

	/**
	* takes the value that was created by parsing the request
	* and compares to the method's signature, if available.
	*
	* @param	mixed
	* @return	boolean
	* @access   private
	*/
	function verify_method($operation,$request){
		if(isset($this->wsdl) && is_object($this->wsdl)){
			if($this->wsdl->getOperationData($operation)){
				return true;
			}
	    } elseif(isset($this->operations[$operation])){
			return true;
		}
		return false;
	}

	/**
	* add a method to the dispatch map
	*
	* @param    string $methodname
	* @param    string $in array of input values
	* @param    string $out array of output values
	* @access   public
	*/
	function add_to_map($methodname,$in,$out){
			$this->operations[$methodname] = array('name' => $methodname,'in' => $in,'out' => $out);
	}

	/**
	* register a service with the server
	*
	* @param    string $methodname
	* @param    string $in assoc array of input values: key = param name, value = param type
	* @param    string $out assoc array of output values: key = param name, value = param type
	* @param	string $namespace
	* @param	string $soapaction
	* @param	string $style (rpc|literal)
	* @access   public
	*/
	function register($name,$in=false,$out=false,$namespace=false,$soapaction=false,$style=false,$use=false){
	    if(false == $in) {
		}
		if(false == $out) {
		}
		if(false == $namespace) {
		}
		if(false == $soapaction) {
			global $SERVER_NAME,$SERVER_PORT, $SCRIPT_NAME;
			if ($SERVER_PORT == "80")
			$soapaction = "http://$SERVER_NAME$SCRIPT_NAME";
			else
			  $soapaction = "http://$SERVER_NAME:$SERVER_PORT$SCRIPT_NAME";
		}
		if(false == $style) {
			$style = "rpc";
		}
		if(false == $use) {
			$use = "encoded";
		}
		
		$this->operations[$name] = array(
	    'name' => $name,
	    'in' => $in,
	    'out' => $out,
	    'namespace' => $namespace,
	    'soapaction' => $soapaction,
	    'style' => $style);
        if($this->wsdl){
        	$this->wsdl->addOperation($name,$in,$out,$namespace,$soapaction,$style,$use);
	    }
		return true;
	}

	/**
	* create a fault. this also acts as a flag to the server that a fault has occured.
	*
	* @param	string faultcode
	* @param	string faultactor
	* @param	string faultstring
	* @param	string faultdetail
	* @access   public
	*/
	function fault($faultcode,$faultactor,$faultstring='',$faultdetail=''){
		$this->fault = new soap_fault($faultcode,$faultactor,$faultstring,$faultdetail);
	}

    /**
    * prints html description of services
    *
    * @access private
    */
    function webDescription(){
		$b = '
		<html><head><title>NuSOAP: '.$this->wsdl->serviceName.'</title>
		<style type="text/css">
		    body    { font-family: arial; color: #000000; background-color: #ffffff; margin: 0px 0px 0px 0px; }
		    p       { font-family: arial; color: #000000; margin-top: 0px; margin-bottom: 12px; }
		    pre { background-color: silver; padding: 5px; font-family: Courier New; font-size: x-small; color: #000000;}
		    ul      { margin-top: 10px; margin-left: 20px; }
		    li      { list-style-type: none; margin-top: 10px; color: #000000; }
		    .content{
			margin-left: 0px; padding-bottom: 2em; }
		    .nav {
			padding-top: 10px; padding-bottom: 10px; padding-left: 15px; font-size: .70em;
			margin-top: 10px; margin-left: 0px; color: #000000;
			background-color: #ccccff; width: 20%; margin-left: 20px; margin-top: 20px; }
		    .title {
			font-family: arial; font-size: 26px; color: #ffffff;
			background-color: #999999; width: 105%; margin-left: 0px;
			padding-top: 10px; padding-bottom: 10px; padding-left: 15px;}
		    .hidden {
			position: absolute; visibility: hidden; z-index: 200; left: 250px; top: 100px;
			font-family: arial; overflow: hidden; width: 600;
			padding: 20px; font-size: 10px; background-color: #999999;
			layer-background-color:#FFFFFF; }
		    a,a:active  { color: charcoal; font-weight: bold; }
		    a:visited   { color: #666666; font-weight: bold; }
		    a:hover     { color: cc3300; font-weight: bold; }
		</style>
		<script language="JavaScript" type="text/javascript">
		<!--
		// POP-UP CAPTIONS...
		function lib_bwcheck(){ //Browsercheck (needed)
		    this.ver=navigator.appVersion
		    this.agent=navigator.userAgent
		    this.dom=document.getElementById?1:0
		    this.opera5=this.agent.indexOf("Opera 5")>-1
		    this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom && !this.opera5)?1:0;
		    this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom && !this.opera5)?1:0;
		    this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
		    this.ie=this.ie4||this.ie5||this.ie6
		    this.mac=this.agent.indexOf("Mac")>-1
		    this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0;
		    this.ns4=(document.layers && !this.dom)?1:0;
		    this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
		    return this
		}
		var bw = new lib_bwcheck()
		//Makes crossbrowser object.
		function makeObj(obj){
		    this.evnt=bw.dom? document.getElementById(obj):bw.ie4?document.all[obj]:bw.ns4?document.layers[obj]:0;
		    if(!this.evnt) return false
		    this.css=bw.dom||bw.ie4?this.evnt.style:bw.ns4?this.evnt:0;
		    this.wref=bw.dom||bw.ie4?this.evnt:bw.ns4?this.css.document:0;
		    this.writeIt=b_writeIt;
		    return this
		}
		// A unit of measure that will be added when setting the position of a layer.
		//var px = bw.ns4||window.opera?"":"px";
		function b_writeIt(text){
		    if (bw.ns4){this.wref.write(text);this.wref.close()}
		    else this.wref.innerHTML = text
		}
		//Shows the messages
		var oDesc;
		function popup(divid){
		    if(oDesc = new makeObj(divid)){
			oDesc.css.visibility = "visible"
		    }
		}
		function popout(){ // Hides message
		    if(oDesc) oDesc.css.visibility = "hidden"
		}
		//-->
		</script>
		</head>
		<body>
		<div class=content>
			<br><br>
			<div class=title>'.$this->wsdl->serviceName.'</div>
			<div class=nav>
				<p>View the <a href="'.$GLOBALS['PHP_SELF'].'?wsdl">WSDL</a> for the service.
				Click on an operation name to view it&apos;s details.</p>
				<ul>';
				foreach($this->wsdl->getOperations() as $op => $data){
				    $b .= "<li><a href='#' onclick=\"popup('$op')\">$op</a></li>";
				    // create hidden div
				    $b .= "<div id='$op' class='hidden'>
				    <a href='#' onclick='popout()'><font color='#ffffff'>Close</font></a><br><br>";
				    foreach($data as $donnie => $marie){
						if($donnie == 'input' || $donnie == 'output'){
						    $b .= "<font color='white'>".ucfirst($donnie).':</font><br>';
						    foreach($marie as $captain => $tenille){
							if($captain == 'parts'){
							    $b .= "&nbsp;&nbsp;$captain:<br>";
				                if(is_array($tenille)){
							    foreach($tenille as $joanie => $chachi){
									$b .= "&nbsp;&nbsp;&nbsp;&nbsp;$joanie: $chachi<br>";
							    }
				        		}
							} else {
							    $b .= "&nbsp;&nbsp;$captain: $tenille<br>";
							}
						    }
						} else {
						    $b .= "<font color='white'>".ucfirst($donnie).":</font> $marie<br>";
						}
				    }
					$b .= '</div>';
				}
				$b .= '
				<ul>
			</div>
		</div></body></html>';
		return $b;
    }

    /**
    * sets up wsdl object
    * this acts as a flag to enable internal WSDL generation
    * NOTE: NOT FUNCTIONAL
    *
    * @param string $serviceName, name of the service
    * @param string $namespace, tns namespace
    */
    function configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http')
    {
		$SERVER_NAME = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $GLOBALS['SERVER_NAME'];
		$SERVER_PORT = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : $GLOBALS['SERVER_PORT'];
		$SCRIPT_NAME = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $GLOBALS['SCRIPT_NAME'];
        if(false == $namespace) {
		if ($SERVERPORT == "80")
            $namespace = "http://$SERVER_NAME/soap/$serviceName";
		else
		    $namespace = "http://$SERVER_NAME:$SERVER_PORT/soap/$serviceName";
        }
        
        if(false == $endpoint) {
	    if ($SERVERPORT == "80")
            $endpoint = "http://$SERVER_NAME$SCRIPT_NAME";
	    else
	    	$endpoint = http://$SERVER_NAME:$SERVER_PORT$SCRIPT_NAME";
        }
        
		$this->wsdl = new wsdl;
		$this->wsdl->serviceName = $serviceName;
        $this->wsdl->endpoint = $endpoint;
		$this->wsdl->namespaces['tns'] = $namespace;
		$this->wsdl->namespaces['soap'] = 'http://schemas.xmlsoap.org/wsdl/soap/';
		$this->wsdl->namespaces['wsdl'] = 'http://schemas.xmlsoap.org/wsdl/';
        $this->wsdl->bindings[$serviceName.'Binding'] = array(
        	'name'=>$serviceName.'Binding',
            'style'=>$style,
            'transport'=>$transport,
            'portType'=>$serviceName.'PortType');
        $this->wsdl->ports[$serviceName.'Port'] = array(
        	'binding'=>$serviceName.'Binding',
            'location'=>$endpoint,
            'bindingType'=>'http://schemas.xmlsoap.org/wsdl/soap/');
    }
}



?>