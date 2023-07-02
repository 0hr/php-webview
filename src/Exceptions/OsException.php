<?php

namespace PhpWebView\Exceptions;

class OsException extends \Exception
{
    public const OsNotSupportedCode = 1;

    public static function OsNotSupported(): self
    {
        return new self(
            "Os is not supported, Only Linux, MacOs and windows are only supported by default. You can compile yourself.",
            self::OsNotSupportedCode
        );
    }
}