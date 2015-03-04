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
        if ($this->query['FROM'] === 'ROOT') {
            $this->output = $this->input;
            return;
        }
        $this->digDataByFrom($this->input, $this->query['FROM']);
    }

    private function digDataByFrom ($input) {    //  iterates through given array
        if (!is_array($input))
            return;

        foreach($input as $tag)
            if (isset($tag[$this->query['FROM']])) {
                $this->output[] = $tag;
                return;
            } else $this->digDataByFrom($tag);

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

        foreach ($this->query['WHERE'] as $actual) {

            $negation = $actual['NOT'];             // get negation

            unset($actual['NOT']);                  // unset NOT element

            $this->input = $this->output;           // "reload" output
            $this->output = NULL;

            if ($negation) {
                foreach ($this->input as $tag)          // iterate through actual tag
                    if (!$this->digDataByWhere($tag, $actual))
                        $this->output[] = $tag;
            } else {
                foreach ($this->input as $tag)          // iterate through actual tag
                    if ($this->digDataByWhere($tag, $actual))
                        $this->output[] = $tag;
            }
        }
    }

    private function digDataByWhere ($input, $actual) {  //  iterates through given array

        $found = false;

        foreach ($input as $tag)
            if ($this->whereCondition($tag, $actual))    // apply where condition on actual tag
                return true;
            else {
                if (!is_array($tag))
                    return false;
                $found = $this->digDataByWhere($tag, $actual);
            }
        return $found;
    }

    private function whereCondition ($input, $actual) {
        if (!is_array($input))
            return false;

        $result = false;

        $operator = end($actual);

        $condition =  function ($value) use ($operator) {       // evaluates condition
            return eval('return ' . '"' .
                $value . '"'. $operator . '"' . $this->query['CONTAINS']
                . '"' . ';');
        };

        foreach (array_slice($actual, 0, -1) as $index)
            if (isset($input[$index])) {
                foreach ($input[$index] as $value)
                    if ($condition($value))
                        $result = true;
            }

        return $result;
    }



    public function parseQuery ($query) {   // parse Query and sets individual elements

        $query = explode(" ", trim($query));

        $count = count($query);                     // counts elements of query

        $i = 0;                                     // index of query field

        $inc = function () use (&$i, $count) {      // increments index of query filed
            return ($i == $count) ? die('SQL query error') : ++$i;
        };

        for ($rule = 0; $i < $count; ++$rule)
            switch ($rule) {
                case 0 && $query[$i] == 'SELECT' :

                    $this->query['SELECT'] = $query[$inc()];
                    $inc();

                    break;

                case 1 && $query[$i] == 'LIMIT' :

                        $getLimit = function ($element) {       // checks if limit is integer
                            if (!ctype_digit($element))
                                throw new Exception('SQL LIMIT command must be NUMERIC and INTEGER');
                            return intval($element);
                        };

                        $this->query['LIMIT'] = $getLimit ($query[$inc()]);
                        $inc();

                    break;

                case 2 && $query[$i] == 'FROM' :

                       $this->query['FROM'] = $query[$inc()];

                    ++$i;
                    break;


                case 3 && $query[$i] == 'WHERE' :

                        $inc();

                        $negation = false;

                        while ($query[$i] == 'NOT') {
                            $negation = !$negation;
                            $inc();
                        }

                        $condition = '==';

                        $getWhere = function ($element) {   // returns array of sought elements / attributes
                            return array_reverse(explode ('.', $element));
                        };

                        $this->query['WHERE'][] = $getWhere ($query[$i]);

                        $actual = count($this->query['WHERE']) - 1;

                        array_push($this->query['WHERE'][$actual], $condition);
                        $this->query['WHERE'][$actual]['NOT'] = $negation;

                        $inc();

                    break;

                case 4 && $query[$i] == 'CONTAINS':

                        $getContains = function ($element) {
                            if (($element[0] != "\"") && ($element[strlen($element)-1] != "\""))
                                throw new Exception('CONTAINS Element must be string');
                            return trim($element, "\"");
                        };

                         $this->query['CONTAINS'] = $getContains ($query[$inc()]);
                        $inc();

                    break;

                default :

                    throw new Exception('Unknown query command' . $this->query[$i]);

                    break;
            }
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