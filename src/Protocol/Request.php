<?php
namespace Crunch\FastCGI\Protocol;

use ArrayIterator;
use Crunch\FastCGI\ReaderWriter\EmptyReader;
use Crunch\FastCGI\ReaderWriter\ReaderInterface;
use Traversable;

class Request implements RequestInterface
{
    /** @var Role */
    private $role;
    /** @var int Request ID */
    private $requestId;
    /** @var bool */
    private $keepConnection;
    /** @var RequestParameters */
    private $parameters;
    /** @var ReaderInterface content to send ("body") */
    private $stdin;

    /**
     * Creates new Request instance.
     *
     * If $keepConnection is set to `false` the server may close the connection
     * right after sending the response.
     *
     * @param Role                            $role
     * @param int                             $requestId
     * @param bool                            $keepConnection Default: true
     * @param RequestParametersInterface|null $parameters
     * @param ReaderInterface|null            $stdin
     */
    public function __construct(Role $role, $requestId, $keepConnection = true, RequestParametersInterface $parameters = null, ReaderInterface $stdin = null)
    {
        $this->role = $role;
        $this->requestId = $requestId;
        $this->keepConnection = $keepConnection;
        $this->parameters = $parameters ?: new RequestParameters();
        $this->stdin = $stdin ?: new EmptyReader();
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return bool
     */
    public function isKeepConnection()
    {
        return $this->keepConnection;
    }

    /**
     * @return RequestParameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return ReaderInterface
     */
    public function getStdin()
    {
        return $this->stdin;
    }

    /**
     * Encodes request into an traversable of records.
     *
     * @return Traversable|Record[]
     */
    public function toRecords()
    {
        $result = [new Record(new Header(RecordType::beginRequest(), $this->getRequestId(), 8), \pack('xCCxxxxx', $this->role->value(), 0xFF & ($this->keepConnection ? 1 : 0)))];

        foreach ($this->getParameters()->encode($this->getRequestId()) as $value) {
            $result[] = $value;
        }

        while ($chunk = $this->stdin->read(65535)) {
            $result[] = new Record(new Header(RecordType::stdin(), $this->getRequestId(), strlen($chunk)), $chunk);
        }

        $result[] = new Record(new Header(RecordType::stdin(), $this->getRequestId(), 0, 0), '');

        return new ArrayIterator($result);
    }
}
