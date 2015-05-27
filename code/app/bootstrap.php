<?php

// Require Composer Autoloader
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Create new Silex App
$app = new Silex\Application();

// Use Twig — @note: Be sure to install Twig via Composer first!
$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__ .  DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'views'
));

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . DIRECTORY_SEPARATOR . 'config.php'));

$app->register(new Silex\Provider\DoctrineServiceProvider());

$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
    'swiftmailer.options' => array(
        'host' => 'smtp2.kahosl.be',
        'port' => '25',
        'username' => '',
        'password' => '',
        'encryption' => null,
        'auth_mode' => null
    )
));

// Use Repository Service Provider — @note: Be sure to install RSP via Composer first!
$app->register(new Knp\Provider\RepositoryServiceProvider(), array());

// Use UrlGenerator Service Provider - @note: Be sure to install "symfony/twig-bridge" via Composer if you want to use the `url` & `path` functions in Twig
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Use Validator Service Provider - @note: Be sure to install "symfony/validator" via Composer first!
$app->register(new Silex\Provider\ValidatorServiceProvider());

// Use Form Service Provider - @note: Be sure to install "symfony/form" & "symfony/twig-bridge" via Composer first!
$app->register(new Silex\Provider\FormServiceProvider());

// Use Translation Service Provider because without it our form won't work
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
	'translator.messages' => array(),
));

// Use Session Service Provider
$app->register(new Silex\Provider\SessionServiceProvider());
