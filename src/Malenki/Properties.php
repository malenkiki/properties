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
     * TODO si le fichier n'existe pas, le créer !
     * @param $str_file Le nom du fichier à lire
     */
    public function __construct($str_file)
    {
        if(file_exists($str_file) && !is_dir($str_file))
        {
            $this->str_file = $str_file;
        }
    }



    /**
     * @brief Indique quel est le retour à la ligne utilisé
     * @param $str @c String indiquant le retour 
     * ("\r","\r\n" ou "\n")
     * @see setNewLineAsDOS(), setNewLineAsUNIX() et 
     * setNewLineAsMAC()
     */
    private function setNewLine($str)
    {
        $this->str_new_line = $str;
    }



    /**
     * @brief Indique qu'il faut utiliser et prendre en compte 
     * les retours à la
     * ligne de type DOS/Windows
     */
    public function setNewLineAsDOS()
    {
        $this->setNewLine("\r\n");
    }



    /**
     * @brief Indique qu'il faut utiliser et prendre en compte 
     * les retours à la
     * ligne de type UNIX
     */
    public function setNewLineAsUNIX()
    {
        $this->setNewLine("\n");
    }



    /**
     * @brief Indique qu'il faut utiliser et prendre en compte 
     * les retours à la
     * ligne de type MAC
     */
    public function setNewLineAsMAC()
    {
        $this->setNewLine("\r");
    }



    /**
     * @brief Utilisation de valeurs ayant des retours à la 
     * ligne en leur sein
     */
    public function multiLine()
    {
        $this->is_multiline = true;
    }



    /**
     * @brief Utilisation de valeurs sans retour à la ligne
     *
     * Si un retour à la ligne se présente, le reste de la 
     * valeur après le premier
     * retour à la ligne est ignoré.
     */
    public function singleLine()
    {
        $this->is_multiline = false;
    }



    /**
     * @brief Parcourt le fichier et stocke les valeurs trouvées
     * @return Boolean
     */
    public function read()
    {
        if(!is_null($this->str_file))
        {
            $content = file_get_contents($this->str_file);
            $content = explode($this->str_new_line,$content);

            // On parcourt le tableau qui contient les lignes...
            foreach($content as $line)
            {
                // Si on trouve un signe "égal", alors on s'attend à trouver 2 parties
                if(strpos($line,$this->str_separator) !== false)
                {
                    list($key,$value) = explode($this->str_separator,$line);
                    $key   = trim($key);
                    $value = trim($value);

                    // On vérifie que la clé est non nulle, si nulle, est ignorée
                    if(strlen($key) > 0)
                    {
                        $this->arr_key_value[$key] = $value;
                    }

                    // On stocke la dernière clé pour le cas des retours à la ligne dans
                    // les valeurs (cf. en-dessous)
                    $lastKey = $key;
                }
                else
                {
                    if($this->is_multiline)
                    {
                        // On est dans le cas où il n'y a pas de signe "égal"
                        $line = trim($line);

                        // Si la ligne contient quelquechose, et qu'au moins une clé est déjà
                        // stockée, alors on concataine cette ligne à la valeur de la clé
                        // précédente
                        if(strlen($line) > 0 and count($this->arr_key_value) > 0)
                        {
                            $this->arr_key_value[$lastKey] .= $this->str_new_line.$line;
                        }
                    }
                }
            }

            return true;
        }
        else
        {
            return false;
        }
    }



    /**
     * @brief Écrit les valeurs dans le fichier.
     */
    public function save()
    {
        if(!is_null($this->str_file))
        {
            $str = '';

            foreach($this->arr_key_value as $k => $v)
            {
                $str .= $k . $this->str_separator . $v . $this->str_new_line;
            }

            $result = file_put_contents($this->str_file, $str);

            if($result === false)
            {
                return false;
            }

            return true;
        } 
        else 
        {
            return false;
        }
    }



    /**
     * @brief Retourne les valeurs trouvées
     *
     * Les valeurs trouvées sont retournées sous la forme d'un 
     * tableau. S'il n'y a aucune valeur, retourne @c null
     *
     * @return mixed
     */
    public function getAllValues()
    {
        if(count($this->arr_key_value) == 0)
        {
            return null;
        }

        return $this->arr_key_value;
    }



    /**
     * @brief Retourne une valeur donnée.
     *
     * Si le tableau est vide ou que la clé n'existe pas, 
     * retourne @c false.
     */
    public function getValue($key)
    {
        if(!array_key_exists($key,$this->arr_key_value) || count($this->arr_key_value) == 0)
        {
            return false;
        }

        return $this->arr_key_value[$key];
    }



    /**
     * @brief Fournit une valeur à une clé.
     *
     * @param $key Nom de la clé
     * @param $value Valeur de la clé
     */
    public function setValue($key, $value)
    {
        $this->arr_key_value[$key] = $value;
        return true;
    }

}
