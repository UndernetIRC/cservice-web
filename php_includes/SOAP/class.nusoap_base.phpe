<?php

/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

If you have any questions or comments, please email:

Dietrich Ayala
dietrich@ganx4.com
http://dietrich.ganx4.com/nusoap

NuSphere Corporation
http://www.nusphere.com

*/

// make errors handle properly in windows (thx, thong@xmethods.com)
//error_reporting(2039);
//error_reporting(E_ALL);

// load classes

// necessary classes
require_once('class.soapclient.php');
require_once('class.soap_val.php');
require_once('class.soap_parser.php');
require_once('class.soap_fault.php');

// transport classes
require_once('class.soap_transport_http.php');

// optional add-on classes
require_once('class.xmlschema.php');
require_once('class.wsdl.php');

// server class
require_once('class.soap_server.php');//

/**
*
* nusoap_base
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  v 0.6.3
* @access   public
*/
class nusoap_base {

	var $title = 'NuSOAP';
	var $version = '0.6.3';
	var $error_str = false;
    var $debug_str = '';
	// toggles automatic encoding of special characters
	var $charencoding = true;

    /**
	*  set schema version
	*
	* @var      XMLSchemaVersion
	* @access   public
	*/
	var $XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';
	
    /**
	*  set default encoding
	*
	* @var      soap_defencoding
	* @access   public
	*/
	//var $soap_defencoding = 'UTF-8';
    var $soap_defencoding = 'ISO-8859-1';

