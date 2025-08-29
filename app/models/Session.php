<?php
namespace Core;

defined('ROOT') or die('Direct script access denied');

/**
 * @desc Stocke les infos de l'utilisateur connecté
 * Session class
 */
class Session
{
  private string $varKey = 'APP';
  private string $userKey = 'USER';

  /**
   * @desc Si statut à true (1) -> démarrer la session
   * @return int
   */
  public function startSession(): int
  {
    if(session_status() == PHP_SESSION_NONE)
      session_start();

    return 1;
  }


  /**
   * @desc Stocke une ou plusieurs valeurs dans la session sous la clé principale "APP"
   * @param string|array $keyOrArray Clé unique (string) ou tableau associatif de valeurs
   * @param mixed|null $value Valeur associée à la clé si $keyOrArray est une string
   * @return bool Retourne true si au moins une valeur a été enregistrée
   */
  public function set(string|array $keyOrArray, mixed $value = null): bool
  {
    $this->startSession();

    // $keyOrArray est un tableau
    if (is_array($keyOrArray)) {
      // Pour chaque paire clé/valeur du tableau stocke dans $_SESSION['APP'][$key] = $value
      foreach ($keyOrArray as $key => $value) {
        $_SESSION[$this->varKey][$key] = $value;
      }
      return true;
    } else {
      // $keyOrArray est une string : stocke directement la valeur sous la clé donnée.
      $_SESSION[$this->varKey][$keyOrArray] = $value;
      return true;
    }

    return false;
  }


  /**
   * @desc Récupère une valeur enregistrée en session sous la clé principale "APP"
   * @param string $key Nom de la clé à récupérer
   * @return mixed Valeur stockée (string, int, array, etc.) ou false si absente
   */
  public function get(string $key): mixed
  {
    $this->startSession();

    if (!empty($_SESSION[$this->varKey][$key])) {
      return $_SESSION[$this->varKey][$key];
    }

    return false;
  }

  /**
   * @desc Récupère une valeur de session puis la supprime immédiatement.
   * Cette méthode est utile pour les données temporaires qui ne doivent être
   * lues qu'une seule fois (par exemple un message flash, un token expiré, etc.).
   * @param string $key Nom de la clé stockée sous $_SESSION[$this->varKey][$key]
   * @return mixed Retourne la valeur de session si elle existe,
   *               puis la supprime. Retourne false si la clé est absente.
   */
  public function pop(string $key): mixed
  {
    $this->startSession();
    // Vérifie si une valeur existe dans $_SESSION['APP']['key']
    if (!empty($_SESSION[$this->varKey][$key])) {
      // Si oui sauvegarde dans $sessionValue
      $sessionValue = $_SESSION[$this->varKey][$key];
      // Supprime l’entrée de $_SESSION
      unset($_SESSION[$this->varKey][$key]);
      // Retourne la valeur supprimée
      return $sessionValue;
    }

    return false;
  }


  /**
   * @desc Authentifie un utilisateur en enregistrant ses données dans la session
   *
   * Les données peuvent être un tableau associatif ou un objet (ex. ligne de BDD).
   * Elles seront stockées dans $_SESSION['USER'].
   * @param object|array $row Données de l'utilisateur (objet ou tableau)
   * @return bool Retourne toujours true après enregistrement
   */
  public function auth(object|array $row): bool
  {
    $this->startSession();

    $_SESSION[$this->userKey] = $row;

    return true;
  }


  /**
   * @desc Vérifie si un utilisateur est actuellement connecté
   *
   * La méthode considère qu'un utilisateur est connecté si
   * la clé $_SESSION['USER'] contient un objet ou un tableau
   * (données enregistrées via la méthode auth()).
   * @return bool Retourne true si un utilisateur est connecté, false sinon
   */
  public function is_logged_in(): bool
  {
    $this->startSession();

    if (empty($_SESSION[$this->userKey]))
      return false;

    if (is_object($_SESSION[$this->userKey]))
      return true;

    if (is_array($_SESSION[$this->userKey]))
      return true;

    return false;
  }


  /**
   * @desc Réinitialise complètement la session
   *
   * - Détruit toutes les données de la session active.
   * - Génère un nouvel identifiant de session pour des raisons de sécurité
   * @return bool Retourne toujours true
   */
  public function reset(): bool
  {
    session_destroy();
    session_regenerate_id();

    return true;
  }


  /**
   * @desc Déconnecte l'utilisateur actuellement authentifié
   *
   * - Supprime uniquement les données stockées sous $_SESSION['USER'].
   * - Les autres données de session (par ex. $_SESSION['APP']) restent intactes.
   * @return bool Retourne toujours true
   */
  public function logout(): bool
  {
    $this->startSession();

    if (!empty($_SESSION[$this->userKey]))
      unset($_SESSION[$this->userKey]);

    return true;
  }


  /**
   * @desc Récupère les données de l'utilisateur connecté
   *
   * - Si $key est vide, retourne toutes les données (objet ou tableau) stockées en session.
   * - Si $key est fourni, retourne uniquement la valeur associée.
   * - Retourne null si aucun utilisateur n'est connecté ou si la clé n'existe pas.
   * @param string $key Clé à récupérer dans les données utilisateur (optionnelle)
   * @return mixed Données utilisateur complètes, valeur de la clé demandée, ou null
   */
  public function user(string $key = ''): mixed
  {
    $this->startSession();

    if (!empty($_SESSION[$this->userKey])) {
      if (empty($key))
        return $_SESSION[$this->userKey];

      if (is_object($_SESSION[$this->userKey])) {
        if (!empty($_SESSION[$this->userKey]->{$key})) {
          return $_SESSION[$this->userKey]->{$key};
        }
      } elseif (is_array($_SESSION[$this->userKey])) {
          if (!empty($_SESSION[$this->userKey][$key])) {
            return $_SESSION[$this->userKey][$key];
          }
      }
    }
    return null;
  }


  /**
   * @desc Récupère toutes les données enregistrées dans la session APP
   * @return mixed Tableau associatif des données stockées ou false si vide
   */
  public function all(): mixed
  {
    $this->startSession();

    if (!empty($_SESSION[$this->varKey])) {
      return $_SESSION[$this->varKey];
    }

    return false;
  }

}