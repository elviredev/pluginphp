# 📦 Système de Plugins PHP (Partie 1)

## Plugin based PHP MVC Framework from scratch
- Créer un `framework PHP MVC` utilisant des **plugins** pour nous aider à démarrer plus rapidement de nouveaux projets PHP, sans avoir à tout recommencer de zéro. 
- Il combine les avantages de **WordPress** et de frameworks légers comme **CodeIgniter**, créant ainsi un framework MVC utilisant des plugins.

### Projet utilisant Laragon : pluginphp

## Structure du projet
- Création structure du projet : 
  - ###### les dossiers :
    - **app**
    - **assets**
    - **plugins**
    - **uploads**
  - ###### les fichiers :
    - **config.php**
    - **index.php**
    - **thunder** : pour CLI ligne de commande

### Le dossier "app"
- Contenu :
  - les dossiers dans "app" :
    - **core**
    - **models**
    - **thunder**
- Le dossier "core" :
  - classe `App.php` : c'est ici que se trouve l'application entière. Tout s'exécutera à partir de cette classe. Normalement, on devrait créer un contrôleur par page (MVC) mais on ne le fait pas ici. Nous allons charger le contenu directement dans cette classe `App` mais en utilisant des **plugins**
  - fichier `functions.php`
  - fichier `init.php`

### Le dossier "plugins"
- Contenu : 
  - les dossiers dans "plugins" :
    - **basic-auth**
    - **header-footer**
    - **home-page**

```markdown
# 📂 Structure du projet

📦 projet
├── 📂 app/ # cœur de l'application
│ ├── 📂 core/ # fichiers principaux (App, init, fonctions)
│ │ ├── 📄 App.php
│ │ ├── 📄 functions.php
│ │ └── 📄 init.php
│ ├── 📂 models/ # modèles (entités, logique métier)
│ └── 📂 thunder/ # module spécifique Thunder
│ └── 📄 .htaccess
├── 📂 assets/ # ressources (CSS, JS, images)
├── 📂 plugins/ # plugins installés
│ ├── 📂 basic-auth/ # plugin d'authentification basique
│ │ └── 📄 plugin.php
│ ├── 📂 header-footer/# plugin d'en-tête/pied de page
│ │ └── 📄 plugin.php
│ ├── 📂 home-page/ # plugin de page d'accueil
│ │ └── 📄 plugin.php
│ └── 📄 .htaccess
├── 📂 upload/ # fichiers uploadés par l’utilisateur
├── 📄 .gitignore # ignore fichiers Git
├── 📄 .htaccess # règles Apache
├── 📄 config.php # configuration globale
├── 📄 index.php # point d’entrée principal
└── 📂 thunder/ # répertoire du framework Thunder
```

### Les fonctions dans functions.php
- function `split_url($url)`
  - Découpe l'URL en segments
- function `URL(string $key = '')`
  - Récupère une URL depuis $APP['URL'] et peut retourner une URL spécifique ou tout le tableau
- function `get_plugin_folders()`
  - Récupère une liste de dossiers présents dans "plugins/"
- function `load_plugins(array $plugin_folders)`
  - Vérifier s'il y a au moins un plugin à charger mais ne charge aucun plugin pour l'instant

### Les fichier .htaccess pour Apache
- Souvent utilisé pour les URLs propres et la gestion des accès.
- Permet la réécriture d'URL et la protection des dossiers app, plugins et à la racine du projet
- Placer un .htaccess dans "app" pour refuser l'accès à tous les fichiers
- Placer un .htaccess dans "plugins" pour refuser l'accès à tous les fichiers sauf index.php, les fichiers CSS, JS et les images
- Placer un .htaccess à la racine du projet pour refuser l'accès au fichier CLI de Thunder ou plutôt rediriger l'accès vers index.php, pour accepter le chargement des fichiers et répertoires réels et rediriger les autres URL vers index.php

### Les fonctions do_action() et add_action(), add_filter() et do_filter()
- function `add_action(string $hook, mixed $func)` permet d'ajouter, enregistrer une fonction (ou callback) associée à un nom de hook
  - un `hook` est une sorte de clé pour nous dire quand exécuter quelque chose, donc nous allons attendre un hook qui sera de type string
  - une **action** est en fait une fonction que nous ajoutons donc on passe en paramètre `$func` donc cette fonction va ajouter l'action que nous allons exécuter un peu plus tard et le `$hook` va nous dire quand exécuter cette action plus tard dans la fonction do_action()
  - on a besoin d'appeler la variable globale `$ACTIONS` et on va ajouter une action dans ce tableau. On va stocker la fonction dans ce tableau associatif `$ACTIONS[$hook] = $func;`

