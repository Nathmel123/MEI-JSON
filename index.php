<!DOCTYPE html><html><head><meta charset="utf-8"></meta></head><body>

<?php

function meiXmlToJson($meiXmlString) {

    //Check if config file is valid
    $xmlDom = new DOMDocument();
    $xmlDom->load("config.xml");
    if(!$xmlDom->validate()) {
        die("Aborted - please provie a valid config file");
    }
    // Load config file
    $config = simplexml_load_file("config.xml");
    // Load MEI-XML string into SimpleXMLElement
    $xml = simplexml_load_string($meiXmlString);

    if ($xml === false) {
        // Handle XML parsing error
        return json_encode(['error' => 'Invalid XML']);
    }
    global $filename; //To be removed, move file interactions to MeiXmlToJson
    // Get xmlid of root element and write it to filename
    $filename = trim(strval($xml->attributes('xml', true)->id)) . ".json";

    // Convert SimpleXMLElement to associative array
    $array = xmlToArray($xml, $config);

    // Convert array to JSON
    $json = json_encode($array, JSON_PRETTY_PRINT);

    return $json;
}

function xmlToArray(SimpleXMLElement $xml, SimpleXMLElement $config): array
{
    $parseNode = function (SimpleXMLElement $node, SimpleXMLElement $config) use (&$parseNode) {
        $result = [];
        // Parse attributes
        $attributes = $node->attributes();
        foreach ($attributes as $attrName => $attrValue) {
            $trimmedValue = trim(strval($attrValue));
            if (!empty($trimmedValue)) {
                $result['@attributes'][$attrName] = $trimmedValue;
            }
        }

        // Parse value
        $nodeValue = trim(strval($node));
        if (!empty($nodeValue)) {
            $result['@value'] = $nodeValue;
        }

        // Include xml:id attribute
        if($config->xmlId['include'] == 'true') {

            $xmlId = $node->attributes('xml', true)->id;
            $trimmedXmlId = trim(strval($xmlId));
            if (!empty($trimmedXmlId)) {
            $result['@xml:id'] = $trimmedXmlId;
            }

        }


        // Check if node is a mixed-content element
        
        if($config->literalString['include'] == 'true') {
            if($node->getName() == "p") {    
                if($node->count() > 0 && !empty($node)) {
                    // Add literal string, to store the node order
                    $literal = str_replace(array("\n","\r"),'',trim($node->asXML()));
                    $result['@literal'] = $literal;
                }
            }
        }
        
        // Parse child nodes
        foreach($node->children() as $childNode) {
            $childName = $childNode->getName();
            $xmlString =  $childNode->asXML();
            foreach(readSplitSymbols($config) as $symbol) {
                $symbol = "/".$symbol."$/";
                if(preg_match($symbol,$xmlString)){
                    writeChildTree($childNode, $config);
                    $result["@link"][$node->attributes('xml', true)->id];
                    return $result;
                }
            }
            $childData = $parseNode($childNode, $config);

            // Always parse child nodes as array
            if (!isset($result[$childName])) {

                $result[$childName] = [];
            }
            $result[$childName][] = $childData;
        }

        return $result;
    };

    return [$xml->getName() => $parseNode($xml, $config)];
}

// Helper function to split and parse child tree
function writeChildTree(SimpleXMLElement $xml, SimpleXMLElement $config) {

    $filename = $xml->atrributes('xml',true)->id . ".json";
    $array = xmlToArray($xml, $config);
    $file = fopen($filename, "w");
    fwrite($file, json_encode($array, JSON_PRETTY_PRINT));
    fclose($file);
    
}

function readSplitSymbols($config) : array{

    $result = array();
    $splitSymbols = $config->splitSymbols->children();
    
    foreach($splitSymbols as $sym) {

        if(!empty($sym)) {
            array_push($result, $sym);
        }
        
    }

    return $result;
    
}   

// Example usage:
$filename;
$meiXmlString = file_get_contents('meitest2.xml');
$jsonResult = meiXmlToJson($meiXmlString);

$file = fopen($filename, "w");
if(!$file) {
	die("Could not open File.");
}

fwrite($file, $jsonResult);
fclose($file);

echo "Saved file";

?>
<p> ---Testwebsite--- </p>
</body>
</html>
