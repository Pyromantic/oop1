<?php
/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 25/02/2015
 * Time: 15:04
 */

require 'xmlParser.php';                // XML Parser Class


class query {

    private $query;          // SQL query
    private $xmlParser;      // XML parser

    private $select;         // select Element
    private $limit;          // limit Element
    private $from;           // from Element
    private $where;          // where Element


    function __construct () {
        $this->xmlParser = new xmlParser();     // allocates new XML parser
    }

    function __destruct () {

    }


    public function applyQuery () {

        $parser = &$this->xmlParser;

        $parser->parseXml();            // starts parsing given XML





    }

    public function parseQuery ($query) {            // parse Query and sets individual elements

        $query = explode(" ", $query);

        $count = count($query) - 1;       // counts elements of query

        $rule = 0;

        for ($i = 0; $i < $count; ++$i)
            switch ($query[$i]) {
                case 'SELECT' :
                    if ($i == 0)
                        $this->setSelect($query[++$i]);
                    else
                        throw new Exception('select expected');
                    ++$rule;
                    break;

                case 'LIMIT' :



                    break;

                case 'FROM' :

                   $this->setFrom($query[++$i]);



                    break;


                case 'WHERE' :



                    break;

                default :

                    throw new Exception('Unknown querry command' . $this->query[$i]);

                    break;
            }


    }

    private function getSelect ($element) {     // implements select
        if ($this->select != $element)
            return true;
        else
            return false;
    }

    private function getLimit ($element) {      // implements limit


    }

    private function  getFrom ($element) {      // implements from
        if ($this->from != $element)
            return true;
        else
            return false;
    }

    private function getWhere ($element) {      // implements where
        if ($this->where != $element)
            return true;
        else
            return false;
    }



    private function setSelect ($element) {     // set select element
        $this->select = $element;
    }

    private function setLimit ($element) {
        $this->from = $element;
    }

    private function setFrom ($element) {
        $this->from = $element;
    }

    private function setWhere ($element) {      // set select element
        $this->where = $element;
    }
    // setters functions

    public function setXmlParser (&$parser) {       // sets XML parser
        $this->xmlParser = $parser;
    }
}