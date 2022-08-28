<?php

namespace SimpleConfig\Parser;

abstract class BasicParser
{
    protected $file, $fileName, $extension, $dir;

    public function __construct($file)
    {
        $info = pathinfo($file);

        $this->file = $file;
        $this->fileName = $info['filename'];
        $this->extension = $info['extension'];
    }

    public function fileName()
    {
        return $this->fileName;
    }

    public function extension()
    {
        return $this->extension;
    }
}
