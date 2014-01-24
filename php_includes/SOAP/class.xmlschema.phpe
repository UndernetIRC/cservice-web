<?php



/**
* parses an XML Schema, allows access to it's data, other utility methods
* no validation... yet.
* very experimental and limited. As is discussed on XML-DEV, I'm one of the people
* that just doesn't have time to read the spec(s) thoroughly, and just have a couple of trusty
* tutorials I refer to :)
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  v 0.6.3
* @access   public
*/
class XMLSchema extends nusoap_base  {
	
	// files
	var $schema = '';
	var $xml = '';
	// define internal arrays of bindings, ports, operations, messages, etc.
	var $complexTypes = array();
	// target namespace
	var $schemaTargetNamespace = '';
	// parser vars
	var $parser;
	var $position;
	var $depth = 0;
	var $depth_array = array();
    
	/**
	* constructor
	*
	* @param    string $schema schema document URI
	* @param    string $xml xml document URI
	* @access   public
	*/
	function XMLSchema($schema='',$xml=''){

		$this->debug('xmlschema class instantiated, inside constructor');
		// files
		$this->schema = $schema;
		$this->xml = $xml;

		// parse schema file
		if($schema != ''){
			$this->debug('initial schema file: '.$schema);
			$this->parseFile($schema);
		}

		// parse xml file
		if($xml != ''){
			$this->debug('initial xml file: '.$xml);
			$this->parseFile($xml);
		}

	}

    /**
    * parse an XML file
    *
    * @param string $xml, path/URL to XML file
    * @param string $type, (schema | xml)
	* @return boolean
    * @access public
    */
	function parseFile($xml,$type){
		// parse xml file
		if($xml != ""){
			$this->debug('parsing $xml');
			$xmlStr = @join("",@file($xml));
			if($xmlStr == ""){
				$this->setError('No file at the specified URL: '.$xml);
			return false;
			} else {
				$this->parseString($xmlStr,$type);
			return true;
			}
		}
		return false;
	}

