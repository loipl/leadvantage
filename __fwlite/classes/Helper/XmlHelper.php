<?php

class Helper_XML {
    
    public static function removeXmlNamespace($xml) {
        try {
            $domdoc = new DOMDocument();
            $domdoc->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING);

            // remove the default namespace binding
            if (empty($domdoc->documentElement)) {
                return "";
            }
            $e = $domdoc->documentElement;  
            
            if (($e->getAttributeNode("xmlns"))) {
                $e->removeAttributeNS($e->getAttributeNode("xmlns")->nodeValue,"");
            }
            

            // hack hack, cough cough, hack hack
            return $domdoc->saveXML($domdoc);
        } catch (Exception $ex) {
            return "";
        }
    }
}
