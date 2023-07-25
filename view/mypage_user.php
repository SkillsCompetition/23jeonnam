<?php

  $now_tour = tour::find("user_idx = ? && status != ?", [USER["idx"], "complete"]);
  $buy_list = buy::findAll("user_idx = ? ORDER BY idx DESC", USER["idx"]);
?>

<div class="point_fix">
  남은 포인트 : <?= point::getPoint(USER["idx"]) ?> 포인트
</div>


<div class="status_section">
  <div class="wrap">
    <div class="title">
      <h1>투어 상태</h1>
      <div class="line"></div>
      <p>투어 상태를 확인해보세요</p>
    </div>

    <div class="status">
      <div class="route flex">
        <div class="tour_area">
          <div class="main">
            <div class="marker_box"></div>
            <canvas id="tour" width="800" height="850"></canvas>
          </div>
        </div>
      </div>

      <div class="status_box col-flex">
        <div>투어 상태</div>
        <div>
          <?php if(!$now_tour): ?>
            현재 진행 중인 투어가 없습니다.
          <?php elseif(@$now_tour["status"] == "wait"): ?>
            신청한 투어를 가이드가 확인 중
          <?php else:?>
            <?php
              $guide = user::find("idx = ?", $now_tour["guide_idx"]);
            ?>
            <?= $guide["username"] ?>(<?= $guide["userid"] ?>)
          <?php endif ?>
        </div>
        <?php if(@$now_tour["complete"] == "8"): ?>
          <div class="btn" onclick="review(<?= $now_tour['idx'] ?>, <?= $now_tour['guide_idx'] ?>)" style="background-color: yellowgreen; color: #fff;"><i class="fa fa-pen"></i>리뷰 작성</div>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>

<div class="basket_section">
  <div class="wrap">
    <div class="title">
      <h1>명물 장바구니</h1>
      <div class="line"></div>
      <p>천안의 명물들을 담아 구매해보세요</p>
    </div>

    <div class="basket">
      <?php foreach(array_reverse($_SESSION["basket"], true) as $k => $v): ?>
        <?php
          $item = specialties::find("idx = ?", $v["idx"]);
        ?>
        <div class="item">
          <div class="top col-flex">
            <div class="flex jcsb aic">
              <h2><?= $item["name"] ?></h2>

              <p><?= $v["count"] * $item["point"] ?>포인트</p>
            </div>

            <div class="flex jcsb aic">
              <small>개수 : <?= $v["count"] ?>개</small>
              <small>개당 포인트 : <?= $item["point"] ?>포인트</small>
            </div>
          </div>
          <div class="bottom btn_box none full">
            <form action="/buy/basket/<?= $k ?>" style="width: 100%;">
              <button class="btn" style="color: #fff;background-color: yellowgreen;">구매</button>
            </form>
            <form action="/delete/basket/<?= $k ?>" style="width: 100%;">
              <button class="btn" style="color: #fff;background-color: tomato;">삭제</button>
            </form>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
</div>

<div class="basket_list_section">
  <div class="wrap">
    <div class="title">
      <h1>명물 구매목록</h1>
      <div class="line"></div>
      <p>구매하신 명물 목록입니다.</p>
    </div>

    <div class="basket_list">
      <div class="table">
        <div>
          <p>날짜</p>
          <p>이름</p>
          <p>포인트</p>
          <p>총 포인트</p>
          <p>비고</p>
        </div>
        <?php foreach($buy_list as $v): ?>
        <?php
          $item = specialties::find("idx = ?", $v["specialties_idx"]);
        ?>
        <div>
          <p><?= $v["create_dt"] ?></p>
          <p><?= $item["name"] ?></p>
          <p><?= $item["point"] ?>포인트</p>
          <p><?= $v["point"] ?>포인트</p>
          <div class="btn_box">
            <a href="/delete/buy_list/<?= $v["idx"] ?>" class="btn" style="color:#fff;background: tomato;">삭제</a>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
  </div>
</div>

<script>
  Tour.route = <?= !$now_tour ? [] : $now_tour["route"] ?>;
  Tour.complete = <?= is_null($now_tour["complete"]) ? 0 : $now_tour["complete"] ?>;

  $(() => {
    Tour.init();
    setInterval(() => {
      Tour.reDraw();
    }, 200);
  })

  function review(idx, guide){
    Modal.open("review");

    $(".modal #tour_idx").val(idx);
    $(".modal #guide_idx").val(guide);
  }

  $(document)
    .on("mousemove", ".stars", star)

  function star(e){
    const left = $(e.target).offset().left;
    const score = Math.ceil((e.pageX - left)/8.5);

    $(".modal #score").val(score)
    $(".modal .stars .fill").css({
      "width" : `${score * 8.5}px`
    });
  }
</script>

<template>

  <form class="review_modal" action="/review" method="POST">
    <div class="main">
      <div class="flex aic">
        <h2 class="modal_title">리뷰</h2>
      </div>
      <hr>
      <div class="inputs">
        <div class="input_box">
          <input type="text" name="tour_idx" id="tour_idx" hidden>
          <input type="text" name="guide_idx" id="guide_idx" hidden>
          <input type="text" name="score" id="score" value="2" hidden>
          <label for="stars">별점</label>
          <div class="stars">
            <div class="emp flex">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
            <div class="fill flex">
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
              <i class="fa fa-star"></i>
            </div>
          </div>
        </div>


        <div class="input_box textarea">
          <label for="count">리뷰</label>
          <textarea name="content" id="content" required></textarea>
        </div>
      </div>
    </div>
    <div class="btn_box submit">
      <button class="btn">작성 완료</button>
      <div class="btn" onclick="Modal.close()">닫기</div>
    </div>
  </form>

</template>