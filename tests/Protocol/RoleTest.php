<?php
namespace Crunch\FastCGI\Protocol;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Protocol\Role
 * @covers \Crunch\FastCGI\Protocol\Role
 */
class RoleTest extends TestCase
{
    /**
     * @covers ::instance
     * @dataProvider getValidRoles
     * @param int $roleId
     */
    public function testInstanciateRole($roleId)
    {
        $role = Role::instance($roleId);

        self::assertEquals($roleId, $role->value());
    }

    /**
     * @covers ::instance
     * @dataProvider getValidRoles
     * @param int $roleId
     */
    public function testInstancesAreIdentical($roleId)
    {
        $role1 = Role::instance($roleId);
        $role2 = Role::instance($roleId);

        self::assertSame($role1, $role2);
    }

    /**
     * Data provider: Valid role IDs
     *
     * The records consists solely of the IDs of valid role IDs both as
     * raw int and fetched from the constant.
     *
     * @return array
     */
    public static function getValidRoles()
    {
        return [
            [1],
            [2],
            [3],
            [Role::RESPONDER],
            [Role::AUTHORIZER],
            [Role::FILTER]
        ];
    }

    /**
     * @covers ::instance
     * @dataProvider getInvalidRoles
     * @param int $roleId
     */
    public function testInvalidRoleIds($roleId)
    {
        $this->setExpectedException('\InvalidArgumentException');

        Role::instance($roleId);
    }

    /**
     * Data provider: Invalid role IDs
     *
     * The records consists of values, that are considered as invalid as
     * role id.
     *
     * @return array
     */
    public static function getInvalidRoles()
    {
        return [
            [null],
            [''],
            [0],
            [-42],
            [42]
        ];
    }

    /**
     * @covers ::isResponder
     * @covers ::isAuthorizer
     * @covers ::isFilter
     */
    public function testIsserOfResponder()
    {
        $role = Role::instance(Role::RESPONDER);

        self::assertTrue($role->isResponder());
        self::assertFalse($role->isAuthorizer());
        self::assertFalse($role->isFilter());
    }

    /**
     * @covers ::isResponder
     * @covers ::isAuthorizer
     * @covers ::isFilter
     */
    public function testIsserOfAuthorizer()
    {
        $role = Role::instance(Role::AUTHORIZER);

        self::assertFalse($role->isResponder());
        self::assertTrue($role->isAuthorizer());
        self::assertFalse($role->isFilter());
    }

    /**
     * @covers ::isResponder
     * @covers ::isAuthorizer
     * @covers ::isFilter
     */
    public function testIsserOfFilter()
    {
        $role = Role::instance(Role::FILTER);

        self::assertFalse($role->isResponder());
        self::assertFalse($role->isAuthorizer());
        self::assertTrue($role->isFilter());
    }

    /**
     * @covers ::responder
     */
    public function testDirectInstanciationMethodOfResponder()
    {
        $expectedRole = Role::instance(Role::RESPONDER);

        $role = Role::responder();

        self::assertSame($expectedRole, $role);
    }

    /**
     * @covers ::authorizer
     */
    public function testDirectInstanciationMethodOfAuthorizer()
    {
        $expectedRole = Role::instance(Role::AUTHORIZER);

        $role = Role::authorizer();

        self::assertSame($expectedRole, $role);
    }

    /**
     * @covers ::filter
     */
    public function testDirectInstanciationMethodOfFilter()
    {
        $expectedRole = Role::instance(Role::FILTER);

        $role = Role::filter();

        self::assertSame($expectedRole, $role);
    }
}
