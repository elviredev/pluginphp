<?php
namespace Model;
defined('ROOT') or die('Direct script access denied');

/**
 * User Model
 */
class User extends Model
{
  protected $table = 'users';

  protected $allowedColumns = [
    'email',
    'password',
    'date_created'
  ];

  protected $allowedUpdateColumns = [
    'email',
    'password',
    'date_updated'
  ];

  function __construct()
  {
    dd("This is the user class");
  }
}