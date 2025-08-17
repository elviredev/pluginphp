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
 * @param int|string $key
 * @return mixed|string
 */
function URL(int|string $key = ''): mixed
{
  global $APP;
  // Si on demande un index particulier
  if ($key !== '' && isset($APP['URL'][$key])) {
    return $APP['URL'][$key]; // retourne directement la string
  }

  // Si pas de clé -> renvoyer tout le tableau
  return $APP['URL'];
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
 * @desc Charge dynamiquement les plugins actifs d’un projet.
 * @param array $plugin_folders Liste des dossiers de plugins à analyser
 * @return bool TRUE si au moins un plugin a été chargé, FALSE sinon.
 */
function load_plugins(array $plugin_folders): bool
{
  global $APP;
  $loaded = false;

  // Parcourt chaque dossier de plugin fourni
  foreach($plugin_folders as $folder) {
    $file = 'plugins/' . $folder . '/config.json';

    // vérifier si config.json existe
    if(file_exists($file)) {
      // transform json string en object
      $json = json_decode(file_get_contents($file));
      // vérifier si c'est bien un object et si un id unique est défini
      if(is_object($json) && isset($json->id)) {
        // vérifier que le plugin est marqué comme actif (`active = true`)
        if (!empty($json->active)) {
          $file = 'plugins/' . $folder . '/plugin.php';
          // s'assurer que plugin.php existe avant de continuer et que la route actuelle est valide
          if(file_exists($file) && valid_route($json)) {
            // si file existe, ajouter des métadonnées utiles au plugin et l'ajouter dans `$APP['plugins']`
            $json->index_file = $file;
            $json->path = 'plugins/' . $folder . '/';
            $json->http_path = ROOT . '/' . $json->path;

            $APP['plugins'][] = $json;
          }
        }
      }
    }
  }

  // charger les plugins
  if(!empty($APP['plugins'])) {
    foreach($APP['plugins'] as $json) {
      if(file_exists($json->index_file)) {
        // Inclut chaque fichier `plugin.php` trouvé pour exécuter le code des plugins
        require $json->index_file;
        $loaded = true;
      }
    }
  }

  return $loaded;
}

/**
 * @desc Vérifie si un plugin est autorisé à s’exécuter sur la page courante.
 * @param object $json L’objet JSON décodé du fichier `config.json` d’un plugin.
 * @return bool Retourne TRUE si le plugin est autorisé à s’exécuter sur la page en cours, FALSE sinon.
 */
function valid_route(object $json): bool
{
  // Vérifie la liste des routes interdites
  if (!empty($json->routes->off) && is_array($json->routes->off)) {
    // si la page courante est dans le tableau "off", le plugin est désactivé
    if (in_array(page(), $json->routes->off))
      return false;
  }

  // Vérifie la liste des routes autorisées
  if (!empty($json->routes->on) && is_array($json->routes->on)) {
    // Si la valeur `"all"` est présente, le plugin est actif sur toutes les pages.
    if ($json->routes->on[0] == 'all')
      return true;
    // Si la page courante figure dans `routes.on`, le plugin est actif.
    if (in_array(page(), $json->routes->on))
      return true;
  }

  // Si aucune condition n’est remplie, le plugin n’est pas chargé.
  return false;
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
 * @desc Vérifier sur quelle page nous sommes
 * Retourne le premier segment de l’URL (ou une chaîne vide si inexistant)
 * ex: http://pluginphp.test/products/new/1 -> URL(0) => 'products'
 * @return string
 */
function page(): string
{
  return URL(0) ?? '';
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






