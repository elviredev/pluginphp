<?php

/**
 * @desc Sert à stocker des données spécifiques à un plugin (clés/valeurs) dans $USER_DATA
 * Définit une ou plusieurs valeurs pour un plugin en fonction de son identifiant.
 * Cette fonction associe une clé ou un tableau de clés/valeurs à un plugin donné,
 * identifié automatiquement via son fichier `config.json`. Les données sont stockées
 * dans la variable globale `$USER_DATA`.
 * @param string|array $key Clé unique (string) ou tableau associatif (clé => valeur)
 * @param mixed $value Valeur associée si $key est une chaîne. Ignoré si $key est un tableau
 * @return bool
 */
function set_value(string|array $key, mixed $value = ''): bool
{
  global $USER_DATA;

  $called_from = debug_backtrace();
  $ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
  $path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

  if (file_exists($path)) {
    $json = json_decode(file_get_contents($path));
    $plugin_id = $json->id;

    if (is_array($key)) {
      foreach ($key as $k => $value) {
        $USER_DATA[$plugin_id][$k] = $value;
      }
    } else {
      $USER_DATA[$plugin_id][$key] = $value;
    }

    return true;
  }

  return false;
}

/**
 * @desc Récupère une valeur stockée pour un plugin en fonction de son identifiant
 * Cette fonction récupère une donnée sauvegardée via `set_value()`,
 * en utilisant le fichier `config.json` du plugin pour déterminer l’identifiant.
 * @param string $key (optionnel) Clé à récupérer.
 *                    Si vide, retourne toutes les données associées au plugin
 * @return mixed Retourne la valeur associée à la clé demandée,
 *                un tableau de toutes les données du plugin si $key est vide,
 *                ou null si la clé ou le plugin n’existe pas.
 */
function get_value(string $key = ''): mixed
{
  global $USER_DATA;

  $called_from = debug_backtrace();
  $ikey = array_search(__FUNCTION__, array_column($called_from, 'function'));
  $path = get_plugin_dir(debug_backtrace()[$ikey]['file']) . 'config.json';

  if (file_exists($path)) {
    $json = json_decode(file_get_contents($path));
    $plugin_id = $json->id;

    if (empty($key)) {
      return $USER_DATA[$plugin_id];
    }

    return !empty($USER_DATA[$plugin_id][$key]) ? $USER_DATA[$plugin_id][$key] : null;
  }

  return null;
}

/**
 * @desc Récupère une valeur de configuration depuis la variable globale $APP
 * @param string|null $key (optionnel)
 *         - Si la clé existe, retourne la valeur associée.
 *         - Si la clé n'existe pas, retourne null.
 *         - Si aucune clé n'est fournie, retourne le tableau complet $APP.
 * @return mixed|null
 */
function APP(?string $key = null): mixed
{
  global $APP;

  if(!empty($key)){
    return !empty($APP[$key]) ? $APP[$key] : null;
  } else {
    return $APP;
  }
}

/**
 * @desc Pour le debugging, permet de voir les plugins chargés sur la page courante
 * @return void
 */
function show_plugins(): void
{
  global $APP;

  // vérifier que $APP['plugins'] existe et si c'est bien un tableau
  $plugins = $APP['plugins'] ?? [];
  $names = is_array($plugins) ? array_column($plugins, 'name') : [];
  dd($names);
}

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

function do_filter(string $hook, mixed $data = ''): mixed
{
  return $data;
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

/**
 * @desc Génère le chemin système absolu vers un fichier d'un plugin
 *
 * Cette fonction utilise `debug_backtrace()` pour identifier le fichier source
 * qui appelle la fonction, puis en déduit le dossier du plugin via
 * `get_plugin_dir()`. Le chemin retourné est un chemin système (filesystem).
 *
 * @param string $path Chemin relatif à partir du répertoire du plugin
 * (par défaut chaîne vide).
 *
 * @return string Chemin absolu sur le système de fichiers vers
 * la ressource du plugin.
 */
function plugin_path(string $path = ''): string
{
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  return get_plugin_dir(debug_backtrace()[$key]['file']) . $path;
}

/**
 * @desc Génère l'URL/chemin HTTP vers un fichier d'un plugin.
 *
 * Cette fonction est similaire à `plugin_path()`,mais elle préfixe le chemin
 * du plugin avec la constante `ROOT`
 * (correspondant à la racine du site en HTTP).
 * Elle est utile pour générer une URL vers des fichiers
 * accessibles publiquement.
 * @param string $path
 * @return string
 */
function plugin_http_path(string $path = ''): string
{
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  return ROOT . DIRECTORY_SEPARATOR . get_plugin_dir(debug_backtrace()[$key]['file']) .$path;
}


/**
 * @desc Permet d'obtenir le dossier du plugin courant
 * @param string $filepath
 * @return string
 */
function get_plugin_dir(string $filepath): string
{
  $path = "";

  $basename = basename($filepath);
  $path = str_replace($basename, '', $filepath);

  if (str_contains($path, DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR)) {
    $parts = explode(DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path);
    $path = 'plugins' .DIRECTORY_SEPARATOR .$parts[1];
  }

  return $path;
}

/**
 * @desc Gérer les permissions utilisateur
 * @param $permission
 * @return true
 */
function user_can($permission)
{
  return true;
}




