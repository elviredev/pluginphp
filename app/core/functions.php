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
  $found = false;

  foreach($plugin_folders as $folder) {
    // s'il y a au moins un dossier dans la liste, $found est mis à true
    $found = true;
  }

  return $found;
}










