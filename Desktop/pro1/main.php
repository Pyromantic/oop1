<?php
require 'arguments.php';              // Arguments Class
require 'xmlParser.php';              // XML Parser Class

try {
    $args = new arguments($argc);     // new args Class

    $args->argsProcess($argv, $argc); // process arguments

} catch (Exception $e) {
    die('Caught exception: ' . $e->getMessage() . "\n");
}

$xmlParser = new xmlParser();

$xmlParser->setQuery($args->getQuery());

$xmlParser->add2InputFront($args->getInput());

try {
    $xmlParser->parseXml();
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
