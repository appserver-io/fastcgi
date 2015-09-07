<?php
namespace Crunch\FastCGI\Protocol;

use ArrayIterator;
use Crunch\FastCGI\ReaderWriter\ReaderInterface;
use Traversable;

class Response implements ResponseInterface
{
    /** @var int */
    private $requestId;
    /** @var ReaderInterface */
    private $content = '';
    /** @var ReaderInterface */
    private $error = '';

    /**
     * @param int $requestId
     * @param ReaderInterface $content
     * @param ReaderInterface $error
     */
    public function __construct($requestId, ReaderInterface $content, ReaderInterface $error)
    {
        $this->requestId = $requestId;
        $this->content = $content;
        $this->error = $error;
    }

    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @return ReaderInterface
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return ReaderInterface
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Encodes request into an traversable of records.
     *
     * @return Traversable|Record[]
     */
    public function toRecords()
    {
        $result = [];

        while ($chunk = $this->error->read(65535)) {
            $result[] = new Record(new Header(RecordType::stderr(), $this->requestId, strlen($chunk)), $chunk);
        }

        while ($chunk = $this->content->read(65535)) {
            $result[] = new Record(new Header(RecordType::stdout(), $this->requestId, strlen($chunk)), $chunk);
        }
        $result[] = new Record(new Header(RecordType::stdout(), $this->requestId, 0, 8), '');

        $result[] = new Record(new Header(RecordType::endRequest(), $this->requestId, 0, 8), '');

        return new ArrayIterator($result);
    }

    public function __debugInfo()
    {
        return [
            'content' => bin2hex($this->content),
            'error'   => bin2hex($this->error),
        ];
    }
}
