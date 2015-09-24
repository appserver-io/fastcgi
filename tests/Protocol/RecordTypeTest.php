<?php
namespace Crunch\FastCGI\Protocol;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @coversDefaultClass \Crunch\FastCGI\Protocol\RecordType
 * @covers \Crunch\FastCGI\Protocol\RecordType
 */
class RecordTypeTest extends TestCase
{
    /**
     * @covers ::instance
     * @covers ::__construct
     * @dataProvider getValidRecordTypes
     * @param int $recordTypeId
     */
    public function testInstanciateRecordType($recordTypeId)
    {
        $recordType = RecordType::instance($recordTypeId);

        self::assertEquals($recordTypeId, $recordType->value());
    }

    /**
     * @covers ::instance
     * @covers ::__construct
     * @dataProvider getValidRecordTypes
     * @param int $recordTypeId
     */
    public function testInstancesAreIdentical($recordTypeId)
    {
        $recordType1 = RecordType::instance($recordTypeId);
        $recordType2 = RecordType::instance($recordTypeId);

        self::assertSame($recordType1, $recordType2);
    }

    /**
     * Data provider: Valid role IDs
     *
     * The records consists solely of the IDs of valid role IDs both as
     * raw int and fetched from the constant.
     *
     * @return array
     */
    public static function getValidRecordTypes()
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5],
            [6],
            [7],
            [8],
            [9],
            [10],
            [11],
            [RecordType::BEGIN_REQUEST],
            [RecordType::ABORT_REQUEST],
            [RecordType::END_REQUEST],
            [RecordType::PARAMS],
            [RecordType::STDIN],
            [RecordType::STDOUT],
            [RecordType::STDERR],
            [RecordType::DATA],
            [RecordType::GET_VALUES],
            [RecordType::GET_VALUES_RESULT],
            [RecordType::UNKNOWN_TYPE],
            [RecordType::MAXTYPE],
        ];
    }

    /**
     * @covers ::instance
     * @covers ::__construct
     * @dataProvider getUnknownRecordTypeIds
     * @param int $recordTypeId
     */
    public function testUnknownRecordTypeIds($recordTypeId)
    {
        $recordType = RecordType::instance($recordTypeId);

        self::assertSame(RecordType::instance(RecordType::UNKNOWN_TYPE), $recordType);
    }

    /**
     * Data provider: Invalid role IDs
     *
     * The records consists of values, that are considered as invalid as
     * role id.
     *
     * @return array
     */
    public static function getUnknownRecordTypeIds()
    {
        return [
            [0],
            [-42],
            [42],
        ];
    }
    /**
     * @covers ::instance
     * @covers ::__construct
     * @dataProvider getInvalidRoles
     * @param int $roleId
     */
    public function testInvalidRoleIds($roleId)
    {
        $this->setExpectedException('\InvalidArgumentException');

        RecordType::instance($roleId);
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
        ];
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfBeginRequest()
    {
        $recordType = RecordType::instance(RecordType::BEGIN_REQUEST);

        self::assertTrue($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfAbotRequest()
    {
        $recordType = RecordType::instance(RecordType::ABORT_REQUEST);

        self::assertFalse($recordType->isBeginRequest());
        self::assertTrue($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfEndRequest()
    {
        $recordType = RecordType::instance(RecordType::END_REQUEST);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertTrue($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfParams()
    {
        $recordType = RecordType::instance(RecordType::PARAMS);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertTrue($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfStdin()
    {
        $recordType = RecordType::instance(RecordType::STDIN);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertTrue($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfStdout()
    {
        $recordType = RecordType::instance(RecordType::STDOUT);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertTrue($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfStderr()
    {
        $recordType = RecordType::instance(RecordType::STDERR);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertTrue($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfData()
    {
        $recordType = RecordType::instance(RecordType::DATA);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertTrue($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfGetValues()
    {
        $recordType = RecordType::instance(RecordType::GET_VALUES);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertTrue($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfGetValuesResult()
    {
        $recordType = RecordType::instance(RecordType::GET_VALUES_RESULT);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertTrue($recordType->isGetValuesResult());
        self::assertFalse($recordType->isUnknownType());
        self::assertFalse($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfUnknownType()
    {
        $recordType = RecordType::instance(RecordType::UNKNOWN_TYPE);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertTrue($recordType->isUnknownType());
        self::assertTrue($recordType->isMaxtype());
    }

    /**
     * @covers ::isBeginRequest
     * @covers ::isAbortRequest
     * @covers ::isEndRequest
     * @covers ::isParams
     * @covers ::isStdin
     * @covers ::isStdout
     * @covers ::isStderr
     * @covers ::isData
     * @covers ::isGetValues
     * @covers ::isGetValuesResult
     * @covers ::isUnknownType
     * @covers ::isMaxtype
     */
    public function testIsserOfMaxtype()
    {
        $recordType = RecordType::instance(RecordType::MAXTYPE);

        self::assertFalse($recordType->isBeginRequest());
        self::assertFalse($recordType->isAbortRequest());
        self::assertFalse($recordType->isEndRequest());
        self::assertFalse($recordType->isParams());
        self::assertFalse($recordType->isStdin());
        self::assertFalse($recordType->isStdout());
        self::assertFalse($recordType->isStderr());
        self::assertFalse($recordType->isData());
        self::assertFalse($recordType->isGetValues());
        self::assertFalse($recordType->isGetValuesResult());
        self::assertTrue($recordType->isUnknownType());
        self::assertTrue($recordType->isMaxtype());
    }

    /**
     * @covers ::beginRequest
     */
    public function testDirectInstancationMethodOfBeginRequest()
    {
        $expectedRecordType = RecordType::instance(RecordType::BEGIN_REQUEST);

        $recordType = RecordType::beginRequest();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::abortRequest
     */
    public function testDirectInstancationMethodOfAbortRequest()
    {
        $expectedRecordType = RecordType::instance(RecordType::ABORT_REQUEST);

        $recordType = RecordType::abortRequest();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::endRequest
     */
    public function testDirectInstancationMethodOfEndRequest()
    {
        $expectedRecordType = RecordType::instance(RecordType::END_REQUEST);

        $recordType = RecordType::endRequest();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::params
     */
    public function testDirectInstancationMethodOfParams()
    {
        $expectedRecordType = RecordType::instance(RecordType::PARAMS);

        $recordType = RecordType::params();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::stdin
     */
    public function testDirectInstancationMethodOfStdin()
    {
        $expectedRecordType = RecordType::instance(RecordType::STDIN);

        $recordType = RecordType::stdin();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::stdout
     */
    public function testDirectInstancationMethodOfStdout()
    {
        $expectedRecordType = RecordType::instance(RecordType::STDOUT);

        $recordType = RecordType::stdout();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::stderr
     */
    public function testDirectInstancationMethodOfStderr()
    {
        $expectedRecordType = RecordType::instance(RecordType::STDERR);

        $recordType = RecordType::stderr();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::data
     */
    public function testDirectInstancationMethodOfData()
    {
        $expectedRecordType = RecordType::instance(RecordType::DATA);

        $recordType = RecordType::data();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::getValues
     */
    public function testDirectInstancationMethodOfGetValues()
    {
        $expectedRecordType = RecordType::instance(RecordType::GET_VALUES);

        $recordType = RecordType::getValues();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::getValuesResult
     */
    public function testDirectInstancationMethodOfGetValuesResult()
    {
        $expectedRecordType = RecordType::instance(RecordType::GET_VALUES_RESULT);

        $recordType = RecordType::getValuesResult();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::unknownType
     */
    public function testDirectInstancationMethodOfUnknownType()
    {
        $expectedRecordType = RecordType::instance(RecordType::UNKNOWN_TYPE);

        $recordType = RecordType::unknownType();

        self::assertSame($expectedRecordType, $recordType);
    }

    /**
     * @covers ::maxtype
     */
    public function testDirectInstancationMethodOfMaxtype()
    {
        $expectedRecordType = RecordType::instance(RecordType::MAXTYPE);

        $recordType = RecordType::maxtype();

        self::assertSame($expectedRecordType, $recordType);
    }
}
