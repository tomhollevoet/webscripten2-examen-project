<?php

// Bootstrap
require __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app->error(function (\Exception $e, $code) use ($app) {
	if ($code == 404) {
		return $app['twig']->render('404.twig', array('error' => $e->getMessage()));
	} else {
                return $app['twig']->render('404.twig', array('error' => 'Deze opdracht kan niet worden uitgevoerd'));
	}
});

$app->get('/', function(Silex\Application $app) {
	return $app->redirect($app['request']->getBaseUrl() . '/');
});

// Mount our ControllerProviders
$app->mount('/', new Immotom\Provider\Controller\SiteIndexController());
