<?php

function check_extensions()
{
  $extensions = ['gd', 'pdo_mysql'];

  $not_loaded = [];
  foreach ($extensions as $extension) {
    // Si extension pas activée
    if (!extension_loaded($extension)) {
      // L'ajouter au tableau des non chargées
      $not_loaded[] = $extension;
    }

    if (!empty($not_loaded)) {
      dd("Please load the following extensions in your php.ini file: " . implode(', ', $not_loaded));
    }
  }
}

check_extensions();