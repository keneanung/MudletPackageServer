<?php

namespace MudletPackageServer\Classes\Propel;

use MudletPackageServer\Classes\Propel\Base\Package as BasePackage;

/**
 * Skeleton subclass for representing a row from the 'package' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Package extends BasePackage
{
    public function toArray()
    {
        return array(
            "name"        => $this->name,
            "version"     => $this->version,
            "description" => $this->description,
            "author"      => $this->author,
            "extension"   => $this->extension
        );
    }
}
