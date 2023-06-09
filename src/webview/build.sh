#!/bin/sh

set -e

DIR="$(cd "$(dirname "$0")" && pwd)"
FLAGS="-Wall -Wextra -pedantic -I$DIR"
CFLAGS="-std=c99 $FLAGS"

if [ "$(uname)" = "Darwin" ]; then
	CXXFLAGS="-DWEBVIEW_COCOA -std=c++11 $FLAGS -framework WebKit"
	CXXSHARED="-DWEBVIEW_COCOA -std=c++11 $FLAGS -framework WebKit -fPIC -O3 -shared"
	OUTPUT_NAME = "webview_php_ffi.dylib"
else
	CXXFLAGS="-DWEBVIEW_GTK -std=c++11 $FLAGS $(pkg-config --cflags --libs gtk+-3.0 webkit2gtk-4.0) -fPIC"
	CXXSHARED=" -fPIC -03 -shared -DWEBVIEW_GTK -std=c++11 $FLAGS $(pkg-config --cflags --libs gtk+-3.0 webkit2gtk-4.0) -fPIC -O3 -shared"
	OUTPUT_NAME = "webview_php_ffi.so"
fi

c++ -c $CXXFLAGS webview.cc -o build/webview.o
cc -c webview_php.c $CFLAGS -o build/webview_php_ffi.o
c++ build/webview_php_ffi.o build/webview.o $CXXSHARED -o build/$OUTPUT_NAME