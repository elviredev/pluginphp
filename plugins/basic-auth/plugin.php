<?php

add_action('view', function(){
  dd(get_value());
});

add_action('controller', function(){
  $arr = ['name' => 'John', 'age' => 30];
  set_value($arr);
});