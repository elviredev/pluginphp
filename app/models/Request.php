<?php
namespace Core;

defined('ROOT') or die('Direct script access denied');

/**
 * @@desc Permet de simplifier la gestion des requêtes HTTP
 * Request class
 */
class Request
{
  /**
   * @desc Retourne la méthode HTTP utilisée pour charger cette page
   * 'GET', 'POST', 'PUT', 'DELETE'...
   * Ex. d'utilisation: $req->method()
   * @return string La méthode HTTP utilisée.
   */
  public function method(): string
  {
    return $_SERVER['REQUEST_METHOD'];
  }

  /**
   * @desc Vérifie si la requête est une requête POST.
   * @return bool TRUE si la méthode HTTP est POST, FALSE sinon.
   */
  public function posted():bool
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  /**
   * @desc Récupère une donnée envoyée via la méthode POST.
   *
   * - Si aucun $key n'est fourni, retourne tout le tableau $_POST.
   * - Si une clé existe dans $_POST, retourne sa valeur.
   * - Sinon, retourne une chaîne vide.
   * @param string $key La clé du champ POST à récupérer (optionnel)
   * @return mixed La valeur correspondante ou '' si non trouvée,
   *               ou l'ensemble de $_POST si $key est vide.
   */
  public function post(string $key = ''): mixed
  {
    if (empty($key))
      return $_POST;

    if(!empty($_POST[$key]))
      return $_POST[$key];

    return '';
  }

  /**
   * @desc Récupère une valeur d'entrée envoyée en POST avec une valeur par défaut
   *
   * - Si la clé existe dans $_POST, retourne sa valeur.
   * - Sinon, retourne la valeur par défaut fournie.
   * @param string $key La clé du champ POST à récupérer.
   * @param string $default Valeur par défaut si la clé n'existe pas (par défaut '')
   * @return string La valeur du champ POST ou la valeur par défaut
   */
  public function input(string $key, string $default = ''): string
  {
    if(!empty($_POST[$key]))
      return $_POST[$key];

    return $default;
  }

  /**
   * @desc Récupère une donnée envoyée via la méthode GET
   *
   * - Si aucun $key n'est fourni, retourne tout le tableau $_GET.
   * - Si la clé existe dans $_GET, retourne sa valeur.
   * - Sinon, retourne une chaîne vide.
   * @param string $key La clé du paramètre GET à récupérer (optionnel)
   * @return mixed La valeur correspondante, '' si non trouvée,
   *               ou le tableau $_GET si $key est vide.
   */
  public function get(string $key = ''): mixed
  {
    if (empty($key))
      return $_GET;

    if(!empty($_GET[$key]))
      return $_GET[$key];

    return '';
  }

  /**
   * @desc Récupère un ou plusieurs fichiers envoyés via un formulaire
   *
   * - Si aucun $key n'est fourni, retourne tout le tableau $_FILES.
   * - Si une clé existe dans $_FILES, retourne ses informations
   * (nom, type, tmp_name, erreur, taille).
   * - Sinon, retourne une chaîne vide.
   * @param string $key La clé du champ <input type="file"> à récupérer (optionnel)
   * @return string|array Tableau $_FILES complet ou les infos d'un fichier,
   *                      ou '' si aucun fichier correspondant.
   */
  public function files(string $key = ''): string|array
  {
    if (empty($key))
      return $_FILES;

    if(!empty($_FILES[$key]))
      return $_FILES[$key];

    return '';
  }

  /**
   * @desc Récupère une donnée depuis la requête (GET, POST, COOKIE)
   *
   * - Si aucun $key n'est fourni, retourne tout le tableau $_REQUEST.
   * - Si la clé existe dans $_REQUEST, retourne sa valeur.
   * - Sinon, retourne une chaîne vide.
   * @param string $key La clé du paramètre à récupérer (optionnel)
   * @return string|array Le tableau $_REQUEST complet,
   *                      ou la valeur du paramètre demandé,
   *                      ou '' si non trouvée.
   */
  public function all(string $key = ''): string|array
  {
    if (empty($key))
      return $_REQUEST;

    if(!empty($_REQUEST[$key]))
      return $_REQUEST[$key];

    return '';
  }


}










