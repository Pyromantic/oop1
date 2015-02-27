<?php
/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 25/02/2015
 * Time: 15:04
 */

class query {

    private $query;          // SQL query
    private $input;          // parsed XML
    private $output;

    private $select;         // select Element
    private $limit;          // limit Element
    private $from;           // from Element
    private $where;          // where Element


    function __construct () {

    }

    function __destruct () {

    }


    public function applyQuery () {

        //var_dump($this->input);

  // var_dump($database[$root][0]['library'][0]['room'][1]['book'][0]['author']);


        $this->applyFrom();     // apply FROM Command

        $this->applySelect();   // apply SELECT Command

    }

    private function applyFrom () {         // apply SQL FROM, result stored in $output
        foreach ($this->input as $tag) {
            if (isset($tag[$this->from])) {
                $this->output[] = $tag;
            } else $this->digData($tag, $this->from);
        }
    }

    private function applySelect () {       // apply SQL SELECT, result stored in $output

        $this->input = $this->output;           // sets newly parsed input

        $this->output = NULL;

        foreach ($this->input as $tag) {
            if (isset($tag[$this->select])) {
                $this->output[] = $tag[$this->select];
            } else $this->digData($tag, $this->select);
        }
    }

    private function digData ($input, $seek) {

        if (!is_array($input))
            return;

        foreach($input as $tag) {
            if (isset($tag[$seek])) {
                $this->output[] = $tag;
            } else $this->digData($tag, $seek);
        }

    }


    public function parseQuery ($query) {            // parse Query and sets individual elements

        $query = explode(" ", $query);

        $count = count($query) - 1;       // counts elements of query

        $query[$count] = substr($query[$count], 0, -1);

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

    private function getFrom ($element) {      // implements from
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

    public function setParsedXml ($parsedXml) {       // sets XML parser
        $this->input = $parsedXml;
    }

    // getters functions

    public function getOutputXML () {
        return $this->output;
    }
}