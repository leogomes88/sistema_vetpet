<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1d6d5afd3dbce3a46045a833db7e23e1
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MF\\' => 3,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MF\\' => 
        array (
            0 => __DIR__ . '/..' . '/MF',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1d6d5afd3dbce3a46045a833db7e23e1::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1d6d5afd3dbce3a46045a833db7e23e1::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
