<?php
namespace Core;

/**
 * Pager class
 * @desc Permet de gérer la pagination d’un ensemble de résultats
 * (par ex. pour une requête SQL avec LIMIT et OFFSET)
 * et à générer les liens HTML de navigation.
 */
class Pager
{
  public array $links     = [];  // Contiendra les URLs de navigation (current, first, next, etc.)
  public int $limit       = 10;  // Nombre d’éléments affichés par page
  public int $offset      = 0;   // Décalage SQL pour la pagination (ex: LIMIT 10 OFFSET 20)
  public int $start       = 1;   // Première page affichée dans la barre de pagination
  public int $end         = 1;   // Dernière page affichée dans la barre
  public int $page_number = 1;   // Numéro de la page courante

  public function __construct(int $limit = 10, $extras = 1)
  {
    // Récuperer numéro de page
    $page_number = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
    $page_number = $page_number < 1 ? 1 : $page_number;
    // Définir la plage de pages affichées
    $this->start = $page_number - $extras;
    $this->end = $page_number + $extras;
    // $start doit toujours être au moins à "1", ne peut pas être = à 0
    $this->start = $this->start < 1 ? 1 : $this->start;

    $this->page_number = $page_number;
    $this->offset = ($page_number - 1) * $limit;

    // Récupération de l'URL courante
    $current_url = $_SERVER['REQUEST_URI'];

    // On découpe l'URL en parties (path + query)
    $parts = parse_url($current_url);

    // Path → ex: /products/edit
    $path = $parts['path'] ?? '/';

    // Query string → tableau associatif
    $query_params = [];
    if (isset($parts['query'])) {
      parse_str($parts['query'], $query_params);
    }

    // On enlève "url" si présent
    unset($query_params['url']);

    // Reconstruction de la query string
    $query_string = http_build_query($query_params);

    // Reconstruction de l’URL finale
    $current_link = ROOT . $path . (!empty($query_string) ? '?' . $query_string : '');

    // Ajouter "page" si absent
    if (!isset($query_params['page'])) {
      $current_link .= (empty($query_string) ? '?' : '&') . 'page=' . $page_number;
    }

    // Préparer les liens de navigation
    $first_link = preg_replace("/page=[0-9]+/", "page=1", $current_link);
    $next_link = preg_replace("/page=[0-9]+/", "page=".($page_number + $extras + 1), $current_link);

    // Ajouter les liens
    $this->links['current']  = $current_link;
    $this->links['first']    = $first_link;
    $this->links['next']     = $next_link;
  }

  /**
   * @desc Génère le HTML Bootstrap pour la pagination
   * @return void
   */
  public function display()
  {
    ?>
    <nav aria-label="Page navigation example">
      <ul class="pagination">
        <li class="page-item">
          <a class="page-link" href="<?= $this->links['first'] ?>">
            First
          </a>
        </li>
        <?php for ($x = $this->start; $x <= $this->end; $x++) : ?>
          <li class="page-item <?= $x == $this->page_number ? 'active' : '' ?>">
            <a class="page-link" href="<?= preg_replace("/page=[0-9]+/", "page=".$x, $this->links['current']) ?>"><?= $x ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item">
          <a class="page-link" href="<?= $this->links['next'] ?>">
            Next
          </a>
        </li>
      </ul>
    </nav>
    <?php
  }

  public function displayTailwind()
  {
    ?>
    <nav class="flex m-6" aria-label="Pagination">
      <ul class="inline-flex -space-x-px text-sm">

        <!-- Lien vers la première page -->
        <li>
          <a href="<?= $this->links['first'] ?>"
             class="px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300
                  rounded-l-lg hover:bg-gray-100 hover:text-gray-700">
            First
          </a>
        </li>

        <!-- Boucle sur les numéros de page -->
        <?php for ($x = $this->start; $x <= $this->end; $x++) : ?>
          <li>
            <a href="<?= preg_replace("/page=[0-9]+/", "page=".$x, $this->links['current']) ?>"
               class="px-3 py-2 leading-tight border <?= $x == $this->page_number
                   ? 'z-10 text-blue-600 border-blue-300 bg-blue-50'
                   : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700' ?>">
              <?= $x ?>
            </a>
          </li>
        <?php endfor; ?>

        <!-- Lien vers la page suivante -->
        <li>
          <a href="<?= $this->links['next'] ?>"
             class="px-3 py-2 leading-tight text-gray-500 bg-white border border-gray-300
                  rounded-r-lg hover:bg-gray-100 hover:text-gray-700">
            Next
          </a>
        </li>
      </ul>
    </nav>
    <?php
  }

  public function displayCustom()
  {

  }


}