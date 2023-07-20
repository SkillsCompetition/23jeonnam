const dd = console.log;

const App = {

  init(){
    App.hook();

    Graph.init();
  },

  hook(){
    $(document)
      .on("click", ".graph .location div", Graph.bars.clickLocation)
      .on("click", ".graph .day div", Graph.bars.clickDay)
      .on("click", ".graph #total", Graph.radial.changeTab)
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

$(() => App.init())