<?php
namespace Crunch\FastCGI\Protocol;

/**
 * Type: Role
 */
class Role
{
    const RESPONDER = 1;
    const AUTHORIZER = 2;
    const FILTER = 3;

    /** @var int */
    private $role;

    /** @var Role[] */
    private static $instances = [];

    /**
     * @param int $role
     */
    private function __construct($role)
    {
        $this->role = $role;
    }

    /**
     * Returns the raw value
     *
     * @return int
     */
    public function value()
    {
        return $this->role;
    }

    /**
     * Returns an instance of the given role
     *
     * @throws \InvalidArgumentException
     * @param int $role
     *
     * @return Role
     */
    public static function instance($role)
    {
        if (!self::$instances) {
            self::$instances = [
                self::RESPONDER => new self(self::RESPONDER),
                self::AUTHORIZER => new self(self::AUTHORIZER),
                self::FILTER => new self(self::FILTER),
            ];
        }

        if (!array_key_exists($role, self::$instances)) {
            throw new \InvalidArgumentException("Invalid Role $role");
        }

        return self::$instances[$role];
    }

    /**
     * @return bool
     */
    public function isResponder()
    {
        return $this->role === self::RESPONDER;
    }

    /**
     * @return bool
     */
    public function isAuthorizer()
    {
        return $this->role === self::AUTHORIZER;
    }

    /**
     * @return bool
     */
    public function isFilter()
    {
        return $this->role === self::FILTER;
    }

    /**
     * @return Role
     */
    public static function responder()
    {
        return self::instance(self::RESPONDER);
    }

    /**
     * @return Role
     */
    public static function authorizer()
    {
        return self::instance(self::AUTHORIZER);
    }

    /**
     * @return Role
     */
    public static function filter()
    {
        return self::instance(self::FILTER);
    }
}
