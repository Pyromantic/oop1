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
    private $output;         // applied SQL on parsed XML

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

        $this->applyLimit();    // apply LIMIT Command

        $this->applyWhere();    // apply Where Command

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

    private function applyLimit () {    // apply SQL LIMIT Command
        if (empty($this->limit))
            return;

        $tmp = $this->output;

        $this->output = NULL;

        foreach ($tmp as $key)
            if ($this->limit--)
                $this->output[] = $key;
            else
                break;
    }

    private function applyWhere () {

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


    public function parseQuery ($query) {   // parse Query and sets individual elements

        $query = explode(" ", trim($query));

        $count = count($query) - 1;       // counts elements of query

//        $query[$count] = substr($query[$count], 0, -1);

        for ($i = 0, $rule = 0; $i < $count; ++$rule)
            switch ($rule) {
                case 0 :
                    if ($query[$i] == 'SELECT')
                        $this->setSelect($query[++$i]);
                    else
                        throw new Exception('wrong position of SELECT Command');
                    ++$i;
                    break;

                case 1 :
                    if ($query[$i] == 'LIMIT') {
                        $this->setLimit($query[++$i]);
                        ++$i;
                    }
                    break;

                case 2 :
                    if ($query[$i] == 'FROM')
                        $this->setFrom($query[++$i]);
                    else
                        throw new Exception('wrong position of FROM Command');
                    ++$i;
                    break;


                case 3 :

                    if ($query[$i] == 'WHERE')
                        $this->setWhere($query[++$i]);

                    break;

                default :

                    throw new Exception('Unknown query command' . $this->query[$i]);

                    break;
            }
    }


    private function setSelect ($element) {     // set select element
        $this->select = $element;
    }

    private function setLimit ($element) {
        if (!ctype_digit($element))
            throw new Exception('SQL LIMIT command must be NUMERIC and INTEGER');

        $this->limit = intval($element);
    }

    private function setFrom ($element) {
        $this->from = $element;
    }

    private function setWhere ($element) {      // set select element
        $this->where = $element;
    }


    // public setters functions

    public function setParsedXml ($parsedXml) {       // sets XML parser
        $this->input = $parsedXml;
    }

    // public getters functions

    public function getOutputXML () {
        return $this->output;
    }
}