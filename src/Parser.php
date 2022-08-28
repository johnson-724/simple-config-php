<?php

namespace SimpleConfig;

use SimpleConfig\Parser\PhpArray;
use SimpleConfig\Exception\ParseException;
use SimpleConfig\Parser\ParserInterface;

class Parser
{
    public static function getParser($file) : ParserInterface
    {
        $info = pathinfo($file);

        switch ($info['extension']) {
            case 'php':
                return new PhpArray($file);
                break;

            default:
                throw new ParseException('wrong file ext');

                break;
        }
    }
}
