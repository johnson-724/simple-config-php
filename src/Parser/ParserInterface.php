<?php

namespace SimpleConfig\Parser;

interface ParserInterface
{
    public function parse();
    public function fileName();
    public function extension();
}
