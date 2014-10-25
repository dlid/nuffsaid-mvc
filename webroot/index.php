<?php

require __DIR__.'/config_with_app.php'; 

$app->theme->configure(ANAX_APP_PATH . 'config/theme_nuffsaid.php');
$app->navbar->configure(ANAX_APP_PATH . 'config/navbar_nuffsaid.php');
  
$app->url->setUrlType(\Anax\Url\CUrl::URL_CLEAN);
$app->session(); 

$di->set('form', 'Mos\HTMLForm\CForm');

$app->views->addString('<p>Spelaihop är sidan där spelintresserade kan ställa frågor om alla möjliga spel och få svar.</p>', 'footer-col-1');


// Include database support
$di->setShared('db', function() {
    $db = new \Mos\Database\CDatabaseBasic();
    $db->setOptions(require ANAX_APP_PATH . 'config/database_sqlite.php');
    $db->connect();
    return $db;
});


$app->router->add('', function() use ($app, $di) {
  $app->theme->setTitle("Start");
	$app->views->add('shared/page', [
	    'content' => "innehåll",
	    'byline' => "byline",
      'sidebar' => "sidebar"
	]);
});


$app->router->add('questions', function() use ($app, $di) {
  $app->theme->setTitle("Frågor");
  $app->views->add('shared/page', [
      'content' => "frågor",
      'byline' => "byline",
      'sidebar' => "sidebar"
  ]);
});


$app->router->add('tags', function() use ($app, $di) {
  $app->theme->setTitle("Taggar");
  $app->views->add('shared/page', [
      'content' => "tagar",
      'byline' => "byline",
      'sidebar' => "sidebar"
  ]);
});

if( $di->request->getGet(null) == "show_grid" ) {
  $app->theme->addStylesheet('css/void-base/show-grid.css');
}


 
$app->router->handle();
$app->theme->render();