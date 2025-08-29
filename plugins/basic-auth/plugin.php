<?php

add_action('view', function(){
  require plugin_path('includes\login.view.php');
});

add_action('controller', function(){
  $req = new \Core\Request();
  if ($req->posted()) {
    $post = $req->post(); // ce qui arrive du formulaire
    dd("Code CSRF qui arrive du formulaire: " . $post['csrf']);

    $session = new \Core\Session;
    echo "Ce qui existe en session: " . "<br>";
    dd($session->get('csrf')); // ce qui est en session

    echo "Résultat de la vérification: ";
    var_dump(csrf_verify($post, 'csrf')); // résultat de la vérification
  }
});