  <div class="header_emp"></div>

  <!-- 콘텐츠 영역 -->
  <div class="content">

    <div class="graph_section">
      <div class="wrap">
        <div class="title">
          <h1>방문자 정보 차트</h1>
          <div class="line"></div>
          <p>천안에 방문하신 방문자의 정보를 차트로 모아봤어요</p>
        </div>

        <div class="graph col-flex">
          <input type="radio" name="graph" id="hour" hidden checked>
          <input type="radio" name="graph" id="total" hidden>

          <div class="tab_menu btn_box">
            <label class="btn" for="hour"><i class="fa fa-clock"></i>시간대별 방문자 차트</label>
            <label class="btn" for="total"><i class="fa fa-bars"></i>총 방문자 차트</label>
          </div>

          <div class="hour col-flex">
            <div class="location flex jcsb">

            </div>

            <div class="day flex">
              <div class="chk">월</div>
              <div>화</div>
              <div>수</div>
              <div>목</div>
              <div>금</div>
              <div>토</div>
              <div>일</div>
            </div>

            <div class="main">
              <canvas id="bars" width="1200" height="600"></canvas>
            </div>
          </div>

          <div class="total flex jcc aic">
            <div class="main">
              <canvas id="total_canvas" width="800" height="600"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="route_section">
      <div class="wrap">
        <div class="title">
          <h1>투어 경로 설정</h1>
          <div class="line"></div>
          <p>투어 경로를 설정하고 투어를 즐겨보세요!</p>
        </div>

        <div class="route flex">
          <div class="tour_area">
            <div class="main">
              <div class="marker_box"></div>
              <canvas id="tour" width="800" height="850"></canvas>
            </div>
          </div>

          <div class="util_menu col-flex">
            <div class="btn" onclick="Tour.undo()"><i class="fa fa-undo"></i>되돌리기</div>
            <div class="btn" onclick="Tour.reset()"><i class="fa fa-trash"></i>초기화</div>
            <div class="btn" onclick="Tour.download()"><i class="fa fa-download"></i>다운로드</div>
            <div class="btn"><i class="fa fa-hand"></i>가이드 선택</div>
          </div>
        </div>
      </div>
    </div>

  </div>