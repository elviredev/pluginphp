<?php

use Core\Image;

add_action('view', function(){
  require plugin_path('includes\login.view.php');
});

add_action('controller', function(){
  $img = new Image;
  $img->getThumbnail('image.jpeg', 200, 420);
});