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
        if ($this->query['FROM'] == 'ROOT') {
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

        foreach ($this->input as $item)
            if ($this->query['LIMIT']--)
                $this->output[] = $item;
            else
                break;
    }


    private function applyWhere () {    // apply SQL WHERE Command

        foreach ($this->query['WHERE'] as $sets) {
            $rejected = array();
            foreach ($sets as $actual) {

                $negation = $actual['NOT'];             // get negation

                unset($actual['NOT']);                  // unset NOT element

                $affection = $actual['AFFECTION'];      // nonfunctional, saved for later versions
                unset($actual['AFFECTION']);

                if ($affection == 'AND') {
                    $this->input = $this->output;
                    $this->output = NULL;
                    $rejected = NULL;
                } else {
                    $this->input = $rejected;
                }

                if ($negation) {
                    foreach ($this->input as $tag)      // iterate through actual tag
                        if (!$this->digDataByWhere($tag, $actual))
                            $this->output[] = $tag;
                        else
                            $rejected[] = $tag;
                } else {
                    foreach ($this->input as $tag)      // iterate through actual tag
                        if ($this->digDataByWhere($tag, $actual))
                            $this->output[] = $tag;
                        else
                            $rejected[] = $tag;
                }
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

        $condition =  function ($value) use ($actual) {       // evaluates condition
            $operator = end($actual);
            $key = key($actual);

            if ($key == 'CONTAINS') {                       // if CONTAINS
               if (strpos($value, $actual['CONTAINS']) !== false)
                   return true;
                else
                   return false;
            }

            return eval('return ' .
                    $value  . $key .  $operator
                     . ';');
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

        $affection = function ($element) use ($inc) {

            $inc();

            if ($element == 'OR' || $element == 'AND')
                return $element;

            throw new Exception('Neplatna podminka v SQL query');
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


                case 3 && $query[$i] == 'WHERE':
                case 4 && $query[$i] == 'AND' :
                case 4 && $query[$i] == 'OR' :


                    $defaultAffection = ($query[$i] != 'WHERE') ?  $query[$i] : 'AND' ;

                    $inc();

                    $negation = false;

                    while ($query[$i] == 'NOT') {                           // handles negation
                        $negation = !$negation;
                        $inc();
                    }

                    $actual = (isset($this->query['WHERE'])) ? count($this->query['WHERE']) : 0;  // set list

                    $bracket = false;

                    if ($query[$i][0] === '(') {
                        $query[$i] = substr($query[$i], 1);                  // cuts (
                        $bracket = true;
                    }

                    do {

                        $tmp = (isset($this->query['WHERE'][$actual])) ? $affection($query[$i]) : $defaultAffection;

                        $condition = $this->getCondition($inc, $query, $i, $bracket);   // sets condition

                        $condition['NOT'] = $negation;                                  // sets negation

                        $condition['AFFECTION'] = $tmp;

                        // sets affection, default AND

                        $this->query['WHERE'][$actual][] = $condition;

                        $inc();
                    } while ($bracket);


                    break;

                default :

                    throw new Exception('Unknown query command' . $this->query[$i]);

                    break;
            }

    }


    private function getCondition ($inc, $query, &$i, &$bracket) {

        $result = array_reverse(explode ('.', $query[$i]));  // sets first operand + attributes

        $inc();

        if ($query[$i] == 'CONTAINS') {
            $inc();
            if (($query[$i][0] != "\"") && ($query[$i][strlen($query[$i])-1] != "\""))
                throw new Exception('CONTAINS Element must be string');
            $result['CONTAINS'] = trim($query[$i], "\"");
            return $result;
        }

        $operand = NULL;

        if ($query[$i] == '<')
            $operand = $query[$i];

        if ($query[$i] == '>')
            $operand = $query[$i];

        if ($query[$i] == '=')
            $operand = $query[$i] . '=';

        if (empty($operand))
            throw new Exception ('SQL wrong operand');

        $inc();

        $value = NULL;

        if (($query[$i][0] != "\"") && ($query[$i][strlen($query[$i])-1] != "\""))
            $value = trim($query[$i], "\"");
        else
            $value = (ctype_digit($query[$i])) ? intval($query[$i]) : $query[$i];

        if ($value[strlen($value)-1] === ')') {
            $bracket = false;

            $value = substr($value, 0, -1);

            if (ctype_digit($value))
                $value =  intval($value);
        }

        $result[$operand] = $value;

        return $result;
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