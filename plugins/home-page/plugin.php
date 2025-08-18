<?php

require plugin_dir() . 'includes/view.php';

add_action('view', function () {
  dd('This is from the view hook in home-page plugin');
});

add_action('controller', function () {

});

