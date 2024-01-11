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
        $xmlId = $node->attributes('xml', true)->id;
        $trimmedXmlId = trim(strval($xmlId));
        if (!empty($trimmedXmlId)) {
            $result['@xml:id'] = $trimmedXmlId;
        }

        // Parse child nodes
        foreach ($node->children() as $childNode) {
            $childName = $childNode->getName();
            $childData = $parseNode($childNode);

            // Always represent child nodes as an array
            if (!isset($result[$childName])) {
                $result[$childName] = [];
            }
            $result[$childName][] = $childData;
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
