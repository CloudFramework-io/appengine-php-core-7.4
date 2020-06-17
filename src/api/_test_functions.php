<?php
class API extends RESTful
{
    function main()
    {

        $this->addReturnData([
            '$this->core->_version'=> $this->core->_version,
            '$this->core->is->development()'=> $this->core->is->development(),
            '$this->core->is->production()'=> $this->core->is->production(),
            '$this->getHeaders()'=> $this->getHeaders(),
            '$this->getHeader("X-DS-TOKEN")'=> $this->getHeader("X-DS-TOKEN"),
            '$this->getHeadersToResend()'=> $this->getHeadersToResend(),

        ]);
    }
}
