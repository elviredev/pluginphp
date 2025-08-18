<link rel="stylesheet" href="<?= plugin_http_dir().'css/style.css' ?>">
<?php

add_action('controller', function () {
  dd($_POST);
});

add_action('after_view', function () {
  echo "<center>Website | Copyright 2025</center>";
});

add_action('view', function () {
  echo "<form method='POST' style='width: 400px; margin: 40px auto; text-align: center; '>
          <h4>Login</h4>
          <input placeholder='email' name='email'><br>
          <input placeholder='password' name='password'><br>
          <button>Login</button>
        </form>";
});

add_action('before_view', function () {
  echo "<center><div><a href=''>Home</a> . About us . Contact us</div></center>";
});

