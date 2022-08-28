<?php

namespace SimpleConfig\Parser;

use SimpleConfig\Exception\ParseException;

class PhpArray extends BasicParser implements ParserInterface
{
    public function parse()
    {
        $data = include $this->file;

        if (!is_array($data)){
            throw new ParseException('parse error');
        }

        return $data;
    }
}
