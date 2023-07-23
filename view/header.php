<!DOCTYPE html>
<html lang="zxx">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/resources/fonts/fontAwesome/css/all.css">
  <link rel="stylesheet" href="/resources/css/style.css">
  <script src="/resources/js/jquery-3.6.0.js"></script>
  <script src="/resources/js/script.js"></script>
  <title>Cheonan Package Tour</title>
</head>
<body>

  <!-- 로딩 -->
  <div class="loading flex jcc aic">
    <div>
      <img src="/resources/img/loading.gif" alt="#" title="#">
    </div>
  </div>

  <?php
    $type = [
      "user" => "일반 회원",
      "guide" => "가이드",
      "admin" => "관리자"
    ]
  ?>

  <!-- 헤더 섹션 -->
  <header>
    <div class="wrap flex jcsb aic">
      <div class="logo_box">
        <a href="/"><img src="/resources/img/logo.png" alt="#" title="#" class="logo"></a>
      </div>

      <div class="menu_nav flex">
        <a href="/sub">명소 소개</a>
        <a href="/tour">명소 투어</a>
        <a href="/buy">명물 구매</a>
        
        <div class="animation flex jcsb">
          <i class="fa fa-plane fa-rotate-180"></i>
          <i class="fa fa-plane"></i>
        </div>
      </div>

      <div class="utility flex jcfe aic">

        <div class="lang_box flex aic">
          <i class="fa fa-globe"></i>
          <select class="lang flex">
            <option value="kor">한국어</option>
            <option value="eng">English</option>
            <option value="cn">繁體中文</option>
            <option value="jp">日本語</option>
          </select>
        </div>

        <div class="login_box btn_box aic">
          <?php if(@USER):?>
            <a href="/mypage"><?= USER["username"] ?>(<?= $type[USER["type"]] ?>)</a>
            <a href="/login" class="btn"><i class="fa fa-user-times"></i>로그아웃</a>
          <?php else: ?>
            <a href="/login" class="btn"><i class="fa fa-user"></i>로그인</a>
            <a href="/join" class="btn invert"><i class="fa fa-user-plus"></i>회원가입</a>
          <?php endif ?>
        </div>

      </div>
    </div>
  </header>