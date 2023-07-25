<?php
  $now_tour = tour::find("guide_idx = ? && status != ? && complete != ?", [USER["idx"], "complete", "8"]);
  $review = review::findAll("guide_idx = ? ORDER BY idx DESC", USER["idx"]);
?>

<div class="guide">
  <div class="wrap col-flex">
    <input type="radio" name="guide" id="guide1" hidden checked>
    <input type="radio" name="guide" id="guide2" hidden>
  
    <div class="tab_menu flex">
      <label for="guide1">투어현황</label>
      <label for="guide2">후기조회</label>
    </div>

    <div class="container">
      <div class="guide_tour">
        <div class="route flex">
          <div class="tour_area">
            <div class="main">
              <div class="marker_box"></div>
              <canvas id="tour" width="800" height="850"></canvas>
            </div>
          </div>
        </div>

        <div class="table">
          <div>
            <p>투어 상태</p>
          </div>
          <?php if(@$now_tour["status"] == "wait"): ?>
            <div>
              <p><?= user::find("idx = ?", $now_tour['user_idx'])["username"] ?></p>
              <div class="btn_box">
                <a href="/tour/accept/<?= $now_tour["idx"] ?>" class="btn" style="color: #fff;background-color: yellowgreen;">수락</a>
                <a href="/tour/reject/<?= $now_tour["idx"] ?>" class="btn" style="color: #fff;background-color: tomato;">거절</a>
              </div>
            </div>
          <?php elseif(@$now_tour["status"] == "accept"): ?>
            <?php for($i = 1;$i <= 8;$i++):?>
            <div>
              <p>투어 <?= $i ?></p>
              <?php if($now_tour["complete"] < $i): ?>
                <div class="btn_box">
                  <a href="/tour/complete/<?= $now_tour["idx"] ?>/<?= $i ?>" class="btn" style="color: #fff;background-color: yellowgreen;">완료</a>
                </div>
              <?php else: ?>
                <p>완료</p>
              <?php endif ?>
            </div>
            <?php endfor ?>
          <?php endif?>
        </div>
      </div>

      <div class="review_container">

        <?php foreach($review as $v): ?>
          <div class="item col-flex">
            <div class="top flex jcsb aic">
              <h2><?= user::find("idx = ?", $v["write_idx"])["username"] ?></h2>
              <div class="stars flex" style="color: orange;">
                <?= str_repeat("<i class='fa fa-star'></i>", floor($v["score"]/2)) ?>
                <?= str_repeat("<i class='fa fa-star'></i>", $v["score"]%2) ?>
              </div>
            </div>
            <p><?= $v["content"] ?></p>
            <small><?= $v["create_dt"] ?></small>
          </div>
        <?php endforeach ?>

      </div>
    </div>
  </div>
</div>

<script>

<?php if(@$now_tour["status"] == "accept"): ?>
  Tour.route = <?= !$now_tour ? null : $now_tour["route"] ?>;
  Tour.complete = <?= !$now_tour ? 0 : $now_tour["complete"] ?>;

  $(() => {
    Tour.init();
    
    setInterval(() => {
      Tour.reDraw();
    }, 200);
  })
<?php endif ?>


</script>