- function `do_action(string $hook, array $data = [])` permet d'exécuter les fonctions enregistrées (actions) sur un hook donné
  - on doit récupérer la même action qu'on a ajouter à un moment donné et l'exécuter donc on a besoin du $hook et des données quand on en a. $data est un tableau optionnel de données à passer à la fonction callback
  - `global $ACTIONS` : Récupère le registre des hooks.
  - on boucle pour rechercher cet élément particulier ($hook) dans les actions et s'il existe on l'exécute : `foreach($ACTIONS as $key => $func)` parcourt tous les hooks enregistrés.
  - `$func($data);` : Appelle la fonction en lui passant $data.

- Dans la classe `App.php`
  - on appelle `do_action` et on lui passe le hook qu'on nomme `"controller"` et elle s'exécutera donc dans la partie contrôleur
  - on appelle `do_action` avec un 2ème hook qu'on nomme `"view"` et elle s'exécutera donc dans la partie vue

- On doit maintenant charger réellement des plugins 9:30
- Pour qu'on puisse qualifier quelque chose de plugin, on a besoin de 2 fichiers : un fichier de `configuration` et le fichier `plugin.php`

- Dans **basic-auth** on créé un fichier `plugin.php`

```php
<?php
dd("This is the auth plugin");
```
- Modifier la fonction `load_plugins()` afin qu'elle puisse charger réellement le fichier plugin.php
- Dans **header-footer** et dans **home-page** on créé un fichier `plugin.php` 
- Les fichiers plugin.php présents dans chaque dossier de "plugins" peuvent se charger

- On va afficher les vues dans `plugin.php` en utilisant `add_action()` pour pouvoir spécifier quand les choses s'exécutent dans ce plugin
- Ici, on veut que add_action s'exécute sur la partie Vue et on en ajoute une autre action qui sera sur la partie controller

```php
// plugin.php
add_action('view', function(){
  dd('This is from the view hook');
});

add_action('controller', function(){
  dd('This is from the controller hook');
});
```

- On a besoin de plus d'actions que cela

```php
// App.php
class App
{
  public function index(): void
  {
    // le contrôleur (hook "controller") prépare les données nécessaires à la vue
    do_action("before_controller");
    do_action("controller");
    do_action("after_controller");
    
    // before_view et after_view entourent l’affichage de la vue, pour insérer par exemple du HTML commun, du debug, etc.
    do_action("before_view");

    // ob_get_contents() récupère le contenu actuellement dans le tampon de sortie (output buffer)
    // $before_content : ce qu’il y a dans le tampon avant que la vue soit exécutée.
    // $after_content : ce qu’il y a dans le tampon après que la vue ait été exécutée.
    // Donc, si la vue n’affiche rien, la différence entre $after_content et $before_content sera vide.
    $before_content = ob_get_contents();
    // exécute la vue (génère du HTML)
    do_action("view");
    $after_content = ob_get_contents();

    do_action("after_view");
    
    // vérifier si la vue génère du contenu
    // vérifier si $after_content a changé par rapport à $before_content
    if(str_replace($before_content, "", $after_content) == "") { // enlève du contenu final ce qui existait avant la vue.
    // Si ça donne une chaîne vide, c’est que la vue n’a rien ajouté.
    // Si rien n’a été ajouté et que la page demandée n’est pas déjà '404' → on redirige vers la page 404.
      if (page() != '404') {
        redirect('404');
      }
    }
  }
}
```

### Priorité des fonctions
- Le dernier plugin a être chargé est le seul qui sera exécuté, il écrase les autres. 
- On doit pouvoir permettre plusieurs fonctions par hook. Pour corriger cela, on doit ajouter les actions au tableau au lieu d'écraser donc on ajoute [] à add_action() et on doit alors boucler dans do_action() pour récupérer toutes les fonctions qui sont à l'intérieur du tableau

