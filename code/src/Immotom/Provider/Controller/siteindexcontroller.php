<?php

namespace ImmoTom\Provider\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Regex;

class SiteIndexController implements ControllerProviderInterface {
	public function connect(Application $app) {
		//@note $app['controllers_factory'] is a factory that returns a new instance of ControllerCollection when used.
		//@see http://silex.sensiolabs.org/doc/organizing_controllers.html
		$controllers = $app['controllers_factory'];
                
                $controllers
                    ->get('/', array($this, 'browse'))
                    ->bind('immotom.browse');
                
                $controllers
                    ->get('/tekoop/{page}', array($this, 'tekoop'))
                    ->assert('page', '\d+')
                    ->method('GET|POST')
                    ->bind('immotom.tekoop');
                
                 $controllers
                    ->get('/tekoop/detail/{id}', array($this, 'tekoopdetail'))
                    ->assert('id', '\d+')
                    ->bind('immotom.tekoopdetail');;
                    
                $controllers
                    ->get('/tehuur/{page}', array($this, 'tehuur'))
                    ->assert('page', '\d+')
                    ->method('GET|POST')
                    ->bind('immotom.tehuur');
                
                $controllers
                    ->get('/tehuur/detail/{id}', array($this, 'tehuurdetail'))
                    ->assert('id', '\d+')
                    ->bind('immotom.tehuurdetail');;
                    
                $controllers
                    ->get('/onzemakelaars/{page}', array($this, 'onzemakelaars'))
                    ->assert('page', '\d+')
                    ->bind('immotom.onzemakelaars');
                
                $controllers
                    ->get('/contact/{id}', array($this, 'contact'))
                    ->assert('id', '\d+')
                    ->method('GET|POST')
                    ->bind('immotom.contact');
                
                $controllers
                    ->get('/contactAboutRealestate/{id}', array($this, 'contactAboutRealestate'))
                    ->assert('id', '\d+')
                    ->bind('immotom.contactAboutRealestate');
                
                $controllers
                    ->get('/resetfiltertekoop', array($this, 'resetfiltertekoop'))
                    ->bind('immotom.resetfiltertekoop');
                
                $controllers
                    ->get('/resetfiltertehuur', array($this, 'resetfiltertehuur'))
                    ->bind('immotom.resetfiltertehuur');

                return $controllers;
	}


