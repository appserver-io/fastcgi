<?php
namespace Crunch\FastCGI;

interface ClientInterface
{
    /**
     * Creates a new request
     *
     * Although you can create a Request instance manually it is highly
     * recommended to use this factory method, because only this one
     * ensures, that the request uses a previously unused request id.
     *
     * @param RequestParametersInterface $parameters
     * @param string|null $stdin
     * @return RequestInterface
     */
    public function newRequest(RequestParametersInterface $parameters = null, $stdin = null);

    /**
     * Send request, but don't wait for response
     *
     * Remember to call receiveResponse(). Else, it will remain the buffer.
     *
     * @param RequestInterface $request
     */
    public function sendRequest(RequestInterface $request);

    /**
     * Receive response
     *
     * Returns the response a request previously sent with sendRequest()
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function receiveResponse(RequestInterface $request);
}
