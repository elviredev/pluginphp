<?php
namespace app\core;

class App
{
  public function index(): void
  {
    // --- PHASE 1 : Exécution de la logique du contrôleur ---

    // Hook exécuté juste avant la logique du contrôleur.
    // Peut servir pour initialiser des variables globales, vérifier les permissions, etc.
    do_action("before_controller");

    // Hook qui déclenche la logique principale du contrôleur :
    // chargement de données depuis la base, traitement de la requête, etc.
    do_action("controller");

    // Hook exécuté juste après la logique du contrôleur.
    // Peut servir à nettoyer, logger, ou ajouter des données supplémentaires.
    do_action("after_controller");

    // --- PHASE 2 : Préparation et exécution de la vue ---

    // Hook exécuté avant l'affichage de la vue.
    // Utile pour injecter du HTML commun (header, menu, etc.)
    do_action("before_view");

    // Récupère le contenu actuel du tampon de sortie.
    // Ce contenu correspond à tout ce qui a été affiché avant la vue.
    $before_content = ob_get_contents();
    ob_end_clean();

    // Hook qui déclenche l'affichage de la vue.
    // La vue devrait normalement générer du HTML et l'envoyer au tampon de sortie.
    do_action("view");

    // Récupère le contenu du tampon de sortie après l'exécution de la vue.
    $after_content = ob_get_contents();

    // Hook exécuté après l'affichage de la vue.
    // Peut servir à ajouter un footer ou du debug HTML.
    do_action("after_view");

    // On enlève du contenu final ($after_content) ce qui existait avant la vue ($before_content).
    // Si la différence est vide, ça veut dire que la vue n'a rien produit.
    if(str_replace($before_content, "", $after_content) == "") {
      // Si on n'est pas déjà sur la page 404, on redirige vers la 404.
      if (page() != '404') {
        redirect('404');
      }
    }
  }
}