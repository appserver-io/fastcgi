<?php
namespace Crunch\FastCGI\Protocol;

use Crunch\FastCGI\ReaderWriter\ReaderInterface;

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
}