```php
// functions.php
function add_action(string $hook, mixed $func): bool
{
  global $ACTIONS;
  $ACTIONS[$hook][] = $func; // Ici

  return true;
}

function do_action(string $hook, array $data = []): void
{
  global $ACTIONS;

  if (!empty($ACTIONS[$hook])) {
    foreach ($ACTIONS[$hook] as $func) {
      $func($data);
    }    
  }
}
```

- Désormais, tous les plugins s'affichent. Tous les contrôleurs s'exécutent en premier et les vues en dernier car dans App.php on commence par le contrôleur et ensuite la vue
- Pour l'instant, l'ordre d'exécution des plugins est "basic-auth", "header-footer" et "home-page". Si on veut que "home-page" s'exécute en premier par exemple on doit pouvoir choisir l'ordre de priorité d'exécution, on appelle cela, les niveaux de priorité.
- on ajoute un niveau de priorité `int $priority = 10` (moyenne donc 0 est la priorité la plus basse et 20 la plus haute). Ce qui veut dire qu'un niveau de priorité de 7, 8 ou 10 s'exécutera avant un niveau de priorité de 15 ou 20 par exemple.

```php
// functions.php
function add_action(string $hook, mixed $func, int $priority = 10): bool
{
  global $ACTIONS;

  while (!empty($ACTIONS[$hook][$priority])) {
    $priority++;
  }

  $ACTIONS[$hook][$priority] = $func;
  dd($ACTIONS);
  return true;
}
```

- par exemple, si on veut que le controller basic-auth s'exécute en dernier (pour l'instant, il s'exécute en premier), on lui ajoute une priority de 20

```php
// basic-auth
add_action('view', function(){
  dd('This is from the view hook in basic-auth plugin');
});

add_action('controller', function(){
  dd('This is from the controller hook in basic-auth plugin');
}, 20); // Ici
```

- dans do_action(), on fait un tri par clé en utilisant ksort()
```php
// functions.php
function do_action(string $hook, array $data = []): void
{
  global $ACTIONS;

  if (!empty($ACTIONS[$hook])) {
    ksort($ACTIONS[$hook]); // Ici
    foreach ($ACTIONS[$hook] as $func) {
      $func($data);
    }
  }
}
```

- test : http://pluginphp.test/
- le hook "controller" de "basic-auth" s'exécute bien en dernier et le hook "view" est exécuté en premier car il a le niveau de priorité par défaut de 10

- On veut par exemple que "home-page" controller vienne en premier, on peut lui mettre un niveau à 8 car les autres commencent à 10. On enlève pour l'instant le niveau de priorité de 20 qu'on avait mis à "basic-auth"

```php
//home-page
add_action('view', function () {
  dd('This is from the view hook in home-page plugin');
});

add_action('controller', function () {
  dd('This is from the controller hook in home-page plugin');
}, 8); // Ici
```

- On enlève tous les niveaux de priorité pour l'instant
- On va voir comment tout cela fonctionne en utilisant un vrai template HTML

### Homepage
- dans "home-page" on supprime le contenu du controller car on ne veut pas afficher du texte dans le contrôleur sur cette page
- dans "basic-auth", on supprime le contenu des 2 hooks
- dans "header-footer", il faudrait ajouter le "header" dans le hook "before_view" et notre footer dans le hook "after_view"

```php
// header-footer plugin.php
add_action('after_view', function () {
  echo "<center>Website | Copyright 2025</center>";
});

add_action('before_view', function () {
  echo "<center><div><a href=''>Home</a> . About us . Contact us</div></center>";
});
```

- Ajoutons un formulaire dans le hook "view"

```php
// header-footer plugin
add_action('after_view', function () {
  echo "<center>Website | Copyright 2025</center>";
});

add_action('view', function () {
  echo "<form style='width: 400px; margin: 40px auto; text-align: center; '>
          <h4>Login</h4>
          <input placeholder='email'><br>
          <input placeholder='password'><br>
          <button>Login</button>
        </form>";
});

add_action('before_view', function () {
  echo "<center><div><a href=''>Home</a> . About us . Contact us</div></center>";
});
```

- si je veux que le plugin "home-page" s'exécute avant le plugin "header-footer", je donne un niveau de priorité < à 10

```php
// home-page plugin
add_action('view', function () {
  dd('This is from the view hook in home-page plugin');
}, 9);

add_action('controller', function () {

});
```

