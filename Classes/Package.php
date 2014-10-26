<?php

namespace MudletPackageServer\Classes;

class Package {

    public $Name;
    public $Version;
    public $Description;
    public $Author;
    public $Extension;
    public $Url;

    function __construct($databaseRow)
    {
        $this->Name = $databaseRow["name"];
        $this->Version = $databaseRow["version"];
        $this->Description = $databaseRow["description"];
        $this->Extension = $databaseRow["extension"];
        $this->Author = User::GetUser($databaseRow["author"]);
    }

    /**
     * @param \mysqli $con
     * @return \MudletPackageServer\Classes\Package[]
     */
    public static function GetPackages(\mysqli $con){
        $result = mysqli_query($con,"SELECT * FROM packages");
        $packages = array();
        while($row = mysqli_fetch_assoc($result)){
            $packages[] = new Package($row);
        }
        return $packages;
    }

    /**
     * @return string|array[string]
     */
    public function toArray()
    {
        return array(
            "name"        => $this->Name,
            "version"     => $this->Version,
            "description" => $this->Description,
            "extension"   => $this->Extension,
            "author"      => $this->Author->toArray(),
            "url"         => $this->Url,
        );
    }
} 