<?php
session_start();

use Core\App;

require 'config.php';

$minPHPVersion = '8.0';
if(phpversion() < $minPHPVersion) {
  die("You need a minimum of PHP version $minPHPVersion to run this application.");
}

const DS = DIRECTORY_SEPARATOR;
const ROOTPATH = __DIR__.DS;
require 'app'.DS.'core'.DS.'init.php'; // app/core/init.php

// Si on est en mode DEBUG afficher les errors sinon ne pas les afficher
DEBUG ? ini_set('display_errors', 1) : ini_set('display_errors', 0);

$ACTIONS = [];
$FILTERS = [];
$APP['URL'] = split_url($_GET['url'] ?? 'home'); // $APP['URL'] = ['products', 'new', '1'] ou ['home']

/** load plugins avant que l'application ne démarre **/
// Récupère la liste des plugins installés
$PLUGINS = get_plugin_folders();
// Vérifier s'il y a au moins un plugin
if(!load_plugins($PLUGINS))
{
  die("
    <div style=\"text-align: center; font-family: Tahoma,serif\">
      <h1>Aucun plugin n'a été trouvé! Merci de mettre au moins un plugin dans le dossier plugins</h1>
    </div>
  ");
}

$app = new App();
$app->index();




