- Si je veux envoyer une requête POST en cliquant sur le bouton "Login", j'aurai besoin d'un controller
- Dans le plugin "header-footer" pou j'ai mon formulaire, il suffit d'ajouter un hook "controller" et celui-ci s'exécutera avant les autres actions "before_view", "view" et "after_view"

```php
// header-footer plugin

add_action('controller', function () {
  dd($_POST);
});

add_action('after_view', function () {
  echo "<center>Website | Copyright 2025</center>";
});

add_action('view', function () {
  echo "<form method='POST' style='width: 400px; margin: 40px auto; text-align: center; '>
          <h4>Login</h4>
          <input placeholder='email' name='email'><br>
          <input placeholder='password' name='password'><br>
          <button>Login</button>
        </form>";
});

add_action('before_view', function () {
  echo "<center><div><a href=''>Home</a> . About us . Contact us</div></center>";
});
```

- Nous allons pas mettre tout ce contenu dans le plugin "header-footer".
- On va créer des fichiers séparés pour le controller et pour les vues et importer ces fichiers dans `plugin.php` pour garder ce dernier le plus propre possible

### Routing

- pour l'instant nos plugins actifs s'exécutent sur toutes les pages. Nous allons voir comment exécuter un plugin uniquement où on en a besoin grâce à un système de routing.
- On aura besoin d'un fichier JSON qui contiendra des informations sur chaque plugin afin de savoir quand charger un plugin spécifique.
- Dans les dossiers "header-footer", "basic-auth" et "home-page" on créé un fichier `config.json` 
- les infos sont le `name` (nom lisible du plugin), `id` (Identifiant unique du plugin), `active` qui sera à true ou false selon que le plugin est actif ou désactivé, `index` qui détermine l'ordre de chargement du plugin en entier donc c'est différent du niveau de priorité vu précédemment qui détermine l'ordre d'exécution des fonctions controller ou view.
- On peut ajouter d'autres éléments comme "author" (Auteur du plugin), "thumbnail" (Image de prévisualisation) etc
- Les `routes` pour déterminer quelles routes seront actives ou pas : 
  - pour les plugins "basic-auth" et "header-footer", `"on": ["all"]` : s'exécute sur toutes les pages
  - pour le plugin "home-page", `"on": ["home"]` : s'exécute sur la homepage. 


```json
{
  "name": "Basic Authentication",
  "id": "basic-auth",
  "author": "",
  "thumbnail": "thumbnail.jpg",
  "active": true,
  "index": 1,
  "routes": {
    "on": ["all"],
    "off": []
  }
}
```

```json
{
  "name": "Home Page",
  "id": "home-page",
  "author": "",
  "thumbnail": "thumbnail.jpg",
  "active": true,
  "index": 1,
  "routes": {
    "on": ["home"],
    "off": []
  }
}
```

- On doit collecter ces informations avant de charger un plugin, puis les lire pour obtenir des instructions sur la façon de gérer ce plugin spécifique. Pour cela on doit modifier la fonction `load_plugins()`, créer une fonction `valid_route()` pour valider si un plugin est autorisé à s'exécuter sur la page courante

```php
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
```

- `valid_route()`

```php
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
```

- Modifier les fonctions `page()` qui doit renvoyer une string et non pas un tableau `URL()` pour qu'elle retourne un tableau de segments de l'URL courante

```php
// URL()
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

// page()
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
```

#### Exemple avec URL: http://pluginphp.test/home
- URL() → ['home']
- URL(0) → "home"
- page() → "home" ✅


#### Exemple de fonctionnement du routing
- URL courante : /home
- page() → "home"

##### Cas 1 : dans le config.json du plugin
- ✅ Le plugin est chargé uniquement sur /home.
```php
"routes": { "on": ["home"], "off": [] }
```

##### Cas 2:
- ✅ Le plugin est chargé sur toutes les pages.
```php
"routes": { "on": ["all"], "off": [] }
```

##### Cas 3:
- ✅ Chargé sur /home et /about
- ❌ Pas chargé sur /admin
```php
"routes": { "on": ["home", "about"], "off": ["admin"] }
```

### Get plugin dir
- On a besoin maintenant de pouvoir charger des fichiers et pour cela on doit récupérer le chemin actuel du plugin
- Par exemple, on a le plugin "home-page" qui s'exécute et on doit savoir dans quel dossier ce plugin se trouve sans avoir a spécifier le dossier courant donc si on change par exemple le dossier dans lequel se trouve ce plugin, on doit toujours pouvoir savoir d'où il vient.
- Par exemple, on veut voir le dossier actuel du fichier plugin.php qui est "home-page"

