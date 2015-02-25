<?php
require 'arguments.php';                // Arguments Class
require 'query.php';                    // query Class

try {
    $args = new arguments($argc);       // new args Class

    $args->argsProcess($argv, $argc);   // process arguments

} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}

$xmlParser = new xmlParser();           // new XML parser

$xmlParser->add2InputFront($args->getInput());  // sets input for XML parser

$query = new query();                   // new query applier

$query->setXmlParser($xmlParser);       // sets XML parser to query applier

$query->parseQuery($args->getQuery());    // sets query

try {
    $query->applyQuery();               // applies query
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
