<?php namespace PhpWebView;

use Closure;
use FFI;

class WebView
{
    private $ffi;

    private $webview;

    public function __construct(
        private string         $title,
        private int            $width,
        private int            $height,
        private WindowSizeHint $hint,
        private bool           $debug = false,
    )
    {
        try {
            $headerContent = file_get_contents(__DIR__ . '/webview_php.h');
            $this->ffi = FFI::cdef($headerContent, __DIR__ . '/build/webview_php_ffi.so');
            $this->webview = $this->ffi->webview_create((int)$this->debug, null);
            $this->setValues();
        } catch (FFI\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @return FFI
     */
    public function getFFI(): FFI
    {
        return $this->ffi;
    }

    /**
     * @return mixed
     */
    public function getWebview()
    {
        return $this->webview;
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->setValues();
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
        $this->setValues();
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
        $this->setValues();
    }

    /**
     * @return WindowSizeHint
     */
    public function getHint(): WindowSizeHint
    {
        return $this->hint;
    }

    /**
     * @param WindowSizeHint $hint
     */
    public function setHint(WindowSizeHint $hint): void
    {
        $this->hint = $hint;
        $this->setValues();
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    private function setValues(): void
    {
        $this->ffi->webview_set_title($this->webview, $this->title);
        $this->ffi->webview_set_size($this->webview, $this->width, $this->height, $this->hint->value);
    }

    public function setHTML(string $html): void
    {
        $this->ffi->webview_set_html($this->webview, $html);
    }

    public function returnValue($seq, $req, object|array $value): void
    {
        $this->ffi->webview_return($this->webview, $seq, $req, json_encode($value));
    }

    public function bind($name, Closure $function, ?Context $context = null): void
    {
        $newFunction = function ($seq, $req, $args) use ($context, $function) {
            $value = $function($seq, json_decode($req), $context);
            if ($value && (is_object($value) || is_array($value))) {
                $this->returnValue($seq, 0, $value);
            }
        };
        $this->ffi->webview_bind($this->webview, $name, $newFunction, null);
    }

    public function unbind($name): void
    {
        $this->ffi->webview_unbind($this->webview, $name);
    }

    public function eval(string $js): void
    {
        $this->ffi->webview_eval($this->webview, $js);
    }

    public function init(string $js): void
    {
        $this->ffi->webview_init($this->webview, $js);
    }

    public function navigate(string $url): void
    {
        $this->ffi->webview_navigate($this->webview, $url);
    }

    public function run(): void
    {
        $this->ffi->webview_run($this->webview);
    }

    public function destroy(): void
    {
        $this->ffi->webview_destroy($this->webview);
    }

    public function terminate(): void
    {
        $this->ffi->webview_terminate($this->webview);
    }
}