```php
echo __DIR__; // D:\laragon\www\pluginphp\plugins\home-page

add_action('view', function () {
  dd('This is from the view hook in home-page plugin');
});

add_action('controller', function () {

});
```

- on a aussi la super globale `__FILE__` qui permet de récupérer le fichier actuel

```php
echo __FILE__; // D:\laragon\www\pluginphp\plugins\home-page\plugin.php
```

- on veut récupérer le dossier **"plugins"**
- cela est utile par exemple quand on veut charger des fichiers CSS dans mon dossier de plugin actuel. En récupérant "plugins" je sais que des fichiers CSS peuvent se trouver dans un dossier de plugin ("home-page" pr exemple ou "home-page/css/") donc tout ce qu'on a à faire est de récupérer le dossier "plugins/css" pour récupérer les fichiers css. Et cela va fonctionner même si on change le dossier de plugin "home-page" par exemple.

- Comme on utilise un système Windows, le "/" est "\" donc on doit corriger cela pour que le chemin soit dynamique pour s'ajuster en fonction du système d'exploitation utilisé. Pour cela on va créer une fonction `get_plugin_dir()`

- `basename` récupère le dernier élément du chemin par exemple ici "D:\laragon\www\pluginphp\plugins\home-page\plugin.php", `basename` correspond à `plugin.php`
- donc on récupère ce basename et on le remplace par une chaîne vide de cette manière on obtiendra cette partie là "D:\laragon\www\pluginphp\plugins\home-page\"

```php
function get_plugin_dir(string $filepath): string
{
  $path = "";

  $basename = basename($filepath);
  $path = str_replace($basename, '', $filepath);

  return $path;
}
```

```php
echo get_plugin_dir(__FILE__); // D:\laragon\www\pluginphp\plugins\home-page\
```

- cependant, on ne veut pas obtenir tout ce chemin "D:\laragon\www\pluginphp\"
- on va créer 2 fonctions, l'une aura le chemin avec HTTP et l'autre non
- on veut le chemin relatif qui est celui-ci "plugins\home-page\" au final donc on a pas besoin de cette partie "D:\laragon\www\pluginphp\" donc on va la supprimer en explosant le path et en récupérant 2 éléments : "D:\laragon\www\pluginphp" et "\home-page\", on sait que "\plugins\" sera toujours présent dans les chemins 
- on utilise la fonction php `strstr($path, '\plugins\')` qui permet de chercher un élément dans une string donc ici dans notre path on cherche la partie '\plugins\'
- On peut aussi utiliser `str_contains($path, '\plugins\')`
- si on trouve "\plugins\" dans le chemin, on va exploser ce dernier `$parts = explode(DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path);` en 2 parties donc " D:\laragon\www\pluginphp" et "home-page\"
- pour pouvoir obtenir ce qu'on veut c'est à dire cette partie là **"plugins\home-page\"**, on fait `$path = 'plugins' .DIRECTORY_SEPARATOR .$parts[1];`, $parts[1] vaut "home-page\"

```php
function get_plugin_dir(string $filepath): string
{
  $path = "";

  $basename = basename($filepath);
  $path = str_replace($basename, '', $filepath);

  if (strstr($path, DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR)) {
    $parts = explode(DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path);
    $path = 'plugins' .DIRECTORY_SEPARATOR .$parts[1];
  }

  return $path;
}
```

```php
echo get_plugin_dir(__FILE__); // plugins\home-page\
```

