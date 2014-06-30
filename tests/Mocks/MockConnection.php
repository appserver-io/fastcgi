<?php
namespace Crunch\FastCGI\Mocks;

use Crunch\FastCGI\Connection;
use Crunch\FastCGI\Request;

/**
 * Crunch\FastCGI\Mocks\MockConnection
 *
 * A mock of the connection class, which enables us to inject dependencies.
 */
class MockConnection extends Connection
{
    /**
     * Send request, but don't wait for response
     *
     * Remember to call receiveResponse(). Else, it will remain the buffer.
     *
     * @param Request $request
     */
    public function sendRequest(Request $request)
    {
        // Call the parent so we get all the functionality going
        parent::sendRequest($request);

        // Overwrite the parent ResponseBuilder with out mock
        $this->builder[$request->ID] = new MockResponseBuilder;
    }

    /**
     * Receive response
     *
     * Will wrap the parent method and allow for the addition of a timeout
     *
     * @param Request $request
     * @param integer $timeout
     * @return Response
     */
    public function receiveResponse (Request $request, $timeout)
    {
        $this->builder[$request->ID]->setCompleteTimeout($timeout);

        return parent::receiveResponse($request);
    }
}
