<?php
namespace Migration;

use Core\Database;

/**
 * @desc Cette classe permet de gérer des migrations basiques en PHP sans passer par un framework
 * comme Laravel. Elle hérite de la classe `Database` et offre des méthodes pour :
 *    - Créer une table (avec colonnes, clés primaires, clés étrangères, index, etc.)
 *    - Insérer des données après création de table
 *    - Supprimer une table
 *
 * Les colonnes, clés et données sont accumulées via des méthodes `addX()` puis utilisées
 * lors de l'appel de `createTable()` ou `insert()`.
 *
 */
class Migration extends Database
{
  /** @var array $columns Liste des colonnes à créer dans la table */
  private $columns       = [];

  /** @var array $keys Liste des index simples (KEY) */
  private $keys          = [];

  /** @var array $data Données à insérer dans la table */
  private $data          = [];

  /** @var array $primaryKeys Liste des clés primaires */
  private $primaryKeys   = [];

  /** @var array $foreignKeys Liste des clés étrangères */
  private $foreignKeys   = [];

  /** @var array $uniqueKeys Liste des clés uniques */
  private $uniqueKeys    = [];

  /** @var array $fullTextKeys Liste des index FULLTEXT */
  private $fullTextKeys  = [];

  /**
   * @desc Crée une table SQL avec les colonnes et clés précédemment ajoutées
   * @param string $table
   * @return void
   */
  public function createTable(string $table)
  {
    if (!empty($this->columns)) {
      $query = "CREATE TABLE IF NOT EXISTS `$table` (";

      $query .= implode(",", $this->columns) . ",";

      foreach ($this->primaryKeys as $key) {
        $query .= "PRIMARY KEY (`$key`),";
      }

      foreach ($this->keys as $key) {
        $query .= "KEY (`$key`),";
      }

      foreach ($this->uniqueKeys as $key) {
        $query .= "UNIQUE KEY (`$key`),";
      }

      foreach ($this->fullTextKeys as $key) {
        $query .= "FULLTEXT KEY (`$key`),";
      }

      foreach ($this->foreignKeys as $fk) {
        // $fk devrait être un tableau : ['column' => '...', 'refTable' => '...', 'refColumn' => '...']
        $query .= "FOREIGN KEY (`{$fk['column']}`) REFERENCES `{$fk['refTable']}`(`{$fk['refColumn']}`),";
      }

      $query = rtrim($query, ",");

      $query .= ")ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

      $this->query($query);

      // Réinitialisation des propriétés après création
      $this->columns       = [];
      $this->keys          = [];
      $this->data          = [];
      $this->primaryKeys   = [];
      $this->foreignKeys   = [];
      $this->uniqueKeys    = [];
      $this->fullTextKeys  = [];

      echo "\n\r Table $table created successfully!\n";
    } else {
      echo "\n\r Column data not found! Could not create table: $table\n";
    }

  }

  /**
   * @desc Insère des données dans une table
   * @param string $table
   * @return void
   */
  public function insert(string $table)
  {
    if (!empty($this->data) && is_array($this->data)) {
      foreach ($this->data as $row) {
        $keys = array_keys($row);
        $columns_string = implode(",", $keys);
        $values_string = ':' . implode(",:", $keys);

        $query = "INSERT INTO `$table` ($columns_string) VALUES ($values_string)";
        $this->query($query, $row);
      }
      $this->data = [];
      echo "\n\r Data inserted successfully in table: $table\n";
    } else {
      echo "\n\r Row data not found! No data inserted in table: $table\n";
    }
  }

  /**
   * @desc Ajoute une colonne à la définition de la table
   * Exemple : `$migration->addColumn("id INT AUTO_INCREMENT")`
   * @param string $column Définition SQL de la colonne
   * @return void
   */
  public function addColumn(string $column)
  {
    $this->columns[] = $column;
  }

  /**
   * @desc Ajoute un index simple (KEY)
   * @param string $key Nom de la colonne indexée
   * @return void
   */
  public function addKey(string $key)
  {
    $this->keys[] = $key;
  }

  /**
   * @desc Ajoute une clé primaire
   * @param string $primaryKey Nom de la colonne clé primaire
   * @return void
   */
  public function addPrimaryKey(string $primaryKey)
  {
    $this->primaryKeys[] = $primaryKey;
  }

  /**
   * @desc Ajoute des données à insérer dans la table
   * @param array $data Tableau associatif (colonne => valeur)
   * @return void
   */
  public function addData(array $data)
  {
    $this->data[] = $data;
  }

  /**
   * @desc Ajoute une clé étrangère à la table.
   *
   * Exemple :
   * ```php
   * $migration->addForeignKey('user_id', 'users', 'id');
   * ```
   *
   * @param string $column Nom de la colonne locale
   * @param string $refTable Nom de la table de référence
   * @param string $refColumn Nom de la colonne de référence
   * @return void
   */
  public function addForeignKey(string $column, string $refTable, string $refColumn)
  {
    $this->foreignKeys[] = [
      'column'    => $column,
      'refTable'  => $refTable,
      'refColumn' => $refColumn,
    ];
  }

  /**
   * @desc Supprime une table si elle existe
   * @param string $table
   * @return void
   */
  public function drop(string $table)
  {
    $query = "DROP TABLE IF EXISTS $table ";
    $this->query($query);

    echo "\n\r Table $table deleted successfully!\n";
  }

}