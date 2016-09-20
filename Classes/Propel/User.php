<?php

namespace MudletPackageServer\Classes\Propel;

use MudletPackageServer\Classes\Propel\Base\User as BaseUser;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class User extends BaseUser implements UserInterface{

    public function __construct(){
        $verify_string = '';
        for ($i = 0; $i < 16; $i++) {
            $verify_string .= chr(mt_rand(32, 126));
        }
        $this->setVerifyString($verify_string);
    }

    /**
     * Creates a string that can be used as salt for the user.
     *
     * The salt is 50 characters long.
     *
     * @return string The salt.
     */
    private static function createSalt(){
        $salt = "";
        for ($i = 0; $i < 50; $i++) {
            $salt .= chr(mt_rand(33, 122));
        }
        return $salt;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Returns the salt for the user.
     *
     * The salt is created if it doesn't exist yet.
     *
     * @return string The users salt
     */
    public function getSalt(){
        $salt = parent::getSalt();
        if(null === $salt){
            $salt = self::createSalt();
            $this->setSalt($salt);
        }
        return $salt;
    }
}
