<?php
/**
 * Created by PhpStorm.
 * User: Michal
 * Date: 25/02/2015
 * Time: 15:04
 */

class query {

    private $query;          // parsed SQL query
    private $input;          // parsed XML
    private $output;         // applied SQL on parsed XML


    function __construct () {

    }

    function __destruct () {

    }


    public function applyQuery () {

        $this->applyFrom();         // apply FROM Command

        $this->applySelect();       // apply SELECT Command

        if (isset($this->query['LIMIT']))
            $this->applyLimit();    // apply LIMIT Command

        if (isset($this->query['WHERE']))
            $this->applyWhere();    // apply WHERE Command

    }


    private function applyFrom () {         // apply SQL FROM, result stored in $output
        if (empty($this->query['FROM'])) {
            $this->output = $this->input;
            return;
        }
        $this->digDataByFrom($this->input, $this->query['FROM']);
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


    private function applySelect () {       // apply SQL SELECT, result stored in $output
        $this->input = $this->output;           // sets newly parsed input

        $this->output = NULL;

        $this->digDataBySelect($this->input,$this->query['SELECT']);
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


    private function applyLimit () {    // apply SQL LIMIT Command

        $this->input = $this->output;

        $this->output = NULL;

        foreach ($this->input as $key)
            if ($this->query['LIMIT']--)
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

        foreach ($this->query['WHERE'] as $index)
            if (isset($input[$index]))
                foreach ($input[$index] as $value)
                    if ($value === $this->query['CONTAINS'])
                        $result = true;
                    elseif ($result != true)
                        $result = false;

        return $result;
    }



    public function parseQuery ($query) {   // parse Query and sets individual elements

        $query = explode(" ", trim($query));

        $count = count($query) - 1;       // counts elements of query

        for ($i = 0, $rule = 0; $i < $count; ++$rule)
            switch ($rule) {
                case 0 :
                    if ($query[$i] == 'SELECT')
                        $this->query['SELECT'] = $this->getSelect($query[++$i]);
                    else
                        throw new Exception('wrong position of SELECT Command');
                    ++$i;
                    break;

                case 1 :
                    if ($query[$i] == 'LIMIT') {
                        $this->query['LIMIT'] = $this->getLimit($query[++$i]);
                        ++$i;
                    }
                    break;

                case 2 :
                    if ($query[$i] == 'FROM')
                       $this->query['FROM'] = $this->getFrom($query[++$i]);
                    else
                        throw new Exception('wrong position of FROM Command');
                    ++$i;
                    break;


                case 3 :
                    if ($query[$i] == 'WHERE') {
                         $this->query['WHERE'] = $this->getWhere($query[++$i]);
                        ++$i;
                    }
                    break;

                case 4 :
                    if ($query[$i] == 'CONTAINS') {
                         $this->query['CONTAINS'] = $this->getContains($query[++$i]);
                        ++$i;
                    }

                    break;

                default :

                    throw new Exception('Unknown query command' . $this->query[$i]);

                    break;
            }
    }


    private function getSelect ($element) {     // set select element
        return $element;
    }

    private function getLimit ($element) {      // sets limit element
        if (!ctype_digit($element))
            throw new Exception('SQL LIMIT command must be NUMERIC and INTEGER');

        return intval($element);
    }

    private function getFrom ($element) {       // sets from element
        if ($element != 'ROOT')
            return $element;

        return NULL;
    }

    private function getWhere ($element) {      // set select element
        return array_reverse(explode ('.', $element));
    }

    private function getContains ($element) {   // set contains element
        if (($element[0] != "\"") && ($element[strlen($element)-1] != "\""))
            throw new Exception('CONTAINS Element must be string');

        return trim($element, "\"");
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