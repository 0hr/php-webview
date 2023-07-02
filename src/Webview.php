<?php namespace PhpWebView;

use Closure;
use FFI;
use PhpWebView\Exceptions\OsException;
use PhpWebView\FFI\Darwin;
use PhpWebView\FFI\Linux;
use PhpWebView\FFI\Os;
use PhpWebView\FFI\Windows;

class WebView
{
    private $ffi;

    private $webview;

    /**
     * @param string $title
     * @param int $width
     * @param int $height
     * @param WindowSizeHint $hint
     * @param string $baseDir
     * @param string|null $libraryFile
     * @param bool $debug
     * @throws OsException
     * @throws FFI\Exception
     */
    public function __construct(
        protected string         $title,
        protected int            $width,
        protected int            $height,
        protected WindowSizeHint $hint,
        protected bool           $debug = false,
        protected string         $baseDir = __DIR__,
        protected ?string        $libraryFile = null,
    )
    {
        $headerContent = file_get_contents($this->baseDir . DIRECTORY_SEPARATOR . 'webview_php.h');
        $this->ffi = FFI::cdef($headerContent, $this->getDefaultLibraryFile());
        $this->webview = $this->ffi->webview_create((int)$this->debug, null);
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
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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
    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
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
    public function setHint(WindowSizeHint $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setHTML(string $html): self
    {
        $this->ffi->webview_set_html($this->webview, $html);

        return $this;
    }

    public function returnValue($seq, $req, object|array $value): self
    {
        $this->ffi->webview_return($this->webview, $seq, $req, json_encode($value));

        return $this;
    }

    public function bind($name, Closure $function, ?Context $context = null): self
    {
        $newFunction = function ($seq, $req, $args) use ($context, $function) {
            $value = $function($seq, json_decode($req), $context);
            if ($value && (is_object($value) || is_array($value))) {
                $this->returnValue($seq, 0, $value);
            }
        };
        $this->ffi->webview_bind($this->webview, $name, $newFunction, null);

        return $this;
    }

    public function unbind($name): self
    {
        $this->ffi->webview_unbind($this->webview, $name);

        return $this;
    }

    public function eval(string $js): self
    {
        $this->ffi->webview_eval($this->webview, $js);

        return $this;
    }

    public function init(string $js): self
    {
        $this->ffi->webview_init($this->webview, $js);

        return $this;
    }

    public function navigate(string $url): self
    {
        $this->ffi->webview_navigate($this->webview, $url);

        return $this;
    }

    public function run(): self
    {
        $this->ffi->webview_set_title($this->webview, $this->title);
        $this->ffi->webview_set_size($this->webview, $this->width, $this->height, $this->hint->value);
        $this->ffi->webview_run($this->webview);

        return $this;
    }

    public function destroy(): self
    {
        $this->ffi->webview_destroy($this->webview);

        return $this;
    }

    public function terminate(): self
    {
        $this->ffi->webview_terminate($this->webview);

        return $this;
    }

    /**
     * @return string
     * @throws OsException
     */
    private function getDefaultLibraryFile(): string
    {
        if ($this->libraryFile !== null) {
            return $this->libraryFile;
        }

        $this->libraryFile = match (PHP_OS_FAMILY) {
            'Linux' => $this->baseDir . '/build/linux/webview_php_ffi.so',
            'Darwin' => $this->baseDir . '/build/macos/webview_php_ffi.dylib',
            'Windows' => $this->baseDir . '\build\windows\webview_php_ffi.dll',
            default => throw OsException::OsNotSupported(),
        };


        return $this->libraryFile;
    }
}