<!DOCTYPE html><html><head><meta charset="utf-8"></meta></head><body>

<?php

function meiXmlToJson($meiXmlString) {
    // Load MEI-XML string into SimpleXMLElement
    $xml = simplexml_load_string($meiXmlString);

    if ($xml === false) {
        // Handle XML parsing error
        return json_encode(['error' => 'Invalid XML']);
    }

    // Convert SimpleXMLElement to associative array
    $array = xmlToArray($xml);

    // Convert array to JSON
    $json = json_encode($array, JSON_PRETTY_PRINT);

    return $json;
}

function xmlToArray(SimpleXMLElement $xml): array
{
    $parseNode = function (SimpleXMLElement $node) use (&$parseNode) {
        $result = [];
        
        // Parse attributes
        $attributes = $node->attributes();
        foreach ($attributes as $attrName => $attrValue) {
            $result['@attributes'][$attrName] = trim(strval($attrValue));
        }

        // Parse value
        $nodeValue = trim(strval($node));
        if (!empty($nodeValue)) {
            $result['@value'] = $nodeValue;
        }
        

        // Include xml:id attribute
        $xmlId = $node->attributes('xml', true)->id;
        if (!empty($xmlId)) {
            $result['@xml:id'] = trim(strval($xmlId));
        }
        
        // Parse child nodes

    /*
    *  Die Child Nodes werden erst dann zu einem Array, wenn das erste Element mit dem selben Namen
    *  bereits geparst wurde. Daher entstehen die Fehler. Klüger wäre es, zunächst den Namen
    *  und dann die Kindelemente zu parsen. => TODO
    */
        foreach ($node->children() as $childName => $childNode) {
            if (isset($result[$childName])) {
                // If multiple nodes with the same name, convert to an array
                $result[$childName] = array_merge((array)$result[$childName], [$parseNode($childNode)]);
            } else {
                $result[$childName] = $parseNode($childNode);
            }
        }

        return $result;
    };

    return [$xml->getName() => $parseNode($xml)];
}


// Example usage:
$meiXmlString = file_get_contents('meitest.xml');
$jsonResult = meiXmlToJson($meiXmlString);

$outputfilename = "output.json";

$file = fopen($outputfilename, "w");
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
