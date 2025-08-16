<?php

/**
 * @desc Découpe l'URL en segments
 * trim() supprime les / au début et à la fin de l'URL
 * explode() coupe la chaîne en morceaux là où il y a un /
 * Résultat pour /products/new/1/ : ['products', 'new', '1']
 * @param $url
 * @return array
 */
function split_url($url): array
{
  return explode('/', trim($url, '/'));
}

/**
 * @desc Récupère une URL depuis la variable globale $APP['URL']
 * Peut retourner une URL spécifique (selon une clé) ou tout le tableau
 * @param string $key
 * @return mixed|string
 */
function URL(string $key = ''): mixed
{
  global $APP;

  if(!empty($key)) {
    if(!empty($APP['URL'][$key])) {
      return $APP['URL'][$key];
    }
  } else {
    return $APP['URL'];
  }

  return '';
}

/**
 * @desc Récupère une liste de dossiers présents dans "plugins/"
 * @return array
 */
function get_plugin_folders(): array
{
  $plugins_folder = 'plugins/';
  $res = [];
  // Scan 'plugins/' et retourne un tableau de tous les fichiers et dossiers qu'il contient
  $folders = scandir($plugins_folder);
  foreach($folders as $folder) {
    // Ignorer '.' et '..'
    if($folder != '.' && $folder != '..' && is_dir($plugins_folder . $folder)) {
      // si l'élément est bien un dossier on l'ajoute au tableau $res
      $res[] = $folder;
    }
  }
  // Retourner le tableau des dossiers trouvés
  return $res;
}

/**
 * @desc Vérifier s'il y a au moins un plugin à charger mais ne charge aucun plugin pour l'instant
 * @param array $plugin_folders
 * @return bool
 */
function load_plugins(array $plugin_folders): bool
{
  $loaded = false;

  foreach($plugin_folders as $folder) {
    // s'il y a au moins un dossier dans la liste, $loaded est mis à true
    $file = 'plugins/' . $folder . '/plugin.php';
    if(file_exists($file)) {
      require $file;
      $loaded = true;
    }

  }

  return $loaded;
}

/**
 * @desc Permet d'ajouter, enregistrer une fonction (ou callback) associée à un nom de hook
 * @param string $hook
 * @param mixed $func
 * @param int $priority
 * @return bool
 */
function add_action(string $hook, mixed $func, int $priority = 10): bool
{
  global $ACTIONS;

  while (!empty($ACTIONS[$hook][$priority])) {
    $priority++;
  }

  $ACTIONS[$hook][$priority] = $func;

  return true;
}

/**
 * @desc Permet d'exécuter les fonctions enregistrées (actions) sur un hook donné
 * @param string $hook
 * @param array $data
 * @return void
 */
function do_action(string $hook, array $data = []): void
{
  global $ACTIONS;

  if (!empty($ACTIONS[$hook])) {
    ksort($ACTIONS[$hook]);
    foreach ($ACTIONS[$hook] as $func) {
      $func($data);
    }
  }
}

function add_filter()
{

}

function do_filter()
{

}

/**
 * @desc Permet d'imprimer du code
 * @param $data
 * @return void
 */
function dd($data): void
{
  echo "<pre><div style='margin: 1px; background-color: #444; color: white; padding: 5px 10px;'>";
  print_r($data);
  echo "</div></pre>";
}

/**
 * @desc Vérifier sur quelle page nous sommes Retourne le 1er élément de l'url courante
 * ex: http://pluginphp.test/products/new/1 -> URL(0) => 'products'
 * @return mixed|string
 */
function page(): mixed
{
  return URL(0);
}

/**
 * @desc Rediriger le navigateur vers une autre page
 * ex: redirect("404") va envoyer une redirection vers http://pluginphp.test/404 et arrêter le script.
 * @param $url
 * @return void
 */
function redirect($url): void
{
  header("Location: " . ROOT . '/' . $url);
  die;
}






