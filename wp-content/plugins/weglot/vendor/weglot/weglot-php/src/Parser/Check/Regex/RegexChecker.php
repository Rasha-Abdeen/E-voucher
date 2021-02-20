<?php
/**
 * @author Remy Berda
 * User: remy
 * Date: 12/06/2019
 * Time: 16:52
 */

namespace Weglot\Parser\Check\Regex;

use Weglot\Parser\Parser;


/**
 * Class RegexChecker
 * @package Weglot\Parser\Check
 */
class RegexChecker
{
    /**
     * DOM node to match
     *
     * @var string
     */
    public $regex = '';

    /**
     * DOM node to match
     *
     * @var string
     */
    public $type = '';


    public $var_number = 1;


    /**
     * DOM node to match
     *
     * @var string
     */
    public $keys = '';



    /**
     * DomChecker constructor.
     * @param Parser $parser
     */
    public function __construct($regex = '' , $type = '' , $var_number = 0 , $keys = array())
    {
        $this->regex        = $regex;
        $this->type         =  $type;
        $this->var_number   = $var_number;
        $this->keys         = $keys;
    }


    /**
     * @return array
     */

    public function toArray()
    {
        return [
            $this->regex,
            $this->type,
            $this->var_number,
            $this->keys
        ];
    }
}