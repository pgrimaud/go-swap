<?php

namespace App\Client;

use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Response;

class PokebipClient extends AbstractBrowser
{
    protected function doRequest($request)
    {
        return new Response($content, $status, $headers);
    }
}