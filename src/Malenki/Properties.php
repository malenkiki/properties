<?php
/*
Copyright (c) 2013 Michel Petit <petit.michel@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */



namespace Malenki;


/**
 * Read, get, put and save data in Java's properties file.
 */
class Properties
{
    private $str_file      = null;
    private $arr_key_value = array();
    private $str_new_line  = "\n";
    private $str_separator = '=';
    private $is_multiline  = false;



    /**
     * Constructor, and sets file’s name.
     *
     * @param string $str_file File’s name as a string
     */
    public function __construct($str_file)
    {
        if(file_exists($str_file) && !is_dir($str_file))
        {
            $this->str_file = $str_file;
        }
    }



    /**
     * Set the break line style to use.
     *
     * @param string $str Break line string ("\r","\r\n" or "\n")
     * @see setNewLineAsDOS(), setNewLineAsUNIX() et 
     * setNewLineAsMAC()
     * @return Properties
     */
    private function setNewLine($str)
    {
        $this->str_new_line = $str;

        return $this;
    }



    /**
     * Set break line as Windwos style (\r\n)
     *
     * @return Properties
     */
    public function setNewLineAsDOS()
    {
        $this->setNewLine("\r\n");

        return $this;
    }



    /**
     * Use unix style break line.
     *
     * @return Properties
     */
    public function setNewLineAsUNIX()
    {
        $this->setNewLine("\n");

        return $this;
    }



    /**
     * Set break line as Mac style.
     *
     * @return Properties
     */
    public function setNewLineAsMAC()
    {
        $this->setNewLine("\r");

        return $this;
    }



    /**
     * Use multiline properties.
     *
     * @return Properties
     */
    public function multiLine()
    {
        $this->is_multiline = true;

        return $this;
    }



    /**
     * Use properties as single line only.
     *
     * If break line occurs, value part string after this break line will be 
     * ignore
     *
     * @return Properties
     */
    public function singleLine()
    {
        $this->is_multiline = false;

        return $this;
    }



    /**
     * Parse file and store value in memory.
     *
     * @return Properties
     */
    public function read()
    {
        $content = file_get_contents($this->str_file);
        $content = explode($this->str_new_line, $content);

        // Parsing table which contains lines
        foreach($content as $line)
        {
            // If equals sign found, then there are maybe two parts
            if(strpos($line, $this->str_separator) !== false)
            {
                list($key, $value) = explode($this->str_separator, $line);
                $key   = trim($key);
                $value = trim($value);

                // Checkes if key is null. If null, this key is skip
                if(strlen($key) > 0)
                {
                    $this->arr_key_value[$key] = $value;
                }

                // Store last key in case of break line into values.
                $lastKey = $key;
            }
            else
            {
                if($this->is_multiline)
                {
                    // No equal sign found case
                    $line = trim($line);

                    // If line has something and there is at least one key 
                    // stored, then this line is concatenate to the previous 
                    // key
                    if(strlen($line) > 0 && count($this->arr_key_value) > 0)
                    {
                        $this->arr_key_value[$lastKey] .= $this->str_new_line.$line;
                    }
                }
            }
        }

        return $this;
    }



    /**
     * Write values into file
     *
     * @return Properties
     */
    public function save()
    {
        $str = '';

        foreach($this->arr_key_value as $k => $v)
        {
            $str .= $k . $this->str_separator . $v . $this->str_new_line;
        }

        $result = file_put_contents($this->str_file, $str);

        if($result === false)
        {
            throw \RuntimeException('Cannot write file!');
        }

        return $this;

    }



    /**
     * Test if there is at least one property defined.
     * 
     * @access public
     * @return boolean
     */
    public function isVoid()
    {
        return count($this->arr_key_value) == 0;
    }


    /**
     * Return all found values.
     *
     * Found values are return as array.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->arr_key_value;
    }



    /**
     * Get one value by its name.
     *
     * If not exists, throw an Exception.
     *
     * @return mixed
     */
    public function get($key)
    {
        if(!array_key_exists($key,$this->arr_key_value) || count($this->arr_key_value) == 0)
        {
            throw new \Exception('This property does not exist!');
        }

        return $this->arr_key_value[$key];
    }



    /**
     * Set value
     *
     * @param string $key
     * @param mixed $value 
     * @return Property
     */
    public function set($key, $value)
    {
        $this->arr_key_value[$key] = $value;

        return $this;
    }

}
