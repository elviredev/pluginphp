# 📦 Système de Plugins PHP (Partie 2)

- vidéo #12 : https://www.youtube.com/watch?v=PpKYknosJGU

### Variable data

- Créer une fonction `APP()` à la place d'utiliser la variable $APP['URL'] qui récupère l'URL courante sous sa forme splitée.

```php
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
```

- Pour voir ce que contiens cette fonction, on l'exécute dans le plugin "basic-auth/plugin.php"

```php
add_action('view', function(){
  dd(APP());
});
```

- Retourne l'application: http://pluginphp.test/home

```php
Array
(
  [URL] => Array
    (
        [0] => home
    )

  [plugins] => Array
    (
      [0] => stdClass Object
        (
          [name] => Basic Authentication
          [id] => basic-auth
          [author] => 
          [thumbnail] => thumbnail.jpg
          [active] => 1
          [index] => 1
          [routes] => stdClass Object
            (
              [on] => Array
                (
                    [0] => all
                )

              [off] => Array
                (
                )

            )

          [index_file] => plugins/basic-auth/plugin.php
          [path] => plugins/basic-auth/
          [http_path] => http://pluginphp.test/plugins/basic-auth/
        )

    [1] => stdClass Object
      (
        [name] => Header Footer
        [id] => header-footer
        [author] => 
        [thumbnail] => thumbnail.jpg
        [active] => 1
        [index] => 1
        [routes] => stdClass Object
          (
            [on] => Array
              (
                  [0] => all
              )

            [off] => Array
              (
              )
          )

          [index_file] => plugins/header-footer/plugin.php
          [path] => plugins/header-footer/
          [http_path] => http://pluginphp.test/plugins/header-footer/
      )
    )
)
```

- Si on veut obtenir l'URL courante on lui fournit une clé 'URL'

```php
add_action('view', function(){
  dd(APP('URL'));
});
```

- Résultat:

```php
Array
(
    [0] => home
)
```

- Créer une fonction `show_plugins()` car parfois ou voudra savoir quels plugins sont chargés dans notre parge courante et c'est bon pour le debugging

```php
/**
 * @desc Pour le debugging, permet de voir les plugins chargés sur la page courante 
 * @return void
 */
function show_plugins(): void
{
  global $APP;
  dd($APP['plugins'] ?? []);
}
```

```php
// basic-auth/plugin
show_plugins(); // affiche les plugins chargés sur la page courante
add_action('view', function(){
  
});

add_action('controller', function(){

});
```

- On a pas besoin de toutes les infos incluses dans chaque plugin, on veut juste le nom du plugin

```php
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
```

```php
// résultat sur la page http://pluginphp.test/home
Array
(
  [0] => Basic Authentication
  [1] => Header Footer
  [2] => Home Page
)
```

- Les fonctions `get_value()` et `set_value()`
- On souhaite définir une valeur dans le "controller" qui s'exécutera toujours en 1er et la recevoir dans "view" pour l'afficher
- par exemple :

```php
// basic-auth/plugin
add_action('view', function(){
  echo $name;
});

add_action('controller', function(){
  $name = "John";
});
```

- quand on execute cela, on a une erreur "_Undefined variable $name_" ici dans la view `echo $name;`
- même si on a définit cette variable dans le controller qui s'exécute en 1er, elle sera undefined au niveau de la vue car ce sont des fonctions isolées qui ont leur propre scope, leur propre portée locale donc les variables ne sont pas disponibles endehors de la fonction

- on va créer une fonction `set_value()` et utiliser une variable globale à la place
- cette fonction va retourner un boolean pour dire si on a définit une valeur ou non
- on créé une variable globale `$USER_DATA` qu'il faut définir dans `index.php` comme ceci : `$USER_DATA = [];`

```php
// functions.php
function set_value(string|array $key, mixed $value = ''): bool
{
  global $USER_DATA;

  $USER_DATA[$key] = $value;

  return true;
}
```

- pour la fonction `get_value()`, c'est juste une instruction qui dit que si $USER_DATA[$key] existe alors on la retourne sinon on retourne null

```php
function get_value(string $key): mixed
{
  global $USER_DATA;

  return !empty($USER_DATA[$key]) ? $USER_DATA[$key] : null;
}
```

- On teste

```php
add_action('view', function(){
  echo get_value('name'); // John
});

add_action('controller', function(){
  set_value('name', "John");
});
```

- ça fonctionne "John" s'affiche bien http://pluginphp.test/products
- cependant, on peut faire mieux que ça car si on a un tableau de clé/valeur comme ceci :