	public function browse(Application $app) {
                $app['session']->set('contactimmotom', array('idRealeState' => null ));
                // Get brokers for start
                $brokers = $app['immotom']->getBrokersStart();
                // Get realestate for start
                $realestates = $app['immotom']->getRealeStateStart();
                
                return $app['twig']->render('siteIndex.twig', array('brokers' => $brokers, 'realestates' => $realestates));                                    
	}
        
        
	public function tekoop(Application $app, $page) { 
                $app['session']->set('contactimmotom', array('idRealeState' => null ));
                // Paging
                $page = (int)$page;
                
                // Check if there is a filter filled in.
                if($app['session']->get('filterimmotom') != null){
                    $filterSession = ($app['session']->get('filterimmotom'));           
                    $minpriceses = $filterSession['minpriceses'];
                    $maxpriceses = $filterSession['maxpriceses']; 
                    $bedroomsses = $filterSession['bedroomsses'];
                    $categoryses = $filterSession['categoryses'];
                }
                else {
                    $minpriceses = '';
                    $maxpriceses = '';
                    $bedroomsses = '';
                    $categoryses = '0';
                }
                
                // Filter form
                $filterform = $app['form.factory']->createNamed('filterform')
                        ->add('minprice', 'text', array(
                            'constraints' => array(),
                            'data'  => $minpriceses,
                            'label' => 'Minimum prijs'
                        ))
                        ->add('maxprice', 'text', array(
                            'constraints' => array(),
                            'data'  => $maxpriceses,
                            'label' => 'Maximum prijs'
                        ))
                        ->add('bedroom', 'text', array(
                            'constraints' => array(),
                            'data'  => $bedroomsses,
                            'label' => 'Aantal slaapkamers'
                        ))
                        ->add('category', 'choice', array(
                            'choices' => array(
                                0 => 'Alles',
                                1 => 'Appartement', 
                                2 => 'Huis',
                                3 => 'Villa',
                                4 => 'Bungalow',
                                5 => 'Garage'
                                ),'data' => $categoryses,
                                'label' => 'Categorie'
                            )
                        )
                    ;
                
                if ('POST' == $app['request']->getMethod()) {
                            $filterform->bind($app['request']);

                            if ($filterform->isValid()) {
                                    $data = $filterform->getData();
                                    
                                    $error = false;
                                    // Check errors in form.
                                    if(is_numeric($data['minprice']) == false && $data['minprice'] != ""){
                                            $filterform->get('minprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Geen nummer in minimum prijs!'));
                                            $error = true;        
                                    }
                                    if(is_numeric($data['maxprice']) == false && $data['maxprice'] != "" ){
                                            $filterform->get('maxprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Geen nummer in maximum prijs!'));
                                            $error = true;        
                                    }
                                    if(is_numeric($data['bedroom']) == false && $data['bedroom'] != ""){
                                            $filterform->get('bedroom')->addError(new \Symfony\Component\Form\FormError('FOUT: Geen nummer in slaapkamerveld!'));
                                            $error = true;
                                    }
                                    if($data['minprice'] > $data['maxprice'] && $data['maxprice'] != ""){
                                            $filterform->get('minprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Min. moet kleiner zijn dan max. prijs!'));
                                            $error = true;
                                    }
                                    if($data['minprice'] < 0){
                                            $filterform->get('minprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Min. prijs moet groter zijn dan 0!'));
                                            $error = true;
                                    }
                                    if($data['maxprice'] < 0){
                                            $filterform->get('maxprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Max. prijs moet groter zijn dan 0!'));
                                            $error = true;
                                    }
                                    if($data['bedroom'] > 5 || $data['bedroom'] < 0){
                                            $filterform->get('bedroom')->addError(new \Symfony\Component\Form\FormError('FOUT: Tussen 0 en 5 slaapkamers!'));
                                            $error = true;
                                    }
                                    if ($error){
                                       return $app['twig']->render('tekoop.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => $page, 'filterform' => $filterform->createView()));                 
                                    }
                                    
                                    // Fill in the session with the data from form.
                                    $app['session']->set('filterimmotom', array(
                                        'categoryses' => $data['category'],
                                        'minpriceses' => $data['minprice'],
                                        'maxpriceses' => $data['maxprice'],
                                        'bedroomsses' => $data['bedroom']
                                    ));
                             }
                            else {
                                return $app['twig']->render('tekoop.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => $page, 'filterform' => $filterform->createView()));                        
                            }
                }
                
                // Get data from session array
                $session = $app['session']->get('filterimmotom');
                
                if($page != 0){
                    $countOfItemsOnPage = 6;
                    
                    // COUNT REALESTATES
                    // 1) Only category
                    if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], null, null, null);
                    }
                    // 2) Only min price
                    else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', null, $session['minpriceses'], null, null);
                    }
                    // 3) Only max price
                    else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', null, null, $session['maxpriceses'], null);
                    }
                    // 4) Only bedrooms
                    else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', null, null, null, $session['bedroomsses']);
                    }
                    // 5) category AND min price
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], $session['minpriceses'], null, null);
                    }
                    // 6) category AND max price
                    else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], null, $session['maxpriceses'], null);
                    }
                    // 7) category AND bedrooms
                    else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], null, null, $session['bedroomsses']);
                    }
                    // 8) minprice AND maxprice
                    else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', null, $session['minpriceses'], $session['maxpriceses'], null);
                    }
                    // 9) minprice AND bedrooms
                    else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', null, $session['minpriceses'], null, $session['bedroomsses']);
                    }
                    // 10) maxprice AND bedrooms
                    else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', null, null, $session['maxpriceses'], $session['bedroomsses']);
                    }
                    // 11) category AND min price AND max price
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], null);
                    }
                    // 12) category AND max price AND bedroom
                    else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], null, $session['maxpriceses'], $session['bedroomsses']);
                    }
                    // 14) category AND min price AND bedroom
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], $session['minpriceses'], null, $session['bedroomsses']);
                    }
                    // 15) ALL
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('1', $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], $session['bedroomsses']);
                    }
                    // 0) No filter
                    else {
                        $countRealestates = $app['immotom']->countRealeStates('1', null, null, null, null);
                    }
                    
                    
                    // Count total items
                    $number = (int)$countRealestates['COUNT(idrealestate)'];

                    // Number of pages.
                    $numberPages = ceil($number / $countOfItemsOnPage);

                    $pagesArray;
                    for ($i = 1; $i <= $numberPages; $i++) {
                        $pagesArray[$i] = $i;
                    }

                    // (page - 1) => Count starts by 1!
                    $begin = ($page - 1) * $countOfItemsOnPage;
                    $end = $page * $countOfItemsOnPage;
                }
                // 0
                else {
                    return $app['twig']->render('tekoop.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => null, 'filterform' => $filterform->createView()));    
                }
                
                
                
                // GET THE REALESTATES
                // 1) Only category
                if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], null, null, null);
                }
                // 2) Only min price
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, $session['minpriceses'], null, null);
                }
                // 3) Only max price
                else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, null, $session['maxpriceses'], null);
                }
                // 4) Only bedrooùm
                else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, null, null, $session['bedroomsses']);
                }
                // 5) category AND min price
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], $session['minpriceses'], null, null);
                }
                // 6) category AND max price
                else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], null, $session['maxpriceses'], null);
                }
                // 7) category AND bedrooms
                else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], null, null, $session['bedroomsses']);
                }
                // 8) minprice AND maxprice
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, $session['minpriceses'], $session['maxpriceses'], null);
                }
                // 9) minprice AND bedrooms
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, $session['minpriceses'], null, $session['bedroomsses']);
                }
                // 10) minprice AND bedrooms
                else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, null, $session['maxpriceses'], $session['bedroomsses']);
                }
                // 11) category AND min price AND max price
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], null);
                }
                // 12) category AND AND max price AND bedroom
                else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], null, $session['maxpriceses'], $session['bedroomsses']);
                }
                // 13) min price AND max price AND bedroom
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, $session['minpriceses'], $session['maxpriceses'], $session['bedroomsses']);
                }
                // 14) category AND min price AND bedroom
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], $session['minpriceses'], null, $session['bedroomsses']);
                }
                // 15) ALL
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], $session['bedroomsses']);
                }
                // 0) No filter
                else {
                    $realestates = $app['immotom']->getRealeStates('1', $begin, $end, null, null, null, null);
                }
            
                
                // Check if there are realestates
                if (isset($pagesArray) == false || $numberPages == 1){
                    $pagesArray = null;
                }
                
                return $app['twig']->render('tekoop.twig', array('realestates' => $realestates, 'pagesArray' => $pagesArray, 'currentpage' => $page, 'filterform' => $filterform->createView()));                                    
	}
        
	public function tekoopdetail(Application $app, $id) {
                $app['session']->set('contactimmotom', array('idRealeState' => null ));
                 
                // Get Clickcount - Edit Clickcount
                $countClick = $app['immotom']->getCountClick($id);
                $countClickEdit = $countClick['countclick'] + 1;
                $app['immotom']->editCountClick($id, $countClickEdit);
                
                // Get realestate
                $realestate = $app['immotom']->getRealeState($id, '1');
                
                // Get broker
                $broker = $app['immotom']->getBroker($realestate['broker']);
                
                // Get photos
                $photos = $app['immotom']->getPhotosRealeState($id);
                
                // 5 Related items
                $relatedrealestates = $app['immotom']->getRelatedRealeStates($id, $realestate['category'], $realestate['forsale'], '1');
                
                return $app['twig']->render('tekoopdetail.twig', array('realestate' => $realestate, 'broker' => $broker, 'photos' => $photos, 'relatedrealestates' => $relatedrealestates));                                    
	}
        
	public function tehuur(Application $app, $page) { 
                $app['session']->set('contactimmotom', array('idRealeState' => null ));
                 
                // Paging
                $page = (int)$page;
                
                // Check if there is a filter filled in.
                if($app['session']->get('filterimmotom') != null){
                    $filterSession = ($app['session']->get('filterimmotom'));           
                    $minpriceses = $filterSession['minpriceses'];
                    $maxpriceses = $filterSession['maxpriceses']; 
                    $bedroomsses = $filterSession['bedroomsses'];
                    $categoryses = $filterSession['categoryses'];
                }
                else {
                    $minpriceses = '';
                    $maxpriceses = '';
                    $bedroomsses = '';
                    $categoryses = '0';
                }
                
                // Filter form
                $filterform = $app['form.factory']->createNamed('filterform')
                        ->add('minprice', 'text', array(
                            'constraints' => array(),
                            'data'  => $minpriceses,
                            'label' => 'Minimum prijs'
                        ))
                        ->add('maxprice', 'text', array(
                            'constraints' => array(),
                            'data'  => $maxpriceses,
                            'label' => 'Maximum prijs'
                        ))
                        ->add('bedroom', 'text', array(
                            'constraints' => array(),
                            'data'  => $bedroomsses,
                            'label' => 'Aantal slaapkamers'
                        ))
                        ->add('category', 'choice', array(
                            'choices' => array(
                                0 => 'Alles',
                                1 => 'Appartement', 
                                2 => 'Huis',
                                3 => 'Villa',
                                4 => 'Bungalow',
                                5 => 'Garage'
                                ),'data' => $categoryses,
                                'label' => 'Categorie'
                            )
                        )
                    ;
                
                if ('POST' == $app['request']->getMethod()) {
                            $filterform->bind($app['request']);

                            if ($filterform->isValid()) {
                                    $data = $filterform->getData();
                                    
                                    $error = false;
                                    // Check errors in form.
                                    if(is_numeric($data['minprice']) == false && $data['minprice'] != ""){
                                            $filterform->get('minprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Geen nummer in minimum prijs!'));
                                            $error = true;        
                                    }
                                    if(is_numeric($data['maxprice']) == false && $data['maxprice'] != "" ){
                                            $filterform->get('maxprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Geen nummer in maximum prijs!'));
                                            $error = true;        
                                    }
                                    if(is_numeric($data['bedroom']) == false && $data['bedroom'] != ""){
                                            $filterform->get('bedroom')->addError(new \Symfony\Component\Form\FormError('FOUT: Geen nummer in slaapkamerveld!'));
                                            $error = true;
                                    }
                                    if($data['minprice'] > $data['maxprice'] && $data['maxprice'] != ""){
                                            $filterform->get('minprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Min. moet kleiner zijn dan max. prijs!'));
                                            $error = true;
                                    }
                                    if($data['minprice'] < 0){
                                            $filterform->get('minprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Min. prijs moet groter zijn dan 0!'));
                                            $error = true;
                                    }
                                    if($data['maxprice'] < 0){
                                            $filterform->get('maxprice')->addError(new \Symfony\Component\Form\FormError('FOUT: Max. prijs moet groter zijn dan 0!'));
                                            $error = true;
                                    }
                                    if($data['bedroom'] > 5 || $data['bedroom'] < 0){
                                            $filterform->get('bedroom')->addError(new \Symfony\Component\Form\FormError('FOUT: Tussen 0 en 5 slaapkamers!'));
                                            $error = true;
                                    }
                                    if ($error){
                                       return $app['twig']->render('tehuur.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => $page, 'filterform' => $filterform->createView()));                 
                                    }
                                    
                                    $app['session']->set('filterimmotom', array(
                                        'categoryses' => $data['category'],
                                        'minpriceses' => $data['minprice'],
                                        'maxpriceses' => $data['maxprice'],
                                        'bedroomsses' => $data['bedroom']
                                    ));
                             }
                            else {
                                return $app['twig']->render('tehuur.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => $page, 'filterform' => $filterform->createView()));                        
                            }
                }
                
                // Get data from session array
                $session = $app['session']->get('filterimmotom');
                
                if($page != 0){
                    $countOfItemsOnPage = 6;
                    
                    // COUNT REALESTATES
                    // 1) Only category
                    if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], null, null, null);
                    }
                    // 2) Only min price
                    else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', null, $session['minpriceses'], null, null);
                    }
                    // 3) Only max price
                    else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', null, null, $session['maxpriceses'], null);
                    }
                    // 4) Only bedrooms
                    else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', null, null, null, $session['bedroomsses']);
                    }
                    // 5) category AND min price
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], $session['minpriceses'], null, null);
                    }
                    // 6) category AND max price
                    else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], null, $session['maxpriceses'], null);
                    }
                    // 7) category AND bedrooms
                    else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], null, null, $session['bedroomsses']);
                    }
                    // 8) minprice AND maxprice
                    else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', null, $session['minpriceses'], $session['maxpriceses'], null);
                    }
                    // 9) minprice AND bedrooms
                    else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', null, $session['minpriceses'], null, $session['bedroomsses']);
                    }
                    // 10) maxprice AND bedrooms
                    else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', null, null, $session['maxpriceses'], $session['bedroomsses']);
                    }
                    // 11) category AND min price AND max price
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], null);
                    }
                    // 12) category AND max price AND bedroom
                    else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], null, $session['maxpriceses'], $session['bedroomsses']);
                    }
                    // 14) category AND min price AND bedroom
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], $session['minpriceses'], null, $session['bedroomsses']);
                    }
                    // 15) ALL
                    else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                        $countRealestates = $app['immotom']->countRealeStates('0', $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], $session['bedroomsses']);
                    }
                    // 0) No filter
                    else {
                        $countRealestates = $app['immotom']->countRealeStates('0', null, null, null, null);
                    }
                    
                    
                    // Count total items
                    $number = (int)$countRealestates['COUNT(idrealestate)'];

                    // Number of pages (if there are 6 realestate on a page)
                    $numberPages = ceil($number / $countOfItemsOnPage);

                    $pagesArray;
                    for ($i = 1; $i <= $numberPages; $i++) {
                        $pagesArray[$i] = $i;
                    }

                    // (page - 1) => Count starts by 1!
                    $begin = ($page - 1) * $countOfItemsOnPage;
                    $end = $page * $countOfItemsOnPage;
                }
                // 0
                else {
                    return $app['twig']->render('tehuur.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => null, 'filterform' => $filterform->createView()));    
                }
                
                
                
                // GET THE REALESTATES
                // 1) Only category
                if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], null, null, null);
                }
                // 2) Only min price
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, $session['minpriceses'], null, null);
                }
                // 3) Only max price
                else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, null, $session['maxpriceses'], null);
                }
                // 4) Only bedrooùm
                else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, null, null, $session['bedroomsses']);
                }
                // 5) category AND min price
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], $session['minpriceses'], null, null);
                }
                // 6) category AND max price
                else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], null, $session['maxpriceses'], null);
                }
                // 7) category AND bedrooms
                else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], null, null, $session['bedroomsses']);
                }
                // 8) minprice AND maxprice
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, $session['minpriceses'], $session['maxpriceses'], null);
                }
                // 9) minprice AND bedrooms
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, $session['minpriceses'], null, $session['bedroomsses']);
                }
                // 10) minprice AND bedrooms
                else if($session['categoryses'] == 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, null, $session['maxpriceses'], $session['bedroomsses']);
                }
                // 11) category AND min price AND max price
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] == null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], null);
                }
                // 12) category AND AND max price AND bedroom
                else if($session['categoryses'] != 0 && $session['minpriceses'] == null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], null, $session['maxpriceses'], $session['bedroomsses']);
                }
                // 13) min price AND max price AND bedroom
                else if($session['categoryses'] == 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, $session['minpriceses'], $session['maxpriceses'], $session['bedroomsses']);
                }
                // 14) category AND min price AND bedroom
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] == null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], $session['minpriceses'], null, $session['bedroomsses']);
                }
                // 15) ALL
                else if($session['categoryses'] != 0 && $session['minpriceses'] != null && $session['maxpriceses'] != null && $session['bedroomsses'] != null){
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, $session['categoryses'], $session['minpriceses'], $session['maxpriceses'], $session['bedroomsses']);
                }
                // 0) No filter
                else {
                    $realestates = $app['immotom']->getRealeStates('0', $begin, $end, null, null, null, null);
                }
            
                
                // Check if there are realestates
                if (isset($pagesArray) == false || $numberPages == 1){
                    $pagesArray = null;
                }
                
                return $app['twig']->render('tehuur.twig', array('realestates' => $realestates, 'pagesArray' => $pagesArray, 'currentpage' => $page, 'filterform' => $filterform->createView()));                                    
	}
        
	public function tehuurdetail(Application $app, $id) {
                $app['session']->set('contactimmotom', array('idRealeState' => null ));
                
               // Get Clickcount - Edit Clickcount
                $countClick = $app['immotom']->getCountClick($id);
                $countClickEdit = $countClick['countclick'] + 1;
                $app['immotom']->editCountClick($id, $countClickEdit);
                
                // Get realestate
                $realestate = $app['immotom']->getRealeState($id, '0');
                
                // Get broker
                $broker = $app['immotom']->getBroker($realestate['broker']);
                
                // Get photos
                $photos = $app['immotom']->getPhotosRealeState($id);
                
                // 5 Related items
                $relatedrealestates = $app['immotom']->getRelatedRealeStates($id, $realestate['category'], $realestate['forsale'], '0');
                
                return $app['twig']->render('tehuurdetail.twig', array('realestate' => $realestate, 'broker' => $broker, 'photos' => $photos, 'relatedrealestates' => $relatedrealestates));                                   
	}
        
        
        public function resetfiltertekoop(Application $app) {
                $app['session']->set('filterimmotom', array(
                    'minpriceses' => '',
                    'maxpriceses' => '',
                    'bedroomsses' => '',
                    'categoryses' => 0
                ));
                
                return $app->redirect($app['url_generator']->generate('immotom.tekoop', array('page' => 1)));                
	}
        
        public function resetfiltertehuur(Application $app) {
                $app['session']->set('filterimmotom', array(
                    'minpriceses' => '',
                    'maxpriceses' => '',
                    'bedroomsses' => '',
                    'categoryses' => 0
                ));
                
                return $app->redirect($app['url_generator']->generate('immotom.tehuur', array('page' => 1)));                
	}

	public function onzemakelaars(Application $app, $page) {
                $app['session']->set('contactimmotom', array('idRealeState' => null ));
                 
                // Paging
                $page = (int)$page;
                
                if($page != 0){
                    $countOfItemsOnPage = 6;
                    
                    $countRealestates = $app['immotom']->countBrokersForPage();
                    // Count total items
                    $number = (int)$countRealestates['COUNT(idbrokers)'];

                    // Number of pages (if there are 6 realestate on a page)
                    $numberPages = ceil($number / $countOfItemsOnPage);

                    $pagesArray;
                    for ($i = 1; $i <= $numberPages; $i++) {
                        $pagesArray[$i] = $i;
                    }

                    // (page - 1) => Count starts by 1!
                    $begin = ($page - 1) * $countOfItemsOnPage;
                    $end = $page * $countOfItemsOnPage;
                }
                // 0
                else {
                    return $app['twig']->render('onzemakelaars.twig', array('realestates' => null, 'pagesArray' => null, 'currentpage' => null));    
                }
                
                
                // GET THE BROKERS
                $brokers = $app['immotom']->getBrokersForPage($begin, $end);
                
                // GET REALESTATES
                $realestates = $app['immotom']->getRealeStateStart();
                
                // Check if there are realestates
                if (isset($pagesArray) == false || $numberPages == 1){
                    $pagesArray = null;
                }
                
                return $app['twig']->render('onzemakelaars.twig', array('brokers' => $brokers, 'pagesArray' => $pagesArray, 'currentpage' => $page, 'realestates' => $realestates));                                    
	}
        
        public function contactAboutRealestate(Application $app, $id) {
                 $broker = $app['immotom']->getBrokerByRealeState($id);
                 $idBroker = (int)$broker['broker'];
            
                 $app['session']->set('contactimmotom', array(
                     'idRealeState' => $id 
                 ));

                 return $app->redirect($app['url_generator']->generate('immotom.contact', array('id' => $idBroker)));                
        }
        
        
	public function contact(Application $app, $id) {
                // GET THE BROKER
                $broker = $app['immotom']->getBroker($id);
                
                $session = $app['session']->get('contactimmotom');
                
                if($session['idRealeState'] != null){
                    $session = ($app['session']->get('contactimmotom'));           
                    $idRealeState = (int)$session['idRealeState'];
                    
                    $realestate = $app['immotom']->getRealeStateContact($idRealeState);
                    
                    if((int)$realestate['forsale'] == 1) {
                        $type = "tekoop";
                        $forsale = "Te koop: ";
                    }
                    else{
                        $type = "tehuur";
                        $forsale = "Te huur: ";
                    }
                    $category = $realestate['name'];
                    $address = $realestate['address'];
                    $city = $realestate['city'];
                    
                    $textarea = "**** Zet op deze plaats uw bericht ****  <br />
                                <br />
                                <br />
                                <br />
                                <br />
                                ---------------------------------------------------------------  <br />
                                U contacteert ons over volgende aanbieding:  <br />
                                ID: " . $idRealeState . " <br />
                                Omschrijving: " . $category . " in " . $address . " in " . $city . "";
                }
                else { 
                    $textarea = "";
                    $type = "normal";
                    $realestate = null;
                }
                    
                // Filter form
                $contactform = $app['form.factory']->createNamed('contactform')
                        ->add('name', 'text', array(
                            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 4))),
                            'label' => 'Uw naam'
                        ))
                        ->add('email', 'email', array(
                            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 4))),
                            'label' => 'Uw e-mailadres'
                        ))
                        ->add('text', 'textarea', array(
                            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 4))),
                            'data'  => $textarea,
                            'label' => 'Voer hier uw bericht in'
                        ))
                        ->add('spam', 'text', array(
                            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 4))),
                            'label' => "Spam controle - Voer in dit veld 4 uitroeptekens in:",
                        ))
                    ;
                
                if ('POST' == $app['request']->getMethod()) {
			$contactform->bind($app['request']);

			if ($contactform->isValid()) {
				$data = $contactform->getData();
                                
                                // Check if there are 4 ! in the spam form.
                                if ($data['spam'] == '!!!!'){
                                 
                                    $receiver = $broker['email'];
                                    $name = $data['name'];
                                    $sender = $data['email'];
                                    $subject = "Mail Immo Tom";
                                    $message = strip_tags($data['text']);
                                    $request = $app['request'];

                                    $message = \Swift_Message::newInstance()
                                        ->setSubject($subject)
                                        ->setFrom(array($sender))
                                        ->setTo(array($receiver))
                                        ->setBody("Er is een bericht via de website van Immo Tom" . "\n" .
                                                "Naam: " . $name . "\n" .
                                                "E-mailadres: " . $sender . "\n" .
                                                "Bericht: " . $message . "\n"
                                         );

                                    $app['mailer']->send($message);
                                    
                                    $sended = true;
                                   
                                    return $app['twig']->render('contact.twig', array('broker' => $broker, 'realestate' => $realestate, 'contactform' => $contactform->createView(), 'type' => $type, 'sended' => $sended));                                    
                                }
                                else {
                                    $contactform->get('spam')->addError(new \Symfony\Component\Form\FormError('Gelieve spam veld correct in te vullen!'));
                                    $sended = false;
                                    
                                    return $app['twig']->render('contact.twig', array('broker' => $broker, 'realestate' => $realestate, 'contactform' => $contactform->createView(), 'type' => $type, 'sended' => $sended));                                 
                                }
			}
		}

                $sended = false;
                return $app['twig']->render('contact.twig', array('broker' => $broker, 'realestate' => $realestate, 'contactform' => $contactform->createView(), 'type' => $type, 'sended' => $sended));                                    
	}
}