<?php

namespace MudletPackageServer\Classes;

class User {
    public $Name;
    public $EMail;

    function __construct($name)
    {
        $this->Name = $name;
    }

    public static function GetUser($name){
        return new User($name);
    }

    /**
     * @return string[string]
     */
    public function toArray()
    {
        return array(
            "name"  => $this->Name,
            "email" => $this->EMail,
        );
    }
} 