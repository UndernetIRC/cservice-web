<?php

class FlashMessage {

    var $cssClass = 'flash';
    var $messageTemplate = "<div class=\"%s %s\">%s</div>\n";
    var $messageTypes = array('info', 'error');
    
    public function __construct() {
        if (!array_key_exists('flash_message', $_SESSION)) {
            $_SESSION['flash_message'] = array();
        }
    }

    public function message($message, $type="info") {
        if (!isset($_SESSION['flash_message']) || !isset($message)) {
            return false;
        }

        $_SESSION['flash_message'][$type] = $message;

        return true;
    }

    public function show($type="any") {
        $output = '';
        if (!isset($_SESSION['flash_message'])) {
            return false;
        }

        if ($type == "any") {
            foreach ($this->messageTypes as $mType) {
                if (!empty($_SESSION['flash_message'][$mType])) {
                    $message = "<p>" . $_SESSION['flash_message'][$mType] . "</p>";
                    $output .= sprintf($this->messageTemplate, $this->cssClass, $mType, $message);
                }
            }
        } elseif (!empty($_SESSION['flash_message'][$type])) {
            $message = "<p>" . $_SESSION['flash_message'][$type] . "</p>";
            $output .= sprintf($this->messageTemplate, $this->cssClass, $type, $message);
        }
        
        if (!empty($output)) {
            $this->clear();
            return $output;
        }
        return false;
    }

    public function hasMessage($type="any") {
        if ($type == "any") {
            foreach ($this->messageTypes as $mType) {
                if (!empty($_SESSION['flash_message'][$mType])) {
                    return true;
                }
            }
        } elseif (array_search($type, $this->messageTypes))  {
            if (!empty($_SESSION['flash_message'][$type])) {
                return true;
            }
        }
        return false;
    }

    public function clear() {
        unset($_SESSION['flash_message']);
        return true;
    }

    public function __toString() {
        return $this->hasMessage();
    }
}

?>