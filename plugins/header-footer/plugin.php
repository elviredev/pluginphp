<?php

add_filter('user_permissions', function ($permissions) {

  return $permissions;
});

add_action('controller', function () {

});

add_action('after_view', function () {
  require plugin_path('includes\footer.view.php');
});

add_action('view', function () {
//  $limit = 10;
//  $pager = new \Core\Pager($limit);
//  $offset = $pager->offset;
//  $pager->display();
//  $pager->displayTailwind();
});

add_action('before_view', function () {
  require plugin_path('includes\header.view.php');
});

