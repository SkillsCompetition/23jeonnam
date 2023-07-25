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

  <?php if(@USER["type"] == "user"): ?>
    <div class="alert flex aifs">
      <div class="btn" onclick="openAlert()"><i class="fa fa-bell"></i>警報</div>
      <div class="container col-flex">

      </div>
    </div>
  <?php endif ?>


  <div id="root">

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
        <a href="/sub">景點</a>
        <a href="/tour">景點遊覽</a>
        <a href="/buy">特產採購</a>
        
        <div class="animation flex jcsb">
          <i class="fa fa-plane fa-rotate-180"></i>
          <i class="fa fa-plane"></i>
        </div>
      </div>

      <div class="utility flex jcfe aic">

        <div class="lang_box flex aic">
          <i class="fa fa-globe"></i>
          <select class="lang flex" onchange="changeLang(this)">
            <option value="kor">한국어</option>
            <option value="eng">English</option>
            <option value="cn" selected>繁體中文</option>
            <option value="jp">日本語</option>
          </select>
        </div>

        <div class="login_box btn_box aic">
          <?php if(@USER):?>
            <a href="/mypage"><?= USER["username"] ?>(<?= $type[USER["type"]] ?>)</a>
            <a href="/logout" class="btn"><i class="fa fa-user-times"></i>로그아웃</a>
          <?php else: ?>
            <a href="/login" class="btn"><i class="fa fa-user"></i>登錄</a>
            <a href="/join" class="btn invert"><i class="fa fa-user-plus"></i>加入會員</a>
          <?php endif ?>
        </div>

      </div>
    </div>
  </header>

  <script>

    let isOpen = false;

    function openAlert(){
      isOpen = !isOpen;
      $('.alert').toggleClass('open')
      settingAlert();
    }

    function settingAlert(){
      $.getJSON("/load_alert")
        .then(res => {
          $(".alert .btn").html(`<i class="fa fa-bell"></i>알림`);

          if(res.length != 0){
            $(".alert .btn").append(`<div class="count">${res.length}</div>`)
          }

          if(isOpen){
            $(".alert .container").html(res.map(v => {
              return `
                <div class="item col-flex" onclick="remove(${v.idx})">
                  <p>${v.content}</p>
                  <p>${v.time}</p>
                </div>`
            }))
          }
        })
    };

    function remove(idx){
      $.get(`/remove_alert/${idx}`);
      settingAlert();
    }

    setInterval(() => {
      settingAlert();
    }, 1000);

    settingAlert();

    function changeLang(target){
      const value = $(target).val();

      $.ajax({
        url : `/change_lang/${value}`,
        method : "get",
        cache : false
      }).done(() => {
        location.reload();
      });
    }

  </script>