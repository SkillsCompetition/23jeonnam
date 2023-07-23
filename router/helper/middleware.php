<?php
  
  function notUser(){
    err(@USER, "비회원만 접근 가능한 페이지입니다.");
  }

  function user(){
    err(!USER, "로그인 후 접근 가능합니다.");
  }

  function type_user(){
    user();
    err(@USER["type"] != "user", "일반 회원만 이용 가능합니다.");
  }

?>