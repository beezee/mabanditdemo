<?php
require './vendor/autoload.php';

$app = new \Slim\Slim();
$strategy = \MaBandit\Strategy\EpsilonGreedy::withExplorationEvery(3);
$persistor = new \MaBandit\Persistence\RedisPersistor();
$bandit = \MaBandit\MaBandit::withStrategy($strategy)
  ->withPersistor($persistor);
try {
  $experiment = $bandit->getExperiment('demo');
} catch(Exception $e) {
  $experiment = $bandit->createExperiment('demo',
    array('blue', 'green', 'red', 'yellow'));
}

$app->get('/', function() use($bandit, $experiment) {
  $val = $bandit->chooseLever($experiment)->getValue();
  echo "<a href='vote/$val'>Vote for $val</a>";
});

$app->get('/vote/:val', function($val) use($bandit, $app) {
  $f = new \MaBandit\Persistence\PersistedLever($val, 'demo');
  $l = $bandit->getPersistor()->loadLever($f);
  $bandit->registerConversion($l);
  $app->redirect('/');
});

$app->run();