	/**
	* parse an XML string
	*
	* @param    string $xml path or URL
    * @param string $type, (schema|xml)
	* @access   private
	*/
	function parseString($xml,$type){
		// parse xml string
		if($xml != ""){

	    	// Create an XML parser.
	    	$this->parser = xml_parser_create();
	    	// Set the options for parsing the XML data.
	    	xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);

	    	// Set the object for the parser.
	    	xml_set_object($this->parser, $this);

	    	// Set the element handlers for the parser.
			if($type == "schema"){
		    	xml_set_element_handler($this->parser, 'schemaStartElement','schemaEndElement');
		    	xml_set_character_data_handler($this->parser,'schemaCharacterData');
			} elseif($type == "xml"){
				xml_set_element_handler($this->parser, 'xmlStartElement"','xmlEndElement');
		    	xml_set_character_data_handler($this->parser,'xmlCharacterData');
			}

		    // Parse the XML file.
		    if(!xml_parse($this->parser,$xml,true)){
			// Display an error message.
				$errstr = sprintf('XML error on line %d: %s',
				xml_get_current_line_number($this->parser),
				xml_error_string(xml_get_error_code($this->parser))
				);
				$this->debug('XML parse error: '.$errstr);
				$this->setError('Parser error: '.$errstr);
	    	}
            
			xml_parser_free($this->parser);
		} else{
			$this->debug('no xml passed to parseString()!!');
			$this->setError('no xml passed to parseString()!!');
		}
	}

	/**
	* start-element handler
	*
	* @param    string $parser XML parser object
	* @param    string $name element name
	* @param    string $attrs associative array of attributes
	* @access   private
	*/
	function schemaStartElement($parser, $name, $attrs) {
		
		// position in the total number of elements, starting from 0
		$pos = $this->position++;
		$depth = $this->depth++;
		// set self as current value for this depth
		$this->depth_array[$depth] = $pos;

		// get element prefix
		if($prefix = $this->getPrefix($name)){
			// get unqualified name
			$name = $this->getLocalPart($name);
		} else {
        	$prefix = '';
        }
		
        // loop thru attributes, expanding, and registering namespace declarations
        if(count($attrs) > 0){
        	foreach($attrs as $k => $v){
                // if ns declarations, add to class level array of valid namespaces
				if(ereg("^xmlns",$k)){
                	//$this->xdebug("$k: $v");
                	//$this->xdebug('ns_prefix: '.$this->getPrefix($k));
                	if($ns_prefix = substr(strrchr($k,':'),1)){
						$this->namespaces[$ns_prefix] = $v;
					} else {
						$this->namespaces['ns'.(count($this->namespaces)+1)] = $v;
					}
					if($v == 'http://www.w3.org/2001/XMLSchema' || $v == 'http://www.w3.org/1999/XMLSchema'){
						$this->XMLSchemaVersion = $v;
						$this->namespaces['xsi'] = $v.'-instance';
					}
				}
                // expand each attribute
                $k = strpos($k,':') ? $this->expandQname($k) : $k;
                $v = strpos($v,':') ? $this->expandQname($v) : $v;
        		$eAttrs[$k] = $v;
        	}
        	$attrs = $eAttrs;
        } else {
        	$attrs = array();
        }
		// find status, register data
		switch($name){
			case ('all'|'choice'|'sequence'):
				//$this->complexTypes[$this->currentComplexType]['compositor'] = 'all';
				$this->complexTypes[$this->currentComplexType]['compositor'] = $name;
				if($name == 'all'){
					$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
				}
			break;
			case 'attribute':
            	//$this->xdebug("parsing attribute $attrs[name] $attrs[ref] of value: ".$attrs['http://schemas.xmlsoap.org/wsdl/:arrayType']);
                if(isset($attrs['name'])){
					$this->attributes[$attrs['name']] = $attrs;
					$aname = $attrs['name'];
				} elseif($attrs['ref']){
					$aname = $attrs['ref'];
                    $this->attributes[$attrs['ref']] = $attrs;
				}
                
				if(isset($this->currentComplexType)){
					$this->complexTypes[$this->currentComplexType]['attrs'][$aname] = $attrs;
				} elseif(isset($this->currentElement)){
					$this->elements[$this->currentElement]['attrs'][$aname] = $attrs;
				}
				// arrayType attribute
				if($this->getLocalPart($aname) == 'arrayType'){
                	$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					if(isset($attrs['http://schemas.xmlsoap.org/wsdl/:arrayType'])){
						$v = $attrs['http://schemas.xmlsoap.org/wsdl/:arrayType'];
					} else {
						$v = '';
					}
                    if(strpos($v,'[,]')){
                        $this->complexTypes[$this->currentComplexType]['multidimensional'] = true;
                    }
                    $v = substr($v,0,strpos($v,'[')); // clip the []
                    if(!strpos($v,':') && isset($this->typemap[$this->XMLSchemaVersion][$v])){
                        $v = $this->XMLSchemaVersion.':'.$v;
                    }
                    $this->complexTypes[$this->currentComplexType]['arrayType'] = $v;
				}
			break;
			case 'complexType':
				if(isset($attrs['name'])){
					$this->currentElement = false;
					$this->currentComplexType = $attrs['name'];
					$this->complexTypes[$this->currentComplexType] = $attrs;
					$this->complexTypes[$this->currentComplexType]['typeClass'] = 'complexType';
					if(isset($attrs['base']) && ereg(':Array$',$attrs['base'])){
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					} else {
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
					}
					$this->xdebug('processing complexType '.$attrs['name']);
				}
			break;
			case 'element':
				if(isset($attrs['type'])){
					$this->xdebug("processing element ".$attrs['name']);
					$this->currentElement = $attrs['name'];
					$this->elements[ $attrs['name'] ] = $attrs;
					$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
					$ename = $attrs['name'];
				} elseif(isset($attrs['ref'])){
					$ename = $attrs['ref'];
				} else {
					$this->xdebug('adding complexType '.$attrs['name']);
					$this->currentComplexType = $attrs['name'];
					$this->complexTypes[ $attrs['name'] ] = $attrs;
					$this->complexTypes[ $attrs['name'] ]['element'] = 1;
					$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
				}
				if(isset($ename) && $this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]['elements'][$ename] = $attrs;
				}
			break;
			case 'restriction':
				$this->xdebug("in restriction for ct: $this->currentComplexType and ce: $this->currentElement");
				if($this->currentElement){
					$this->elements[$this->currentElement]['type'] = $attrs['base'];
				} elseif($this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]['restrictionBase'] = $attrs['base'];
					if(strstr($attrs['base'],':') == ':Array'){
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					}
				}
			break;
			case 'schema':
				$this->schema = $attrs;
				$this->schema['schemaVersion'] = $this->getNamespaceFromPrefix($prefix);
			break;
			case 'simpleType':
				$this->currentElement = $attrs['name'];
				$this->elements[ $attrs['name'] ] = $attrs;
				$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
			break;
		}
	}

	/**
	* end-element handler
	*
	* @param    string $parser XML parser object
	* @param    string $name element name
	* @access   private
	*/
	function schemaEndElement($parser, $name) {
		// position of current element is equal to the last value left in depth_array for my depth
		if(isset($this->depth_array[$this->depth])){
        	$pos = $this->depth_array[$this->depth];
        }
		// bring depth down a notch
		$this->depth--;
		// move on...
		if($name == 'complexType'){
			$this->currentComplexType = false;
			$this->currentElement = false;
		}
		if($name == 'element'){
			$this->currentElement = false;
		}
	}

	/**
	* element content handler
	*
	* @param    string $parser XML parser object
	* @param    string $data element content
	* @access   private
	*/
	function schemaCharacterData($parser, $data){
		$pos = $this->depth_array[$this->depth];
		$this->message[$pos]['cdata'] .= $data;
	}

	/**
	* serialize the schema
	*
	* @access   public
	*/
	function serializeSchema(){

		$schemaPrefix = $this->getPrefixFromNamespace($this->XMLSchemaVersion);
		$xml = '';
		// complex types
		foreach($this->complexTypes as $typeName => $attrs){
			$contentStr = '';
			// serialize child elements
			if(count($attrs['elements']) > 0){
				foreach($attrs['elements'] as $element => $eParts){
					if(isset($eParts['ref'])){
						$contentStr .= "<element ref=\"$element\"/>";
					} else {
						$contentStr .= "<element name=\"$element\" type=\"$eParts[type]\"/>";
					}
				}
			}
			// attributes
			if(count($attrs['attrs']) >= 1){
				foreach($attrs['attrs'] as $attr => $aParts){
					$contentStr .= '<attribute ref="'.$aParts['ref'].'"';
					if(isset($aParts['wsdl:arrayType'])){
						$contentStr .= ' wsdl:arrayType="'.$aParts['wsdl:arrayType'].'"';
					}
					$contentStr .= '/>';
				}
			}
			// if restriction
			if( isset($attrs['restrictionBase']) && $attrs['restrictionBase'] != ''){
				$contentStr = "<$schemaPrefix:restriction base=\"".$attrs['restrictionBase']."\">".$contentStr."</$schemaPrefix:restriction>";
			}
			// "all" compositor obviates complex/simple content
			if(isset($attrs['compositor']) && $attrs['compositor'] == 'all'){
				$contentStr = "<$schemaPrefix:$attrs[compositor]>".$contentStr."</$schemaPrefix:$attrs[compositor]>";
			}
			// complex or simple content
			elseif( count($attrs['elements']) > 0 || count($attrs['attrs']) > 0){
				$contentStr = "<$schemaPrefix:complexContent>".$contentStr."</$schemaPrefix:complexContent>";
			}
			// compositors
			if(isset($attrs['compositor']) && $attrs['compositor'] != '' && $attrs['compositor'] != 'all'){
				$contentStr = "<$schemaPrefix:$attrs[compositor]>".$contentStr."</$schemaPrefix:$attrs[compositor]>";
			}
			// finalize complex type
			if($contentStr != ''){
				$contentStr = "<$schemaPrefix:complexType name=\"$typeName\">".$contentStr."</$schemaPrefix:complexType>";
			} else {
				$contentStr = "<$schemaPrefix:complexType name=\"$typeName\"/>";
			}
			$xml .= $contentStr;
		}
		// elements
		if(isset($this->elements) && count($this->elements) > 0){
			foreach($this->elements as $element => $eParts){
				$xml .= "<$schemaPrefix:element name=\"$element\" type=\"".$eParts['type']."\"/>";
			}
		}
		// attributes
		if(isset($this->attributes) && count($this->attributes) > 0){
			foreach($this->attributes as $attr => $aParts){
				$xml .= "<$schemaPrefix:attribute name=\"$attr\" type=\"".$aParts['type']."\"/>";
			}
		}
		// finish 'er up
		$xml = "<$schemaPrefix:schema xmlns=\"$this->XMLSchemaVersion\" targetNamespace=\"$this->schemaTargetNamespace\">".$xml."</$schemaPrefix:schema>";
		return $xml;
	}

	/**
	* expands a qualified name
	*
	* @param    string $string qname
	* @return	string expanded qname
	* @access   private
	*/
	function expandQname($qname){
		// get element prefix
		if(strpos($qname,':') && !ereg('^http://',$qname)){
			// get unqualified name
			$name = substr(strstr($qname,':'),1);
			// get ns prefix
			$prefix = substr($qname,0,strpos($qname,':'));
			if(isset($this->namespaces[$prefix])){
				return $this->namespaces[$prefix].':'.$name;
			} else {
				return $qname;
			}
		} else {
			return $qname;
		}
	}

	/**
	* adds debug data to the clas level debug string
	*
	* @param    string $string debug data
	* @access   private
	*/
	function xdebug($string){
		$this->debug(' xmlschema: '.$string);
	}

    /**
    * get the PHP type of a user defined type in the schema
    * PHP type is kind of a misnomer since it actually returns 'struct' for assoc. arrays
    * returns false if no type exists, or not w/ the given namespace
    * else returns a string that is either a native php type, or 'struct'
    *
    * @param string $type, name of defined type
    * @param string $ns, namespace of type
    * @return mixed
    * @access public
    */
	function getPHPType($type,$ns){
		global $typemap;
		if(isset($typemap[$ns][$type])){
			//print "found type '$type' and ns $ns in typemap<br>";
			return $typemap[$ns][$type];
		} elseif(isset($this->complexTypes[$type])){
			//print "getting type '$type' and ns $ns from complexTypes array<br>";
			return $this->complexTypes[$type]['phpType'];
		}
		return false;
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

	/**
    * pass it a prefix, it returns a namespace
	* returns false if no namespace registered with the given prefix
    *
    * @param string
    * @return mixed
    * @access public
    */
	function getNamespaceFromPrefix($prefix){
		if(isset($this->namespaces[$prefix])){
			return $this->namespaces[$prefix];
		}
		//$this->setError("No namespace registered for prefix '$prefix'");
		return false;
	}

	/**
    * returns the prefix for a given namespace (or prefix)
    * or false if no prefixes registered for the given namespace
    *
    * @param string
    * @return mixed
    * @access public
    */
	function getPrefixFromNamespace($ns){
		foreach($this->namespaces as $p => $n){
			if($ns == $n || $ns == $p){
			    $this->usedNamespaces[$p] = $n;
				return $p;
			}
		}
		return false;
	}

	/**
    * returns an array of information about a given type
    * returns false if no type exists by the given name
    *
	*	 typeDef = array(
	*	 'elements' => array(), // refs to elements array
	*	'restrictionBase' => '',
	*	'phpType' => '',
	*	'order' => '(sequence|all)',
	*	'attrs' => array() // refs to attributes array
	*	)
    *
    * @param string
    * @return mixed
    * @access public
    */
	function getTypeDef($type){
		if(isset($this->complexTypes[$type])){
			return $this->complexTypes[$type];
		} elseif(isset($this->elements[$type])){
			return $this->elements[$type];
		} elseif(isset($this->attributes[$type])){
			return $this->attributes[$type];
		}
		return false;
	}

	/**
    * returns a sample serialization of a given type, or false if no type by the given name
    *
    * @param string $type, name of type
    * @return mixed
    * @access public
    */
    function serializeTypeDef($type){
    	//print "in sTD() for type $type<br>";
	if($typeDef = $this->getTypeDef($type)){
		$str .= '<'.$type;
	    if(is_array($typeDef['attrs'])){
		foreach($attrs as $attName => $data){
		    $str .= " $attName=\"{type = ".$data['type']."}\"";
		}
	    }
	    $str .= " xmlns=\"".$this->schema['targetNamespace']."\"";
	    if(count($typeDef['elements']) > 0){
		$str .= ">";
		foreach($typeDef['elements'] as $element => $eData){
		    $str .= $this->serializeTypeDef($element);
		}
		$str .= "</$type>";
	    } elseif($typeDef['typeClass'] == 'element') {
		$str .= "></$type>";
	    } else {
		$str .= "/>";
	    }
			return $str;
	}
    	return false;
    }

    /**
    * returns HTML form elements that allow a user
    * to enter values for creating an instance of the given type.
    *
    * @param string $name, name for type instance
    * @param string $type, name of type
    * @return string
    * @access public
	*/
	function typeToForm($name,$type){
		// get typedef
		if($typeDef = $this->getTypeDef($type)){
			// if struct
			if($typeDef['phpType'] == 'struct'){
				$buffer .= '<table>';
				foreach($typeDef['elements'] as $child => $childDef){
					$buffer .= "
					<tr><td align='right'>$childDef[name] (type: ".$this->getLocalPart($childDef['type'])."):</td>
					<td><input type='text' name='parameters[".$name."][$childDef[name]]'></td></tr>";
				}
				$buffer .= '</table>';
			// if array
			} elseif($typeDef['phpType'] == 'array'){
				$buffer .= '<table>';
				for($i=0;$i < 3; $i++){
					$buffer .= "
					<tr><td align='right'>array item (type: $typeDef[arrayType]):</td>
					<td><input type='text' name='parameters[".$name."][]'></td></tr>";
				}
				$buffer .= '</table>';
			// if scalar
			} else {
				$buffer .= "<input type='text' name='parameters[$name]'>";
			}
		} else {
			$buffer .= "<input type='text' name='parameters[$name]'>";
		}
		return $buffer;
	}
	
	/**
	* adds an XML Schema complex type to the WSDL types
	* 
	* example: array
	* 
	* addType(
	* 	'ArrayOfstring',
	* 	'complexType',
	* 	'array',
	* 	'',
	* 	'SOAP-ENC:Array',
	* 	array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'string[]'),
	* 	'xsd:string'
	* );
	* 
	* example: PHP associative array ( SOAP Struct )
	* 
	* addType(
	* 	'SOAPStruct',
	* 	'complexType',
	* 	'struct',
	* 	'all',
	* 	array('myVar'=> array('name'=>'myVar','type'=>'string')
	* );
	* 
	* @param name
	* @param typeClass (complexType|simpleType|attribute)
	* @param phpType: currently supported are array and struct (php assoc array)
	* @param compositor (all|sequence|choice)
	* @param restrictionBase namespace:name (http://schemas.xmlsoap.org/soap/encoding/:Array)
	* @param elements = array ( name = array(name=>'',type=>'') )
	* @param attrs = array(
	* 	array(
	*		'ref' => "http://schemas.xmlsoap.org/soap/encoding/:arrayType",
	*		"http://schemas.xmlsoap.org/wsdl/:arrayType" => "string[]"
	* 	)
	* )
	* @param arrayType: namespace:name (http://www.w3.org/2001/XMLSchema:string)
	*
	*/
	function addComplexType($name,$typeClass='complexType',$phpType='array',$compositor='',$restrictionBase='',$elements=array(),$attrs=array(),$arrayType=''){
		$this->complexTypes[$name] = array(
	    'name'		=> $name,
	    'typeClass'	=> $typeClass,
	    'phpType'	=> $phpType,
		'compositor'=> $compositor,
	    'restrictionBase' => $restrictionBase,
		'elements'	=> $elements,
	    'attrs'		=> $attrs,
	    'arrayType'	=> $arrayType
		);
	}
}



?>