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
    private $contains;       // contains Element


    function __construct () {

    }

    function __destruct () {

    }


    public function applyQuery () {


        $this->applyFrom();     // apply FROM Command

        $this->applySelect();   // apply SELECT Command

        $this->applyLimit();    // apply LIMIT Command

        $this->applyWhere();    // apply WHERE Command

    }

    private function applyFrom () {         // apply SQL FROM, result stored in $output
        if (empty($this->from)) {
            $this->output = $this->input;
            return;
        }
        $this->digDataByFrom($this->input, $this->from);
    }

    private function applySelect () {       // apply SQL SELECT, result stored in $output

        $this->input = $this->output;           // sets newly parsed input

        $this->output = NULL;

        $this->digDataBySelect($this->input, $this->select);
    }

    private function applyLimit () {    // apply SQL LIMIT Command
        if (empty($this->limit))
            return;

        $this->input = $this->output;

        $this->output = NULL;

        foreach ($this->input as $key)
            if ($this->limit--)
                $this->output[] = $key;
            else
                break;
    }

    private function applyWhere () {    // apply SQL WHERE Command

        $this->input = $this->output;

        $this->output = NULL;

        foreach ($this->input as $tag)         // iterate through actual tag
           if ($this->digDataByWhere($tag))
               $this->output[] = $tag;

    }

    private function digDataByFrom ($input, $seek) {    //  iterates through given array
        if (!is_array($input))
            return;

        foreach($input as $tag) {
            if (isset($tag[$seek])) {
                $this->output[] = $tag;
                return;
            } else $this->digDataByFrom($tag, $seek);
        }

    }

    private function digDataBySelect ($input, $seek) {  //  iterates through given array
        if (!is_array($input))
            return;

        foreach($input as $tag) {
            if (isset($tag[$seek])) {
                $this->output[] = $tag;
            } else $this->digDataBySelect($tag, $seek);
        }

    }

    private function digDataByWhere ($input) {  //  iterates through given array
        if (!is_array($input))
            return false;

        $found = false;

        foreach ($input as $tag)
            if ($this->whereCondition($tag))     // apply where condition on actual tag
                return true;
            else
                $found = $this->digDataByWhere ($tag);

        return $found;
    }

    private function whereCondition ($input) {
        if (!is_array($input))
            return false;

        $result = false;

        foreach ($this->where as $index) {
            if (isset($input[$index]))
                foreach ($input[$index] as $value) {
                    if ($value === $this->contains)
                        $result = true;
                    elseif ($result != true)
                        $result = false;
                }
        }
        return $result;
    }

    public function parseQuery ($query) {   // parse Query and sets individual elements

        $query = explode(" ", trim($query));

        $count = count($query) - 1;       // counts elements of query

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
                    if ($query[$i] == 'WHERE') {
                        $this->setWhere($query[++$i]);
                        ++$i;
                    }
                    break;

                case 4 :
                    if ($query[$i] == 'CONTAINS') {
                        $this->setContains($query[++$i]);
                        ++$i;
                    }

                    break;

                default :

                    throw new Exception('Unknown query command' . $this->query[$i]);

                    break;
            }
    }


    private function setSelect ($element) {     // set select element
        $this->select = $element;
    }

    private function setLimit ($element) {      // sets limit element
        if (!ctype_digit($element))
            throw new Exception('SQL LIMIT command must be NUMERIC and INTEGER');

        $this->limit = intval($element);
    }

    private function setFrom ($element) {       // sets from element
        if ($element != 'ROOT')
            $this->from = $element;
    }

    private function setWhere ($element) {      // set select element
        $this->where = array_reverse(explode ('.', $element));
    }

    private function setContains ($element) {   // set contains element
        if (($element[0] != "\"") && ($element[strlen($element)-1] != "\""))
            throw new Exception('CONTAINS Element must be string');

        $element = trim($element, "\"");

        $this->contains = $element;
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