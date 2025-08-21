<?php

namespace Core;
use \PDO;
use \PDOException;

defined('ROOT') or die('Direct script access denied');

/**
 * Classe Database
 *
 * @desc Cette classe fournit une interface simple pour se connecter à une base de données
 * via PDO et exécuter des requêtes SQL avec ou sans paramètres.
 */
class Database
{
  /**
   * @desc Identifiant de la requête
   * @var string
   */
  private static $query_id = '';

  /**
   * @desc Établit une connexion à la base de données.
   * @return PDO Objet PDO représentant la connexion à la base de données.
   */
  private function connect(): PDO
  {
    $VARS['DB_NAME']        = DB_NAME;
    $VARS['DB_USER']        = DB_USER;
    $VARS['DB_PASSWORD']    = DB_PASSWORD;
    $VARS['DB_HOST']        = DB_HOST;
    $VARS['DB_DRIVER']      = DB_DRIVER;

    $VARS = do_filter('before_db_connect', $VARS);

    $string = "$VARS[DB_DRIVER]:host=$VARS[DB_HOST];dbname=$VARS[DB_NAME]";

    try {
      $conn = new PDO($string, $VARS['DB_USER'], $VARS['DB_PASSWORD']);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      die("Failed to connect to the database with error " . $e->getMessage());
    }

    return $conn;
  }

  /**
   * @desc Renvoi un seul objet qui correspond à un enregistrement en BDD
   * @param string $query
   * @param array $data
   * @param string $data_type
   * @return false|mixed
   */
  public function get_row(string $query, array $data = [], string $data_type = 'object')
  {
    $result = $this->query($query, $data, $data_type);
    if(is_array($result) && count($result) > 0) {
      return $result[0];
    }

    return false;
  }

  /**
   * @desc Exécute une requête SQL préparée et retourne le résultat.
   * @param string $query Requête SQL à exécuter, avec éventuellement des placeholders
   * @param array $data Tableau associatif contenant les valeurs à lier aux placeholders
   * @param string $data_type Type de données de retour :
   *                      - 'object' (par défaut): retourne un tableau d'objets (PDO::FETCH_OBJ)
   *                      - 'assoc' : retourne un tableau associatif (PDO::FETCH_ASSOC)
   * @return array|false Tableau des résultats si la requête retourne des données,
   *                     ou false si aucune donnée n'est trouvée ou en cas d'échec.
   */
  public function query(string $query, array $data = [], string $data_type = 'object')
  {
    $query = do_filter('before_query_query', $query);
    $data = do_filter('before_query_data', $data);

    $conn = $this->connect();
    $stmt = $conn->prepare($query);

    $result = $stmt->execute($data);
    if($result) {
      if($data_type == 'object') {
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
      } else {
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      }
    }

    $arr = [];
    $arr['query'] = $query;
    $arr['data'] = $data;
    $arr['result'] = $rows ?? [];
    $arr['query_id'] = self::$query_id;
    self::$query_id = '';

    $result = do_filter('after_query', $arr);

    if(is_array($result) && count($result) > 0) {
      return $result;
    }

    return false;
  }
}