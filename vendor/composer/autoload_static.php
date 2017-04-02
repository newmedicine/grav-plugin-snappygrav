<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0e1ed8c032006b85d72fbc19b1a64531
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Process\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Process\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/process',
        ),
    );

    public static $prefixesPsr0 = array (
        'K' => 
        array (
            'Knp\\Snappy' => 
            array (
                0 => __DIR__ . '/..' . '/knplabs/knp-snappy/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0e1ed8c032006b85d72fbc19b1a64531::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0e1ed8c032006b85d72fbc19b1a64531::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit0e1ed8c032006b85d72fbc19b1a64531::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}