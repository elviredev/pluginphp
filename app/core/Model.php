<?php

namespace Model;
use Core\Database;

defined('ROOT') or die('Direct script access denied');

/**
 * Classe Model
 *
 * Cette classe sert de base pour interagir avec une table en base de données.
 * Elle hérite de la classe Database, et fournit des méthodes génériques pour
 * exécuter des requêtes SQL avec des conditions dynamiques.
 *
 * Chaque modèle concret (ex: UserModel, PostModel, etc.) doit définir la propriété
 * `$table` correspondant à sa table.
 *
 * @property string $order        Ordre de tri des résultats (ASC ou DESC).
 * @property string $order_column Colonne utilisée pour le tri (par défaut 'id').
 * @property string $primary_key  Clé primaire de la table (par défaut 'id').
 * @property int    $limit        Nombre maximum de résultats retournés (par défaut 10).
 * @property int    $offset       Décalage des résultats (utile pour la pagination).
 * @property array  $errors       Tableau des erreurs éventuelles rencontrées.
 */
class Model extends Database
{
  /** @var string Ordre de tri par défaut */
  public $order         = 'desc';

  /** @var string Colonne de tri par défaut */
  public $order_column  = 'id';

  /** @var string Clé primaire de la table */
  public $primary_key   = 'id';

  /** @var int Nombre maximum de résultats par requête */
  public $limit         = 10;

  /** @var int Décalage utilisé pour la pagination */
  public $offset        = 0;

  /** @var array Tableau des erreurs rencontrées */
  public $errors        = [];

  /**
   * @desc Exécute une requête SELECT avec une clause WHERE dynamique
   * Cette méthode construit une requête SQL en fonction des conditions fournies :
   *  - `$where_array` génère des clauses d'égalité (`=`).
   *  - `$where_not_array` génère des clauses de différence (`!=`).
   * @param array $where_array Ex: ['field' => 'value'] => génère `field = :field`
   * @param array $where_not_array Ex: ['field' => 'value'] => génère `field != :field`
   * @param string $data_type Format de retour attendu ('object' ou 'array')
   * @return array|bool Retourne les résultats de la requête sous forme de tableau d'objets ou de tableaux.
   *                    Retourne false en cas d'échec de l'exécution
   */
  public function where(array $where_array = [], array $where_not_array = [], string $data_type = 'object'): array|bool
  {
    $query = "SELECT * FROM $this->table WHERE ";

    if (!empty($where_array)) {
      foreach ($where_array as $key => $value) {
        $query .= $key .'= :'. $key . ' AND '; // SELECT * FROM users WHERE user_id = :user_id AND id = :id
      }
    }

    if (!empty($where_not_array)) {
      foreach ($where_not_array as $key => $value) {
        $query .= $key .' != :'. $key . ' AND '; // SELECT * FROM users WHERE user_id = :user_id AND id != :id
      }
    }

    // Comme on ajoute AND à chaque condition, enlève le dernier AND superflu
    $query = rtrim($query, ' AND ');
    $query .= " ORDER BY $this->order_column $this->order LIMIT $this->limit OFFSET $this->offset"; // ORDER BY id desc LIMIT 10 OFFSET 0
    // Donne un tableau clé/valeur des données à binder dans la requête
    $data = array_merge($where_array, $where_not_array);

    // Exécution de la requête
    return $this->query($query, $data);
  }

  /**
   * @desc Récupère le premier enregistrement correspondant aux conditions.   *
   * Cette méthode utilise `where()` pour exécuter la requête, puis
   * retourne uniquement le premier résultat trouvé.
   * @param array $where_array
   * @param array $where_not_array
   * @param string $data_type
   * @return array|bool
   */
  public function first(array $where_array = [], array $where_not_array = [], string $data_type = 'object'): array|bool
  {
    // Exécute la requête en utilisant la méthode where()
    $rows = $this->where($where_array, $where_not_array, $data_type);

    // Si des résultats existent, retourne uniquement le premier
    if (!empty($rows))
      return $rows[0];

    return false;
  }

  /**
   * @desc Récupère tous les enregistrements de la table avec tri et pagination.
   * Cette méthode génère une requête SELECT simple sur la table du modèle,
   * avec un ORDER BY, un LIMIT et un OFFSET basés sur les propriétés
   * de la classe.
   * @param string $data_type
   * @return array|bool
   */
  public function getAll(string $data_type = 'object'): array|bool
  {
    $query = "SELECT * FROM $this->table ORDER BY $this->order_column $this->order LIMIT $this->limit OFFSET $this->offset";
    return $this->query($query, [], $data_type);
  }

  /**
   * @desc Insère un nouvel enregistrement dans la table.
   *
   * Seules les colonnes présentes dans $allowedInsertColumns sont conservées.
   * Les autres sont ignorées pour éviter toute insertion non désirée.   *
   * @param array $data Données à insérer sous forme clé => valeur
   * @return array|false True si l'insertion réussit, false sinon
   */
  public function insert(array $data)
  {
    if (!empty($this->allowedColumns)) {
      foreach ($data as $key => $value) {
        if (!in_array($key, $this->allowedColumns)) {
          // Supprime colonnes indésirables
          unset($data[$key]);
        }
      }
    }
    // exécute l'insertion sans les colonnes non autorisées
    if (!empty($data)){
      $keys = array_keys($data);
      // INSERT INTO table (column1, column2) VALUES (:column1, :column2)
      $query = "INSERT INTO $this->table (".implode(",", $keys).") VALUES (:".implode(",:", $keys).")";
      return $this->query($query, $data);
    }
    return false;
  }

  /**
   * @desc Met à jour un enregistrement existant dans la table
   *
   * Seules les colonnes présentes dans $allowedUpdateColumns sont conservées.
   * La condition WHERE utilise la clé primaire définie dans $primary_key
   * @param string|int $my_id Valeur de la clé primaire de l'enregistrement à modifier
   * @param array $data Données à mettre à jour (clé => valeur)
   * @return array|false True si la mise à jour réussit, false sinon
   */
  public function update(string|int $my_id, array $data)
  {
    if (!empty($this->allowedUpdateColumns)) {
      foreach ($data as $key => $value) {
        if (!in_array($key, $this->allowedUpdateColumns)) {
          // Supprime colonnes indésirables
          unset($data[$key]);
        }
      }
    }
    // exécute la modification sans les colonnes non autorisées
    if (!empty($data)){
      // UPDATE table SET id = :id, name = :name WHERE id = :id
      $query = "UPDATE $this->table ";
      foreach ($data as $key => $value) {
        $query .= $key.' = :'.$key.",";
      }

      $query = trim($query, ',');
      $data['my_id'] = $my_id;

      $query .= " WHERE $this->primary_key = :my_id";
      return $this->query($query, $data);
    }
    return false;
  }

  /**
   * @desc Supprime un enregistrement de la table
   *
   * La condition WHERE utilise la clé primaire définie dans $primary_key.
   * Un `LIMIT 1` est ajouté pour éviter toute suppression massive par erreur.
   * @param string|int $my_id Valeur de la clé primaire de l'enregistrement à supprimer
   * @return array|false True si la suppression réussit, false sinon
   */
  public function delete(string|int $my_id)
  {
    // DELETE FROM table WHERE id = :id
    $query = "DELETE FROM $this->table ";

    $query .= " WHERE $this->primary_key = :my_id LIMIT 1";
    return $this->query($query, ['my_id' => $my_id]);
  }
}