- on aura besoin de 2 autres fonctions `plugin_dir()` pour obtenir le chemin absolu du dossier du plugin courant ("D:\laragon\www\pluginphp\plugins\header-footer) et `plugin_http_dir()` car les images ou les fichiers css par exemple doivent passer par le serveur pour fonctionner donc par un chemin HTTP 

- si on fait ceci, on obtiendra pas notre fichier plugin.php car la fonction plugin_dir() se trouve dans le dossier app/functions.php et ce que remontera le retour de la fonction du fait de `__FILE__`

```php
function plugin_dir()
{
  return get_plugin_dir(__FILE__); // D:\laragon\www\pluginphp\app\core\
}
```

- pour obtenir le chemin absolu suivant "D:\laragon\www\pluginphp\plugins\header-footer\plugin.php", on va passer par la fonction `debug_backtrace()` qui permet de voir ce que le système PHP fait pour remonter jusqu'au chemin absolu du fichier dans lequel on se trouve 

```php
function plugin_dir()
{
  return debug_backtrace();
  return get_plugin_dir(__FILE__);
}
```

- pour pouvoir voir le résultat, mettre un return ici dans la fonction get_plugin_dir()

```php
function get_plugin_dir(string $filepath): string
{
  $path = "";

  $basename = basename($filepath);
  return $path = str_replace($basename, '', $filepath); // Ici

  if (str_contains($path, DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR)) {
    $parts = explode(DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR, $path);
    $path = 'plugins' .DIRECTORY_SEPARATOR .$parts[1];
  }

  return $path;
}
```

- on obtient ceci, toutes les étapes du système PHP, il passe en premier par :
    - "D:\laragon\www\pluginphp\index.php", 
    - ensuite par "D:\laragon\www\pluginphp\app\core\functions.php" 
    - et enfin on obtient "D:\laragon\www\pluginphp\plugins\header-footer\plugin.php"

```php
Array
(
  [0] => Array
    (
      [file] => D:\laragon\www\pluginphp\plugins\header-footer\plugin.php
      [line] => 3
      [function] => plugin_dir
      [args] => Array
        (
        )
    )

  [1] => Array
    (
      [file] => D:\laragon\www\pluginphp\app\core\functions.php
      [line] => 97
      [args] => Array
        (
            [0] => D:\laragon\www\pluginphp\plugins\header-footer\plugin.php
        )

      [function] => require
    )

  [2] => Array
    (
      [file] => D:\laragon\www\pluginphp\index.php
      [line] => 28
      [function] => load_plugins
      [args] => Array
        (
          [0] => Array
            (
              [0] => basic-auth
              [1] => header-footer
              [2] => home-page
            )

      )
    )
)
```

- pour récupérer le 1er élément du tableau donc ceci "D:\laragon\www\pluginphp\plugins\header-footer\plugin.php", on peut faire :

```php
function plugin_dir()
{
  return debug_backtrace()[0]['file']; // Ici
  return get_plugin_dir(__FILE__);
}
```

- si on passe directement cela dans la fonction get_plugin_dir(), on obtient le chemin relatif du dossier du plugin dans lequel on se trouve (enlever le 1er return dans la fonction get_plugin_dir())

```php
function plugin_dir()
{
  return get_plugin_dir(debug_backtrace()[0]['file']); // plugins\header-footer\
}
```

```php
// header-footer/plugin.php
dd(plugin_dir()); // plugins\header-footer\

//home-page/plugin.php
dd(plugin_dir()); // plugins\header-footer\ ET plugins\home-page\
```

- Ceci, ce chemin relatif, est un très bon moyen de charger du CSS et d'inclure des fichiers
- Cependant, on veut le dossier du plugin mais on veut le chemin complet, absolu aussi donc on va utiliser la fonction plugin_http_dir() pour récupérer la version HTTP

```php
function plugin_http_dir()
{
  return ROOT . DIRECTORY_SEPARATOR . get_plugin_dir(debug_backtrace()[0]['file']);
}
```

```php
//home-page/plugin.php
dd(plugin_http_dir()); // http://pluginphp.test\plugins\home-page\
```

- Sur certains serveurs, on peut ne pas trouver le même résultat que renvoi la fonction debug_backtrace() notamment ne pas retrouver ce qu'on cherche dans le 1er élément mais dans le 2è élément :
- quand on fait ça :

```php
function plugin_dir()
{
  return debug_backtrace(); // ce que renvoi ceci
  return get_plugin_dir(debug_backtrace()[0]['file']);
}
```

```php
 [0] => Array
  (
    [file] => D:\laragon\www\pluginphp\plugins\header-footer\plugin.php
    [line] => 3
    [function] => plugin_dir
    [args] => Array
      (
      )
  )
```

- pour remédier à cela, on veut que ça retourne notre chemin quelques soit sa place dans le tableau
- On peut utiliser `__FUNCTION__` qui renvoi le nom de la fonction dans laquelle on est actuellement et c'est dans le même  élément de tableau que se trouve le chemin qu'on cherche

```php
function plugin_dir()
{
  return __FUNCTION__; // plugin_dir
  return debug_backtrace();
  return get_plugin_dir(debug_backtrace()[0]['file']);
}
```

- on va utiliser `array_search(aiguille, botte_de_foin)` pour rechercher notre fonction courante (`plugin_dir`) dans `debug_backtrace`()

```php
function plugin_dir()
{
  // return __FUNCTION__;

  $called_from = debug_backtrace();
  return array_search(__FUNCTION__, $called_from); // Ici

  return get_plugin_dir(debug_backtrace()[0]['file']);
}
```

- ça retourne du vide car en fait on a un array dans un array et ce n'est pas bon pour array_search()
- on va utiliser `array_column()` pour récupérer tous les items nommés `[function]` et créer un nouveau tableau ne contenant que ces items là

```php
function plugin_dir()
{
  // return __FUNCTION__;

  $called_from = debug_backtrace();
  return array_column($called_from, 'function'); // Ici
  // return array_search(__FUNCTION__, $called_from);

  return get_plugin_dir(debug_backtrace()[0]['file']);
}
```

- résultat

```php
Array
(
  [0] => plugin_dir
  [1] => require
  [2] => load_plugins
)
```

- ensuite on utilise array_search() pour retrouver la fonction dans laquelle on se trouve quelque soit son emplacement [0], [1] ou [2] ...

```php
function plugin_dir()
{
  // return __FUNCTION__;

  $called_from = debug_backtrace();

  return array_search(__FUNCTION__, array_column($called_from, 'function')); // 0

  return get_plugin_dir(debug_backtrace()[0]['file']);
}
```

- Le résultat est "0" car elle se trouve dans le 1er élément et ceci sera notre clé que l'on peut utiliser pour éviter les problèmes de serveur

```php
function plugin_dir(): string
{
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  return get_plugin_dir(debug_backtrace()[$key]['file']);
}
```

- Faire pareil dans la fonction `plugin_http_dir()`

```php
function plugin_http_dir(): string
{
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  return ROOT . DIRECTORY_SEPARATOR . get_plugin_dir(debug_backtrace()[$key]['file']);
}
```

- Dans notre plugin.php, on veut instancier une classe par exemple

```php
// header-footer/plugin.php
$user = new User();
```

- dans `app/core/init.php` on va utiliser spl_autoload_register() pour charger automatiquement les classes non trouvées

```php
spl_autoload_register(function ($classname) {
  dd($classname); // User
});

require 'functions.php';
require 'App.php';
```

- Dans "header-footer", on créé un nouveau dossier "models" qui sera un dossier facultatif et chaque fois qu'on voudra créer des models, il cherchera automatiquement dedans pour essayer de trouver la classe du model 
- On créé dedans un fichier `User.php`

```php
// models/User.php
class User
{

}
```

- dans init.php, on veut chercher notre classe User dans le chemin actuel du plugin

```php
spl_autoload_register(function ($classname) {
  dd('models' . DIRECTORY_SEPARATOR . ucfirst($classname . '.php')); // models\User.php
});
```
- Mais nous avons aussi besoin du plugin, du chemin du plugin donc on va utiliser le contenu de notre fonction plugin_dir() et le concaténer avec le chemin de la classe User

```php
spl_autoload_register(function ($classname) {
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));
  dd(get_plugin_dir(debug_backtrace()[$key]['file']) . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname . '.php')); // plugins\header-footer\models\User.php
});
```

- On retrouve bien la classe qu'on cherche, maintenant il faut la charger

```php
spl_autoload_register(function ($classname) {
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  $path = get_plugin_dir(debug_backtrace()[$key]['file']) . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname . '.php');
  if (file_exists($path)) {
    require_once $path;
  }
});
```

- lorsqu'on recharge la page http://pluginphp.test/home, on a plus d'erreur car la class User a bien été trouvée
- pour voir que la class se charge bien automatiquement, on peut faire ceci :

```php
class User
{
  function __construct()
  {
    dd("This is the user class");
  }
}
```

### Loading classes
- Nous avons pu charger automatiquement une classe qui se trouve dans le dossier d'un plugin mais il faut aussi s'assurer que nous pouvons charger automatiquement une classe qui se trouve dans `app/models`

- par exemple charger une classe Session

```php
// header-footer/plugin.php
$user = new User();
$ses = new Session; // Ici
```

- dans core/models on créé un fichier `Session.php`
- l'utilisateur peut créer des classes dans ses plugins et il peut donc créer aussi une class "Session" et dans ce cas il y aura collision car la class "Session" par défaut sera chargée donc on utilise un namespace pour éviter les problèmes de collision

```php
namespace app\core;

/**
 * Session class
 */
class Session
{
  public function __construct()
  {
    dd("This is from Session class");
  }
}
```

- Si on recharge notre page http://pluginphp.test/home on aura une erreur _Uncaught Error: Class "Session" not found_
- Dans le plugin "header-footer" on utilise le namespace dans lequel se trouve la class Session
```php
$ses = new Core\Session;
```

```php
// init.php
spl_autoload_register(function ($classname) {

  dd($classname); // Core\Session
  
  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  $path = get_plugin_dir(debug_backtrace()[$key]['file']) . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname . '.php');
  if (file_exists($path)) {
    require_once $path;
  }
});
```

- Mais on doit se débarrasser de cette partie "Core"
- On doit d'abord vérifier dans le dossier "Models" avant de vérifier dans le dossier des plugins
- On veut récupérer le 2è item donc "Session", on commence par explode la string et ensuite on utilise la fonction end() ou array_pop() pour obtenir le dernier item

```php
// init.php
spl_autoload_register(function ($classname) {

  $parts = explode("\\", $classname);
  $classname = array_pop($parts);
  dd($classname); // Session

  $called_from = debug_backtrace();
  $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

  $path = get_plugin_dir(debug_backtrace()[$key]['file']) . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname . '.php');
  if (file_exists($path)) {
    require_once $path;
  }
});
```

- Vérifier que ce fichier existe dans le dossier "app"
- le path sera celui-ci :

```php
spl_autoload_register(function ($classname) {

  $parts = explode("\\", $classname);
  $classname = array_pop($parts);

  $path = 'app' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname) . '.php';
  dd($path); // app\models\Session.php

  <...>
});
```

- si le path donc la class qui se trouve dans app/models/ existe on le charge sinon on vérifie dans le dossier des plugins s'il existe

```php
spl_autoload_register(function ($classname) {
  $parts = explode("\\", $classname);
  $classname = array_pop($parts);

  $path = 'app' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname) . '.php';

  if (file_exists($path)) {
    require_once $path;
  } else {
    $called_from = debug_backtrace();
    $key = array_search(__FUNCTION__, array_column($called_from, 'function'));

    $path = get_plugin_dir(debug_backtrace()[$key]['file']) . 'models' . DIRECTORY_SEPARATOR . ucfirst($classname . '.php');
    if (file_exists($path)) {
      require_once $path;
    }
  }  
});
```

- dans "header-footer", on instancie 2 classes : `Session.php` qui se trouve dans le dossier "app/models" et `User.php` qui se trouve dans "header-footer/models"

```php
$user = new User(); // This is the user class
$ses = new Core\Session; // This is from Session class
```

- Nous allons ajouter plusieurs classes dans le dossier app/models, on commence à les require dans init.php et on les ajoutent dans "app/core" pour ne pas avoir d'erreur. 

```php
// init.php
<...>

require 'functions.php';
require 'Database.php';
require 'Model.php';
require 'App.php';
```

- Maintenant nous sommes capables de charger des classes dans nos dossiers de plugins et également des fichiers CSS et des images
- Essayons de charger un fichier CSS dans "header-footer" et un fichier view.php depuis le plugin "home-page"

```php
// home-page/plugin.php
<?php

require plugin_dir() . 'includes/view.php'; // ici

add_action('view', function () {
  dd('This is from the view hook in home-page plugin');
});

add_action('controller', function () {

});
```

```php
// header-footer/plugin
<link rel="stylesheet" href="<?= plugin_http_dir().'css/style.css' ?>"> // ici
<?php

<...>
```

- créer un dossier "includes" dans "home-page" et un fichier "view.php"

```html
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Home</title>
</head>
<body>
  <h1>This is the home page</h1>
</body>
</html>
```

- Les fichiers "view.php" dans le plugin "home-page" est bien chargé avec plugin_php() grâce à un chemin relatif et le fichier CSS est bien chargé également dans "header-footer" avec plugin_http_dir() grâce à un chemin absolu. Quand on fait view source on voit bien cela : "http://pluginphp.test\plugins\header-footer\css/style.css"





