```php
add_action('view', function(){
  dd(get_value('data'));
});

add_action('controller', function(){
  $arr = ['name' => 'John', 'age' => 30];
  set_value('data', $arr);
});
```

- Affiche :

```php
Array
(
  [name] => John
  [age] => 30
)
```

- ce tableau est enregistré avec une clé "data" mais que se passe t-il si je veux fournir juste le tableau et récupérer une seule clé

```php
add_action('view', function(){
  dd(get_value('age'));
});

add_action('controller', function(){
  $arr = ['name' => 'John', 'age' => 30];
  set_value($arr);
});
```

- on a une erreur "_Cannot access offset of type array on array_"
- on modifie notre fonction `set_value()` et on vérifie si c'est un array dans ce cas on boucle sur chaque clé pour renvoyer la valeur correspondante

```php
function set_value(string|array $key, mixed $value = ''): bool
{
  global $USER_DATA;

  if (is_array($key)) {
    foreach ($key as $k => $value) {
      $USER_DATA[$k] = $value;
    }
  } else {
    $USER_DATA[$key] = $value;
  }

  return true;
}
```

- Ce code fonctionne si on a un seul plugin dans toute l'application mais si on a une clé du même nom comme 'name' par exemple dans un autre plugin cela va écraser le 1er donc c'est un gros problème notamment si les plugins sont créés par des personnes différentes qui peuvent utiliser les mêmes noms de variables
- Pour remédier à cela, il faut s'assurer qu'on sauvegarde bien les variables de chaque plugin dans un emplacement séparé
- Dans `index.php`, on va mettre une clé supplémentaire dans `$USER_DATA = [];`afin de pouvoir enregistrer chaque plugin dans un endroit différent
- On va se référer au fichier config.json qui contient un "id" unique pour chaque plugin
- Modifier la fonction get_value() car la clé peut ne pas exister

```php
function get_value(string $key = ''): mixed
{
  global $USER_DATA;

  if(empty($key)) {
    return $USER_DATA;
  }
  
  return !empty($USER_DATA[$key]) ? $USER_DATA[$key] : null;
}
```

- tester sans fournir de key

```php
add_action('view', function(){
  dd(get_value());
});

add_action('controller', function(){
  $arr = ['name' => 'John', 'age' => 30];
  set_value($arr);
});
```

- affiche le tableau entier de données

```php
Array
(
  [name] => John
  [age] => 30
)
```

- Maintenant modifier la fonction pour qu'elle soit unique à chaque plugin
- il nous faut savoir dans quel plugin on se trouve, pour cela on utilise le bout de code qu'on avait utilisé dans plugin_dir()

```php
function set_value(string|array $key, mixed $value = ''): bool
{
  global $USER_DATA;

  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  $path = get_plugin_dir(debug_backtrace()[$key]['file']);
  echo $path; // plugins\basic-auth\
  die;

  if (is_array($key)) {
    foreach ($key as $k => $value) {
      $USER_DATA[$k] = $value;
    }
  } else {
    $USER_DATA[$key] = $value;
  }

  return true;
}
```

- Je veux le fichier de config donc on concatène `$path = get_plugin_dir(debug_backtrace()[$key]['file']) . 'config.json';` et on a bien "plugins\basic-auth\config.json"
- si le fichier existe, on le charge
- on récupère le json et on veut l'id qui détermine où on va sauvegarder les données
- on boucle sur USER_DATA en ajoutant l'id du plugin donc ces informations seront enregistrées dans l'id du plugin

```php
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
```

- Modifier aussi get_value()

```php
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
```

- Test

```php
add_action('view', function(){
  dd(get_value());
});

add_action('controller', function(){
  $arr = ['name' => 'John', 'age' => 30];
  set_value($arr);
});
```

- Affiche

```php
Array
(
  [name] => John
  [age] => 30
)
```

- tout fonctionne correctement, les données sont bien renvoyées
- on va voir que si on met les mêmes noms de variables dans un autre plugin il n'y aura pas ecrasement

```php
// header-footer/plugin
add_action('controller', function () {
  $arr = ['name' => 'Mary', 'age' => 32];
  set_value($arr);
});

add_action('after_view', function () {
  echo "<center>Website | Copyright 2025</center>";
});

add_action('view', function () {
  dd(get_value());
});

add_action('before_view', function () {
  echo "<center><div><a href=''>Home</a> . About us . Contact us</div></center>";
});
```

- Affiche bien les données, l'intégrité des données est intacte

```php
Array
(
  [name] => John
  [age] => 30
)
Array
(
  [name] => Mary
  [age] => 32
)
```

### Database class
