<?php
namespace Crunch\FastCGI\ReaderWriter;

/**
 * Reader interface
 *
 * Reads a (binary) string from somewhere. Implementations can assume, that
 * once the data was read it can be dropped ("read-once").
 *
 * The implementation doesn't need to tell, whether or not all content was read.
 * The implementation should return an empty string in that case.
 */
interface ReaderInterface
{
    /**
     * Read
     *
     * Reads at most $max bytes. For consistency 0 is a valid value and will
     * always return an empty string.
     *
     * If $max is null the entire available content.
     *
     * If there are less than $max bytes available, it will return everything
     * available.
     *
     * @param int|null $max
     * @return string
     */
    public function read($max = null);
}