	/**
	*  load namespace uris into an array of uri => prefix
	*
	* @var      namespaces
	* @access   public
	*/
	var $namespaces = array(
		'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
		'xsd' => 'http://www.w3.org/2001/XMLSchema',
		'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
		'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/',
		'si' => 'http://soapinterop.org/xsd');
	/**
	* load types into typemap array
	* is this legacy yet?
	* no, this is used by the xmlschema class to verify type => namespace mappings.
	* @var      typemap
	* @access   public
	*/
	var $typemap = array(
	'http://www.w3.org/2001/XMLSchema' => array(
		'string'=>'string','boolean'=>'boolean','float'=>'double','double'=>'double','decimal'=>'double',
		'duration'=>'','dateTime'=>'string','time'=>'string','date'=>'string','gYearMonth'=>'',
		'gYear'=>'','gMonthDay'=>'','gDay'=>'','gMonth'=>'','hexBinary'=>'string','base64Binary'=>'string',
		// derived datatypes
		'normalizedString'=>'string','token'=>'string','language'=>'','NMTOKEN'=>'','NMTOKENS'=>'','Name'=>'','NCName'=>'','ID'=>'',
		'IDREF'=>'','IDREFS'=>'','ENTITY'=>'','ENTITIES'=>'','integer'=>'integer','nonPositiveInteger'=>'integer',
		'negativeInteger'=>'integer','long'=>'integer','int'=>'integer','short'=>'integer','byte'=>'integer','nonNegativeInteger'=>'integer',
		'unsignedLong'=>'','unsignedInt'=>'','unsignedShort'=>'','unsignedByte'=>'','positiveInteger'=>''),
	'http://www.w3.org/1999/XMLSchema' => array(
		'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
		'float'=>'double','dateTime'=>'string',
		'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
	'http://soapinterop.org/xsd' => array('SOAPStruct'=>'struct'),
	'http://schemas.xmlsoap.org/soap/encoding/' => array('base64'=>'string','array'=>'array','Array'=>'array'),
    'http://xml.apache.org/xml-soap' => array('Map')
	);

	/**
	*  entities to convert
	*
	* @var      xmlEntities
	* @access   public
	*/
	var $xmlEntities = array('quot' => '"','amp' => '&',
		'lt' => '<','gt' => '>','apos' => "'");

	/**
	* adds debug data to the class level debug string
	*
	* @param    string $string debug data
	* @access   private
	*/
	function debug($string){
		$this->debug_str .= get_class($this).": $string\n";
	}

	/**
	* returns error string if present
	*
	* @return   boolean $string error string
	* @access   public
	*/
	function getError(){
		if($this->error_str != ''){
			return $this->error_str;
		}
		return false;
	}

	/**
	* sets error string
	*
	* @return   boolean $string error string
	* @access   private
	*/
	function setError($str){
		$this->error_str = $str;
	}

	/**
	* serializes PHP values in accordance w/ section 5. Type information is
	* not serialized if $use == 'literal'.
	*
	* @return	string
    * @access	public
	*/
	function serialize_val($val,$name=false,$type=false,$name_ns=false,$type_ns=false,$attributes=false,$use='encoded'){
    	if(is_object($val) && get_class($val) == 'soapval'){
        	return $val->serialize($use);
        }
		$this->debug( "in serialize_val: $val, $name, $type, $name_ns, $type_ns, $attributes, $use");
		// if no name, use item
		$name = (!$name|| is_numeric($name)) ? 'soapVal' : $name;
		// if name has ns, add ns prefix to name
		$xmlns = '';
        if($name_ns){
			$prefix = 'nu'.rand(1000,9999);
			$name = $prefix.':'.$name;
			$xmlns .= " xmlns:$prefix=\"$name_ns\"";
		}
		// if type is prefixed, create type prefix
		if($type_ns != '' && $type_ns == $this->namespaces['xsd']){
			// need to fix this. shouldn't default to xsd if no ns specified
		    // w/o checking against typemap
			$type_prefix = 'xsd';
		} elseif($type_ns){
			$type_prefix = 'ns'.rand(1000,9999);
			$xmlns .= " xmlns:$type_prefix=\"$type_ns\"";
		}
		// serialize attributes if present
		if($attributes){
			foreach($attributes as $k => $v){
				$atts .= " $k=\"$v\"";
			}
		}
        // serialize if an xsd built-in primitive type
        if($type != '' && isset($this->typemap[$this->XMLSchemaVersion][$type])){
        	if ($use == 'literal') {
	        	return "<$name$xmlns>$val</$name>";
        	} else {
	        	return "<$name$xmlns xsi:type=\"xsd:$type\">$val</$name>";
        	}
        }
		// detect type and serialize
		$xml = '';
		$atts = '';
		switch(true) {
			case ($type == '' && is_null($val)):
				if ($use == 'literal') {
					// TODO: depends on nillable
					$xml .= "<$name$xmlns/>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:nil\"/>";
				}
				break;
			case (is_bool($val) || $type == 'boolean'):
				if(!$val){
			    	$val = 0;
				}
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:boolean\"$atts>$val</$name>";
				}
				break;
			case (is_int($val) || is_long($val) || $type == 'int'):
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:int\"$atts>$val</$name>";
				}
				break;
			case (is_float($val)|| is_double($val) || $type == 'float'):
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:float\"$atts>$val</$name>";
				}
				break;
			case (is_string($val) || $type == 'string'):
				if($this->charencoding){
			    	$val = htmlspecialchars($val, ENT_QUOTES);
			    }
				if ($use == 'literal') {
					$xml .= "<$name$xmlns $atts>$val</$name>";
				} else {
					$xml .= "<$name$xmlns xsi:type=\"xsd:string\"$atts>$val</$name>";
				}
				break;
			case is_object($val):
				$name = get_class($val);
				foreach(get_object_vars($val) as $k => $v){
					$pXml = isset($pXml) ? $pXml.$this->serialize_val($v,$k,false,false,false,false,$use) : $this->serialize_val($v,$k,false,false,false,false,$use);
				}
				$xml .= '<'.$name.'>'.$pXml.'</'.$name.'>';
				break;
			break;
			case (is_array($val) || $type):
				// detect if struct or array
                $keyList = array_keys($val);
				$valueType = 'arraySimple';
				foreach($keyList as $keyListValue){
					if(!is_int($keyListValue)){
						$valueType = 'arrayStruct';
						break;
					}
				}
                if($valueType=='arraySimple' || ereg('^ArrayOf',$type)){
					$i = 0;
					if(is_array($val) && count($val)> 0){
						foreach($val as $v){
	                    	if(is_object($v) && get_class($v) == 'soapval'){
	                        	$tt = $v->type;
	                        } else {
								$tt = gettype($v);
	                        }
							$array_types[$tt] = 1;
							$xml .= $this->serialize_val($v,'item',false,false,false,false,$use);
							if(is_array($v) && is_numeric(key($v))){
								$i += sizeof($v);
							} else {
								++$i;
							}
						}
						if(count($array_types) > 1){
							$array_typename = 'xsd:ur-type';
						} elseif(isset($tt) && isset($this->typemap[$this->XMLSchemaVersion][$tt])) {
							$array_typename = 'xsd:'.$tt;
						} elseif($tt == 'array' || $tt == 'Array'){
							$array_typename = 'SOAP-ENC:Array';
						} else {
							$array_typename = $tt;
						}
						if(isset($array_types['array'])){
							$array_type = $i.",".$i;
						} else {
							$array_type = $i;
						}
						if ($use == 'literal') {
							$xml = "<$name $atts>".$xml."</$name>";
						} else {
							$xml = "<$name xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"".$array_typename."[$array_type]\"$atts>".$xml."</$name>";
						}
					// empty array
					} else {
						if ($use == 'literal') {
							$xml = "<$name $atts>".$xml."</$name>";;
						} else {
							$xml = "<$name xsi:type=\"SOAP-ENC:Array\" $atts>".$xml."</$name>";;
						}
					}
				} else {
					// got a struct
					if(isset($type) && isset($type_prefix)){
						$type_str = " xsi:type=\"$type_prefix:$type\"";
					} else {
						$type_str = '';
					}
					if ($use == 'literal') {
						$xml .= "<$name$xmlns $atts>";
					} else {
						$xml .= "<$name$xmlns$type_str$atts>";
					}
					foreach($val as $k => $v){
						$xml .= $this->serialize_val($v,$k,false,false,false,false,$use);
					}
					$xml .= "</$name>";
				}
				break;
			default:
				$xml .= 'not detected, got '.gettype($val).' for '.$val;
				break;
		}
		return $xml;
	}

    /**
    * serialize message
    *
    * @param string body
    * @param string headers
    * @param array namespaces
    * @param string style
    * @return string message
    * @access public
    */
    function serializeEnvelope($body,$headers=false,$namespaces=array(),$style='rpc'){
	// serialize namespaces
    $ns_string = '';
	foreach(array_merge($this->namespaces,$namespaces) as $k => $v){
		$ns_string .= "  xmlns:$k=\"$v\"";
	}
	if($style == 'rpc') {
		$ns_string = ' SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"' . $ns_string;
	}

	// serialize headers
	if($headers){
		$headers = "<SOAP-ENV:Header>".$headers."</SOAP-ENV:Header>";
	}
	// serialize envelope
	return
	'<?xml version="1.0" encoding="'.$this->soap_defencoding .'"?'.">".
	'<SOAP-ENV:Envelope'.$ns_string.">".
	$headers.
	"<SOAP-ENV:Body>".
		$body.
	"</SOAP-ENV:Body>".
	"</SOAP-ENV:Envelope>";
    }

    function formatDump($str){
		$str = htmlspecialchars($str);
		return nl2br($str);
    }

    /**
    * returns the local part of a prefixed string
    * returns the original string, if not prefixed
    *
    * @param string
    * @return string
    * @access public
    */
	function getLocalPart($str){
		if($sstr = strrchr($str,':')){
			// get unqualified name
			return substr( $sstr, 1 );
		} else {
			return $str;
		}
	}

	/**
    * returns the prefix part of a prefixed string
    * returns false, if not prefixed
    *
    * @param string
    * @return mixed
    * @access public
    */
	function getPrefix($str){
		if($pos = strrpos($str,':')){
			// get prefix
			return substr($str,0,$pos);
		}
		return false;
	}

    function varDump($data) {
		ob_start();
		var_dump($data);
		$ret_val = ob_get_contents();
		ob_end_clean();
		return $ret_val;
	}
}

