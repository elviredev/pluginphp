<?php
namespace app\core;

class App
{
  public function index()
  {
    echo "Everything is working";
    echo '<pre>';
    print_r(URL());
  }
}