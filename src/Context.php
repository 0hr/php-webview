<?php

namespace PhpWebView;

use FFI\CData;

class Context
{
    private WebView $webview;

    public function __construct(WebView $webview)
    {
        $this->webview = $webview;
    }

    public function getWebview()
    {
        return $this->webview;
    }
}