<?php
namespace Crunch\FastCGI\Protocol;

use ArrayIterator;
use Crunch\FastCGI\ReaderWriter\ReaderInterface;
use Traversable;

class Response implements ResponseInterface
{
    /** @var ReaderInterface */
    private $content = '';
    /** @var ReaderInterface */
    private $error = '';

    public function __construct(ReaderInterface $content, ReaderInterface $error)
    {
        $this->content = $content;
        $this->error = $error;
    }

    /**
     * @return ReaderInterface
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return \Crunch\FastCGI\ReaderWriter\ReaderInterface
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * Encodes request into an traversable of records
     *
     * @return Traversable|Record[]
     */
    public function toRecords($id)
    {
        $result = [];

        while ($chunk = $this->error->read(65535)) {
            $result[] = new Record(new Header(RecordType::stderr(), $id, strlen($chunk)), $chunk);
        }

        while ($chunk = $this->error->read(65535)) {
            $result[] = new Record(new Header(RecordType::stdout(), $id, strlen($chunk)), $chunk);
        }
        $result[] = new Record(new Header(RecordType::stdout(), $id, 0, 8), '');

        $result[] = new Record(new Header(RecordType::endRequest(), $id, 0, 8), '');

        return new ArrayIterator($result);
    }
}
