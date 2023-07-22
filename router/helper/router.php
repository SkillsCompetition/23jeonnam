<?php

  function get(){
    router("GET", ...func_get_args());
  }

  function post(){
    router("POST", ...func_get_args());
  }

  function middleware($auth, $fn){
    $fn();
  }

  function router($method, $uri, $fn){
    $uri = str_replace(["/", "$"], ["\/", "(.+)"], $method.$uri);

    if(!preg_match_all("/^$uri$/", $_SERVER["REQUEST_METHOD"].U, $param)){
      return;
    }

    foreach(debug_backtrace() as $v){
      $v["function"] == "middleware" && $v["args"][0];
    }

    $fn(...array_slice(array_merge(...$param), -count($param) + 1));
  }

?>