<?php
namespace Crunch\FastCGI;

class RecordType
{
    const BEGIN_REQUEST = 1;
    const ABORT_REQUEST = 2;
    const END_REQUEST = 3;
    const PARAMS = 4;
    const STDIN = 5;
    const STDOUT = 6;
    const STDERR = 7;
    const DATA = 8;
    const GET_VALUES = 9;
    const GET_VALUES_RESULT = 10;
    const UNKNOWN_TYPE = 11;
    const MAXTYPE = self::UNKNOWN_TYPE;

    private $type;

    private static $instances = [];

    private function __construct($type)
    {
        $this->type = $type;
    }

    public function value()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return RecordType
     */
    public static function instance($type)
    {
        if (!self::$instances) {
            self::$instances = [
                self::BEGIN_REQUEST => new self(self::BEGIN_REQUEST),
                self::ABORT_REQUEST => new self(self::ABORT_REQUEST),
                self::END_REQUEST => new self(self::END_REQUEST),
                self::PARAMS => new self(self::PARAMS),
                self::STDIN => new self(self::STDIN),
                self::STDOUT => new self(self::STDOUT),
                self::STDERR => new self(self::STDERR),
                self::DATA => new self(self::DATA),
                self::GET_VALUES => new self(self::GET_VALUES),
                self::GET_VALUES_RESULT => new self(self::GET_VALUES_RESULT),
                self::UNKNOWN_TYPE => new self(self::UNKNOWN_TYPE)
            ];
        }

        if ($type >= self::MAXTYPE || $type <= 0) {
            $type = self::UNKNOWN_TYPE;
        }

        return self::$instances[$type];
    }

    public function isBeginRequest()
    {
        return $this->value() === self::instance(RecordType::BEGIN_REQUEST)->value();
    }

    public function isAbortRequest()
    {
        return $this->value() === self::instance(RecordType::ABORT_REQUEST)->value();
    }

    public function isEndRequest()
    {
        return $this->value() === self::instance(RecordType::END_REQUEST)->value();
    }

    public function isParams()
    {
        return $this->value() === self::instance(RecordType::PARAMS)->value();
    }

    public function isStdin()
    {
        return $this->value() === self::instance(RecordType::STDIN)->value();
    }

    public function isStdout()
    {
        return $this->value() === self::instance(RecordType::STDOUT)->value();
    }

    public function isStderr()
    {
        return $this->value() === self::instance(RecordType::STDERR)->value();
    }

    public function isData()
    {
        return $this->value() === self::instance(RecordType::DATA)->value();
    }

    public function isGetValues()
    {
        return $this->value() === self::instance(RecordType::GET_VALUES)->value();
    }

    public function isGetValuesResult()
    {
        return $this->value() === self::instance(RecordType::GET_VALUES_RESULT)->value();
    }

    public function isUnknownType()
    {
        return $this->value() === self::instance(RecordType::UNKNOWN_TYPE)->value();
    }




    public static function beginRequest()
    {
        return  self::instance(RecordType::BEGIN_REQUEST);
    }

    public static function abortRequest()
    {
        return self::instance(RecordType::ABORT_REQUEST);
    }

    public static function endRequest()
    {
        return self::instance(RecordType::END_REQUEST);
    }

    public static function params()
    {
        return self::instance(RecordType::PARAMS);
    }

    public static function stdin()
    {
        return self::instance(RecordType::STDIN);
    }

    public static function stdout()
    {
        return self::instance(RecordType::STDOUT);
    }

    public static function stderr()
    {
        return self::instance(RecordType::STDERR);
    }

    public static function data()
    {
        return self::instance(RecordType::DATA);
    }

    public static function getValues()
    {
        return self::instance(RecordType::GET_VALUES);
    }

    public static function getValuesResult()
    {
        return self::instance(RecordType::GET_VALUES_RESULT);
    }

    public static function unknownType()
    {
        return self::instance(RecordType::UNKNOWN_TYPE);
    }
}
