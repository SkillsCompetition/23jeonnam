  <div class="header_emp"></div>

  <!-- 콘텐츠 영역 -->
  <div class="content">

    <div class="buyitem_section">
      <div class="wrap">
        <div class="title">
          <h1>명물 구매</h1>
          <div class="line"></div>
          <p>천안의 명물을 구매해주세요</p>
        </div>

        <div class="buyitem">
        </div>
      </div>
    </div>

  </div>

  <template>

    <div class="buy_modal">
      <div class="main">
        <div class="flex aic">
          <h2 class="modal_title">모달</h2>
        </div>
        <hr>
        <div class="inputs">
          <p class="description"></p>
          <hr>
          <div class="col-flex" style="gap: .5rem; color: #ffb700;">
            <small class="point"></small>
            <div class="input_box">
              <label for="count">갯수</label>
              <input type="text" id="count">
            </div>
          </div>
          <div class="walnut col-flex jcc aic" style="display: none;">
            <div class="area">
              <canvas id="walnut" width="200" height="200"></canvas>
              <div class="shadow"></div>
            </div>

            <input type="range" name="walnut_prograss" id="walnut_prograss" max="100">
          </div>
        </div>
      </div>
      <div class="btn_box submit">
        <div class="btn">구매</div>
        <div class="btn" onclick="Modal.close()">닫기</div>
      </div>
    </div>

  </template>
  <img class="walnut_img" src="resources/img/walnut-flat.png" alt="#" hidden>