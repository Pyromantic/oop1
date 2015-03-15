<?php

//XQR:xhodan08

require 'arguments.php';                // Arguments Class
require 'xmlParser.php';                // XML Parser Class
require 'query.php';                    // query Class
require 'xmlFileGenerator.php';         // XML file generator Class

try {
    $args = new arguments($argc);               // new args Class

    $args->argsProcess($argv, $argc);           // process arguments

} catch (Exception $e) {
    die(1);
}

$xmlParser = new xmlParser();                   // new XML parser

$xmlParser->add2InputFront($args->getInput());  // sets input for XML parser

try {
    $xmlParser->parseXml();                     // parse XML
} catch (Exception $e) {
    die(4);
}

$query = new query();                           // new query applier

$query->setParsedXml($xmlParser->getParsedXml());   // sets parsed XML

try {
    $query->parseQuery($args->getQuery());      // sets query

    $query->applyQuery();                       // applies query
} catch (Exception $e) {
    error_log("Caught Exception: " . $e->getMessage());
    die(80);
}

$xmlGenerator = new xmlFileGenerator($args->getOutput());   // new XML File Generator

$xmlGenerator->setNFlag($args->getNFlag());     // sets N flag
$xmlGenerator->setFilename($args->getOutput()); // sets output filename
$xmlGenerator->setRoot($args->getRoot());       // sets root tag name

$xmlGenerator->setXML($query->getOutputXML());  // sets parsed XMLl


$xmlGenerator->generateXML();                   // generates XML file


exit;

/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 17/02/2015
 * Time: 13:38
 */

