const dd = console.log;

const App = {

  init(){
    App.hook();

    if(location.pathname.includes("tour")){
      Graph.init();
      Tour.init();
    }

    if(location.pathname.includes("buy")) Buy.init();
  },

  hook(){
    $(document)
      .on("click", ".graph .location div", Graph.bars.clickLocation)
      .on("click", ".graph .day div", Graph.bars.clickDay)
      .on("click", ".graph #total", Graph.radial.changeTab)
      .on("mousedown", ".route .main", Tour.mouse.down)
      .on("mousemove", ".route .main", Tour.mouse.move)
      .on("mouseup mouseleave", ".route .main", Tour.mouse.other)
      .on("input", "#walnut_prograss", Buy.drawWalnut)
  }

}

function radian(deg){
  return (Math.PI/180) * deg;
}

const Graph = {

  async init(){
    await Graph.loadData();

    Graph.bars.init();
    Graph.radial.init();
  },

  bars : {
    canvas : null,
    ctx : null,
    data : [],
    nowIdx : 0,
    nowDay : "월",

    init(){
      Graph.bars.canvas = $("#bars")[0];
      Graph.bars.ctx = Graph.bars.canvas.getContext("2d");

      Graph.bars.settingLocation();
      Graph.bars.drawGrid();
      Graph.bars.drawBars();
    },

    clickLocation(e){
      const target = $(e.target);
      Graph.bars.nowIdx = target.index();

      $(".location .chk").removeClass("chk");
      target.addClass("chk");

      Graph.bars.reDraw();
    },

    clickDay(e){
      const target = $(e.target);
      Graph.bars.nowDay = target.text();

      $(".day .chk").removeClass("chk");
      target.addClass("chk");

      Graph.bars.reDraw();
    },

    settingLocation(){
      $(".graph .hour .location").html(Graph.bars.data.map((v, i) => {
        return `<div ${i == 0 ? "class='chk'" : ""}>${v.name}</div>`
      }))
    }, 

    reDraw(){
      Graph.bars.ctx.clearRect(0, 0, 1200, 600);

      Graph.bars.drawGrid();
      Graph.bars.drawBars();
    },
    
    drawGrid(){
      const day = ["오전 7시","오전 8시","오전 9시","오전 10시","오전 11시","오전 12시","오후 1시","오후 2시","오후 3시","오후 4시","오후 5시","오후 6시","오후 7시","오후 8시","오후 9시","오후 10시","오후 11시"];
      const obj = Graph.bars.data[Graph.bars.nowIdx].visitors_per_hour;
      const data = obj.find(v => v.week == Graph.bars.nowDay).data;
      const maxValue = Math.ceil(Math.max(...Object.values(data))/100) * 100;
      const line = maxValue/100;

      Graph.bars.ctx.fillStyle = "#000";
      for(let i = 0;i <= 17;i++){
        Graph.bars.ctx.fillRect((60 * i) + 90, 25, 1, 550)

        Graph.bars.ctx.font = "12px sans";
        Graph.bars.ctx.textAlign = "center";

        if(i != 17) Graph.bars.ctx.fillText(day[i], (60 * i) + 120, 590)
      }

      for(let i = 0;i <= line;i++){
        Graph.bars.ctx.fillRect(85, (550/line * i) + 25, 1025, 1)

        Graph.bars.ctx.font = "14px sans";
        Graph.bars.ctx.textAlign = "end";

        Graph.bars.ctx.fillText(maxValue - (100 * i), 80, (550/line * i) + 25)
      }
    },

    drawBars(){
      const obj = Graph.bars.data[Graph.bars.nowIdx].visitors_per_hour;
      const data = obj.find(v => v.week == Graph.bars.nowDay).data;
      const maxValue = Math.ceil(Math.max(...Object.values(data))/100) * 100;

      $(".graph .hour .main div").remove();

      let i = 0;
      for(let v in data){
        const value = data[v];
        const height = value/maxValue * 550;

        $(".graph .hour .main").append(`
          <div style="left: ${95 + (60 * i)}px;">
            <div class="bar" style="--i: ${height}px; height: ${height}px;"></div>

            <div class="item">
              <h3>${v}</h3>
              <p>${v}에 ${value}명이 방문했습니다.</p>
            </div>
          </div>
        `)
        i++
      }
    },
  },

  radial : {
    data : [],
    percent : 0,
    
    init(){
      Graph.radial.canvas = $("#total_canvas")[0];
      Graph.radial.ctx = Graph.radial.canvas.getContext("2d");

      Graph.radial.max = Math.ceil(Math.max(...Graph.radial.data.map(v => v.visitor))/10000) * 10000;

      Graph.radial.animation();
    },

    changeTab(){
      Graph.radial.percent = 0;

      Graph.radial.animation()
    },

    reDraw(percent){
      Graph.radial.ctx.clearRect(0, 0, 1200, 600);

      Graph.radial.drawGrid();
      Graph.radial.drawData(percent);
    },

    drawGrid(){
      const ctx = Graph.radial.ctx;
      const degUnit = 360 /  8;
      const depth = Math.ceil(Graph.radial.max/50000);
      const radiusUnit = 250/depth;

      ctx.lineWidth = "1px"
      ctx.strokeStyle = "#d1d1d1"

      ctx.textAlign = "center";
      ctx.font = "13px sans"
      ctx.fillStyle = "#000";

      for(let i = 0; i < 8; i++){
        const [x, y] = [
          Math.cos(radian(degUnit * i)) * 250 + 400,
          Math.sin(radian(degUnit * i)) * 250 + 300
        ];
        
        ctx.beginPath();
          ctx.moveTo(400, 300);
          ctx.lineTo(x, y);
        ctx.closePath();
        ctx.stroke()
      }

      for(let nowDepth = 0;nowDepth <= depth;nowDepth++){
        ctx.beginPath();
        for(let i = 0; i < 8; i++){
          const [x, y] = [
            Math.cos(radian(degUnit * i)) * (radiusUnit * nowDepth) + 400,
            Math.sin(radian(degUnit * i)) * (radiusUnit * nowDepth) + 300
          ];

          if(i == 6) ctx.fillText(50000 * nowDepth > Graph.radial.max ? Graph.radial.max : 50000 * nowDepth, x, y);
          if(i == 0){
            ctx.moveTo(x, y);
          } else ctx.lineTo(x, y);
        }
        ctx.closePath();
        ctx.stroke()
      }

      Graph.radial.data.forEach((v, i) => {
        const [x, y] = [
          Math.cos(radian(degUnit * i)) * 310 + 400,
          Math.sin(radian(degUnit * i)) * 280 + 300
        ];

        ctx.fillText(v.name, x, y);
      })
    },

    drawData(percent = 1){
      const ctx = Graph.radial.ctx;
      const degUnit = 360 /  8;
      const max = Graph.radial.max;
      const data = Graph.radial.data;
      const pos = [];

      for(let i = 0; i < 8; i++){
        const [x, y] = [
          (Math.cos(radian(degUnit * i)) * (250 * data[i].visitor/max)) * percent + 400,
          (Math.sin(radian(degUnit * i)) * (250 * data[i].visitor/max)) * percent + 300
        ];

        pos.push([x, y]);
      }

      // 라인 그리기
      ctx.strokeStyle = "#222";
      ctx.fillStyle = "#ffb7005f";

      ctx.beginPath()
      pos.forEach(([x, y], i) => {
        if(i == 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      })
      ctx.closePath();
      ctx.stroke();
      ctx.fill();

      // 점 그리기
      ctx.fillStyle = "#000";
      ctx.beginPath()
      pos.forEach(([x, y]) => {
        ctx.moveTo(x, y);
        ctx.arc(x, y, 3, 0, Math.PI * 2);
      })
      ctx.fill();
      ctx.closePath();

      $(".graph .total .main div").remove();
      if(percent >= 1){
        $(".graph .total .main").append(pos.map(([x, y], i) => {
          const nowData = data[i];

          return `
            <div style="top: ${y - 3}px; left: ${x - 3}px;">
              <div class="circle"></div>

              <div class="item">
                <h3>${nowData.name}</h3>
                <p>방문자 수 : ${nowData.visitor}명</p>
              </div>
            </div>`
        }));
      }
    },

    animation(){
      window.requestAnimationFrame(() => {
        const unit = 1/60;

        if(Graph.radial.percent <= 1) {
          Graph.radial.percent += unit;

          Graph.radial.reDraw(Graph.radial.percent)
          Graph.radial.animation()
        }  
      });
    }

  },

  loadData(){
    const promise = [
      $.get("/resources/json/rush_hour_visitors.json").then(res => {
        Graph.bars.data = res.landscapes;
  
        return res;
      }),
      $.get("/resources/json/visitors.json").then(res => {
        Graph.radial.data = res.data;
  
        return res;
      }) 
    ];

    return Promise.all(promise);
  }
}

const Tour = {
  canvas : null,
  ctx : null,
  data : [
    { 
      id : 2,
      name : "유관순열사 사적지",
      x : 570,
      y : 505
    },
    { 
      id : 1,
      name : "독립기념관",
      x : 385,
      y : 482
    },
    { 
      id : 3,
      name : "천안 삼거리 공원",
      x : 280,
      y : 430
    },
    { 
      id : 4,
      name : "태조산 왕건길과 청동대좌불",
      x : 355,
      y : 331
    },
    { 
      id : 5,
      name : "아라리오 조각광장",
      x : 275,
      y : 331
    },
    { 
      id : 6,
      name : "성성호수공원",
      x : 260,
      y : 264
    },
    { 
      id : 7,
      name : "광덕산",
      x : 68,
      y : 658
    },
    { 
      id : 8,
      name : "국보 봉선 홍경사 갈기비",
      x : 230,
      y : 68
    },
  ],
  route : [],

  init(){
    Tour.canvas = $("canvas#tour")[0];
    Tour.ctx = Tour.canvas.getContext("2d");

    Tour.background = new Image();
    Tour.background.src = "/resources/img/map.png";

    Tour.background.onload = () => {
      Tour.settingMarker();

      Tour.drawBackground();
      Tour.drawDot();
    }
  },

  settingMarker(){
    $(".route .marker_box").append(Tour.data.map((v, i) => {
      const style = `left: ${v.x/800 * 100}%; top: ${v.y/850 * 100}%;`
      return `
        <div class="marker" style="${style}">
          <div class="point point_id${v.id}"></div>
          <div class="item flex">
            <img src="/resources/img/special/${i + 1}.jpg" alt="">
            <h2>${v.name}</h2>
          </div>
        </div>`
    }))    
  },

  mouse : {
    allowMove : false,

    down(e){
      const { left, top } = $(".route .main").offset();

      const x = e.pageX - left
      const y = e.pageY - top

      const chk = Tour.chkMarker(x, y);
      if(!chk) return;

      const last = [...Tour.route].pop();
      if(last?.id == chk.id){
        Tour.mouse.allowMove = true;
      }else if(!last){
        Tour.mouse.allowMove = true;

        Tour.route.push({
          id : chk.id,
          complete : false,
          pos : [chk.x, chk.y]
        });
      }else{
        alert("마지막으로 선택한 경로부터 시작해주세요.");
      }
    },

    move(e){
      if(!Tour.mouse.allowMove) return;

      const { left, top } = $(".route .main").offset();

      const x = e.pageX - left
      const y = e.pageY - top

      Tour.reDraw([{
        pos : [x, y]
      }])
    },

    other(e){
      if(!Tour.mouse.allowMove) return;
      Tour.mouse.allowMove = false;

      const { left, top } = $(".route .main").offset();

      const x = e.pageX - left
      const y = e.pageY - top

      const chk = Tour.chkMarker(x, y);
      const crash = Tour.route.find(v => v.id == chk?.id);
      if(crash) alert("이미 설정된 경로입니다.");
      if(!crash && chk) Tour.route.push({
        id : chk.id,
        complete : false,
        pos : [chk.x, chk.y]
      });

      if(Tour.route.length == 1) Tour.route = [];

      Tour.reDraw()
    }
  },

  drawDot(){
    const ctx = Tour.ctx;

    ctx.fillStyle = "#ffb700";

    ctx.beginPath();
    Tour.data.forEach((v) => {
      ctx.moveTo(v.x, v.y)
      ctx.arc(v.x, v.y, 15, 0, Math.PI * 2)
    });
    ctx.closePath();
    ctx.fill();
  },

  drawLine(nowPos = []){
    const ctx = Tour.ctx;

    ctx.strokeStyle = "#000";
    ctx.lineWidth = 5
    
    ctx.beginPath();
    [...Tour.route, ...nowPos].forEach((v, i) => {
      if(i == 0) ctx.moveTo(...v.pos);
      else ctx.lineTo(...v.pos);
    });
    ctx.stroke();
    ctx.closePath();
  },

  drawNumber(){
    if(Tour.route.length < 2) return;
    const ctx = Tour.ctx;

    ctx.fillStyle = "#222";
    ctx.font = "bold 16px sans"
    ctx.textAlign = "center";

    Tour.route.forEach((v, i) => {
      ctx.fillText(i + 1, v.pos[0], v.pos[1] + 6);
    })
  },

  drawBackground(){
    const ctx = Tour.ctx;

    ctx.clearRect(0, 0, 800, 850)
    ctx.drawImage(Tour.background, 0, 0)
  },

  reDraw(obj){
    Tour.drawBackground();
    Tour.drawLine(obj);
    Tour.drawDot();
    Tour.drawNumber();
  },

  chkMarker(x, y){
    return Tour.data.find(v => {
      return Math.abs(v.x - x) <= 20 && Math.abs(v.y - y) <= 20
    })
  },

  undo(){
    if(Tour.route.length < 2) return alert("경로를 한개 이상 선택해주세요.");
    Tour.route.pop();

    if(Tour.route.length == 1) {
      Tour.route = [];
    }

    Tour.reDraw();
  },

  reset(){
    if(Tour.route.length < 2) return alert("경로를 한개 이상 선택해주세요.");
    Tour.route = [];

    Tour.reDraw();
  },

  download(){
    if(Tour.route.length < 2) return alert("경로를 한개 이상 선택해주세요.");
    const data = Tour.canvas.toDataURL();

    const a = document.createElement("a");

    a.href = data;
    a.download = "route.png";

    a.click();
    a.remove();
  },

}

const Buy = {
  data : [],

  async init(){
    await Buy.loadData();

    Buy.settingContainer();
  },

  open(i){
    const data = Buy.data[i];

    Modal.open("buy");
    $(".buy_modal .modal_title").html(data.name);
    $(".buy_modal .description").html(data.description);
    $(".buy_modal .point").html(`포인트 개당 ${data.point}포인트`);

    if(data.name === "호두과자"){
      $(".buy_modal .walnut").show();
       
      Buy.drawWalnut({});
    }
  },

  drawWalnut(e){
    const w = h = 100
    const percent = e.target?.value || 152;
    const img = $(".walnut_img")[0];
    const canvas = $("#walnut")[0];
    const ctx = canvas.getContext("2d");
    const imgData = new ImageData(w, h);

    ctx.clearRect(0, 0, 300, 300)
    ctx.drawImage(img, percent, 0, 228, 228, 0, 0, w, h);

    const refIdx = .6;
    const radius = (w/2)**2;

    const centerX = centerY = w/2; 

    let origX = origY = 0;
    let i = 0

    for (let x = 0; x < w;x++){
      for (let y = 0; y < h;y++){
        const distX = x - centerX;
        const distY = y - centerY;
  
        const r2 = distX**2 + distY**2;
  
        origX = x;
        origY = y;
  
        if(r2 > 0.0 && r2 < radius) {
            const z2 = radius - r2;
            const z = Math.sqrt(z2);
  
            const xb = Math.asin(distX / Math.sqrt( distX**2 + z2 ));
            const yb = Math.asin(distY / Math.sqrt( distY**2 + z2 ));
  
            origX = origX - z * Math.tan(xb * (1 - refIdx));
            origY = origY - z * Math.tan(yb * (1 - refIdx));
        }
    
        const data = ctx.getImageData(origX, origY, 1, 1);

        imgData.data[i] = data.data[0];
        imgData.data[i + 1] = data.data[1];
        imgData.data[i + 2] = data.data[2];
        imgData.data[i + 3] = data.data[3];

        i += 4;
      }
      if(x == 99) {
        dd(imgData);
        ctx.putImageData(imgData, w, h)
      };
    }

  },

  settingContainer(){
    $(".buyitem").html(Buy.data.map((v, i) => {
      return `
        <div class="item">
          <img src="/resources/img/item/${v.image}" alt="">
          <div class="box flex jcc aic">
            <div class="btn" onclick="Buy.open(${i})">장바구니 담기</div>
          </div>
        </div>`
    }))
  },

  loadData(){
    return new Promise(res => {
      $.getJSON("/resources/json/specialties.json")
        .then(data => {
          Buy.data = data.data;

          res();
        })
    })
  }
}

const Modal = {
  template : (t) => $($("template")[0].content).find(`.${t}_modal`).clone(),

  open(t){
    $("body").css("overflow", "hidden");

    $(".modal")
      .addClass("open")
      .html(Modal.template(t))
  },

  close(){
    $("body").css("overflow", "");

    $(".modal")
      .removeClass("open")
      .html("")
  }
}

$(() => App.init())
  
    
  
