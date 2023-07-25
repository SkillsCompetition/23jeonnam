<?php
  $guide = DB::mq("SELECT guide.*, NVL(AVG(review.score), 0) AS score_avg
                           FROM user AS guide
                           LEFT JOIN review 
                           ON guide.idx = review.guide_idx
                           WHERE guide.type = ? && recommand IS NULL
                           GROUP BY guide.idx
                           ORDER BY score_avg DESC", ["guide"])->fetchAll();

  $guide_recommand = DB::mq("SELECT guide.*, NVL(AVG(review.score), 0) AS score_avg
                             FROM user AS guide
                             LEFT JOIN review 
                             ON guide.idx = review.guide_idx
                             WHERE guide.type = ? && recommand = ?
                             GROUP BY guide.idx
                             ORDER BY score_avg DESC", ["guide", "on"])->fetchAll();
?>

<div class="admin_section">
  <div class="wrap">
    <div class="title">
      <h1>추천 가이드</h1>
      <div class="line"></div>
      <p>추천 가이드를 설정해보세요</p>
    </div>

    <div class="admin flex">
      <div class="left">
        <h3>일반 가이드 목록</h3>
        <div class="table">
          <div>
            <p>가이드</p>
            <p>리뷰 평균 점수</p>
          </div>
          <?php foreach($guide as $v): ?>
          <div data-idx="<?= $v['idx'] ?>">
            <p><?= $v["username"] ?></p>
            <p><?= round($v["score_avg"], 2) ?>점</p>
          </div>
          <?php endforeach ?>
        </div>
  
        <form action="/recommand/set" method="POST" class="fchk1">
          <input type="text" name="user_idx" id="user_idx" hidden>
          <div class="btn_box full">
            <button class="btn green">추천 가이드 설정</button>
          </div>
        </form>
      </div>
      
      <div class="right">
        <h3>추천 가이드 목록</h3>
        <div class="table">
          <div>
            <p>가이드</p>
            <p>리뷰 평균 점수</p>
          </div>
          <?php foreach($guide_recommand as $v): ?>
          <div data-idx="<?= $v['idx'] ?>">
            <p><?= $v["username"] ?></p>
            <p><?= round($v["score_avg"], 2) ?>점</p>
          </div>
          <?php endforeach ?>
        </div>
  
        <form action="/recommand/delete" method="POST" class="fchk2">
          <input type="text" name="user_idx" id="user_idx" hidden>
          <div class="btn_box full">
            <button class="btn red">추천 가이드 해제</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>

  $(document)
    .on("click", ".admin .left .table div:not(:first-child)", (e) => {
      $(".chk1").removeClass("chk1");

      $(e.target).addClass("chk1");
      $("form.fchk1 #user_idx").val($(e.target).attr("data-idx"));
    })
    .on("click", ".admin .right .table div:not(:first-child)", (e) => {
      $(".chk2").removeClass("chk2");

      $(e.target).addClass("chk2");
      $("form.fchk2 #user_idx").val($(e.target).attr("data-idx"));
    })

</script>