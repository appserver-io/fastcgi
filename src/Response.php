<?php
namespace Crunch\FastCGI;

class Response
{
    /** @var string */
    private $content = '';
    /** @var string */
    private $error = '';

    public function __construct($content, $error)
    {
        $this->content = $content;
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
