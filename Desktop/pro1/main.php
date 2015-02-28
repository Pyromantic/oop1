<?php
require 'arguments.php';                // Arguments Class
require 'xmlParser.php';                // XML Parser Class
require 'query.php';                    // query Class
require 'xmlFileGenerator.php';         // XML file generator Class

try {
    $args = new arguments($argc);       // new args Class

    $args->argsProcess($argv, $argc);   // process arguments

} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}

$xmlParser = new xmlParser();           // new XML parser

$xmlParser->add2InputFront($args->getInput());  // sets input for XML parser

try {
    $xmlParser->parseXml();               // parse XML
} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}

$query = new query();                   // new query applier

$query->setParsedXml($xmlParser->getParsedXml());   // sets parsed XML

try {
    $query->parseQuery($args->getQuery());    // sets query

    $query->applyQuery();                     // applies query
} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}

$xmlGenerator = new xmlFileGenerator($args->getOutput());   // new XML File Generator

$xmlGenerator->setXML($query->getOutputXML());  // sets parsed XML

try {
    $xmlGenerator->generateXML();              // generates XML file
} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}


/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 17/02/2015
 * Time: 13:38
 */
?>