// XML Schema Datatype Helper Functions

//xsd:dateTime helpers

/**
* convert unix timestamp to ISO 8601 compliant date string
*
* @param    string $timestamp Unix time stamp
* @access   public
*/
function timestamp_to_iso8601($timestamp,$utc=true){
	$datestr = date('Y-m-d\TH:i:sO',$timestamp);
	if($utc){
		$eregStr =
		'([0-9]{4})-'.	// centuries & years CCYY-
		'([0-9]{2})-'.	// months MM-
		'([0-9]{2})'.	// days DD
		'T'.			// separator T
		'([0-9]{2}):'.	// hours hh:
		'([0-9]{2}):'.	// minutes mm:
		'([0-9]{2})(\.[0-9]*)?'. // seconds ss.ss...
		'(Z|[+\-][0-9]{2}:?[0-9]{2})?'; // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's

		if(ereg($eregStr,$datestr,$regs)){
			return sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ',$regs[1],$regs[2],$regs[3],$regs[4],$regs[5],$regs[6]);
		}
		return false;
	} else {
		return $datestr;
	}
}

/**
* convert ISO 8601 compliant date string to unix timestamp
*
* @param    string $datestr ISO 8601 compliant date string
* @access   public
*/
function iso8601_to_timestamp($datestr){
	$eregStr =
	'([0-9]{4})-'.	// centuries & years CCYY-
	'([0-9]{2})-'.	// months MM-
	'([0-9]{2})'.	// days DD
	'T'.			// separator T
	'([0-9]{2}):'.	// hours hh:
	'([0-9]{2}):'.	// minutes mm:
	'([0-9]{2})(\.[0-9]+)?'. // seconds ss.ss...
	'(Z|[+\-][0-9]{2}:?[0-9]{2})?'; // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's
	if(ereg($eregStr,$datestr,$regs)){
		// not utc
		if($regs[8] != 'Z'){
			$op = substr($regs[8],0,1);
			$h = substr($regs[8],1,2);
			$m = substr($regs[8],strlen($regs[8])-2,2);
			if($op == '-'){
				$regs[4] = $regs[4] + $h;
				$regs[5] = $regs[5] + $m;
			} elseif($op == '+'){
				$regs[4] = $regs[4] - $h;
				$regs[5] = $regs[5] - $m;
			}
		}
		return strtotime("$regs[1]-$regs[2]-$regs[3] $regs[4]:$regs[5]:$regs[6]Z");
	} else {
		return false;
	}
}



?>