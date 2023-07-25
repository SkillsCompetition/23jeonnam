<?php

  get("/default", function(){
    user::mq("TRUNCATE TABLE user");
    specialties::mq("TRUNCATE TABLE specialties");

    $data = [
      [
        "userid" => "u01",
        "password" => "1234",
        "username" => "사용자1",
        "type" => "user"
      ],
      [
        "userid" => "u02",
        "password" => "1234",
        "username" => "사용자2",
        "type" => "user"
      ],
      [
        "userid" => "g01",
        "password" => "1234",
        "username" => "가이드1",
        "type" => "guide"
      ],
      [
        "userid" => "g02",
        "password" => "1234",
        "username" => "가이드2",
        "type" => "guide"
      ],
      [
        "userid" => "admin",
        "password" => "1234",
        "username" => "관리자",
        "type" => "admin"
      ],
    ];

    foreach($data as $v){
      user::insert($v);
    }

    $json = json_decode(file_get_contents(ROOT."/resources/json/specialties.json"), true)["data"];

    foreach($json as $v){
      $v["image"] = "/resources/img/item/".$v["image"];

      specialties::insert($v);
    }
  });

  get("/", function(){
    if(@!$_SESSION["lang"]) $lang = "kor";
    else $lang = $_SESSION["lang"];
    
    require ROOT."/view/lang/$lang/header.php";
    require ROOT."/view/lang/$lang/index.php";
    require ROOT."/view/lang/$lang/footer.php";
  });

  get("/change_lang/$", function($lang){
    $_SESSION["lang"] = $lang;
  });

  middleware("notUser", function(){

    get("/join", function(){
      view("join");
    });

    post("/join", function(){
      $find = user::find("userid = ?", P["userid"]);
      err($find, "중복된 아이디가 존재합니다.");

      if(!preg_match("/^[ㄱ-힣]{2,6}$/u", P["username"])){
        move("이름은 한글 2자에서 6자 사이만 입력가능합니다.");
      }

      if(!preg_match("/^[\w][^\_]{3,8}$/", P["userid"])){
        move("아이디는 영문, 숫자 조합으로 3자에서 8자 사이만 가능합니다.");
      }

      if(!preg_match("/^(?=.*\w)(?=.*\W)[\w\W]{4,8}$/", P["password"])){
        move("비밀번호는 숫자, 영문, 특수문자 조합으로 4자에서 8자 사이만 가능합니다.");
      }

      user::insert(P);
      move("회원가입이 완료되었습니다.", "/");
    });

    get("/login", function(){
      view("login");
    });

    post("/login", function(){
      $find = user::find("userid = ? && password = ?", array_values(P));
      err(!$find, "아이디 또는 비밀번호를 확인해주세요.");

      $_SESSION["user_101"] = $find;
      move("로그인이 완료되었습니다.", "/");
    });
  
  });

  middleware("user", function(){

    get("/logout", function(){
      session_destroy();

      move("로그아웃이 완료되었습니다.", "/");
    });

    get("/tour", function(){
      view("tour");
    });

    get("/guide_list", function(){
      $data = DB::mq("SELECT guide.*, NVL(AVG(review.score), 0) AS score_avg
                      FROM user AS guide
                      LEFT JOIN review 
                      ON guide.idx = review.guide_idx
                      LEFT JOIN tour 
                      ON guide.idx = tour.guide_idx
                      WHERE guide.type = ?
                      AND (tour.status = ? OR tour.complete = ? OR tour.status IS NULL)
                      GROUP BY guide.idx
                      ORDER BY score_avg DESC", ["guide", "reject", "8"])->fetchAll();

      echo json_encode($data);
    });

    get("/buy", function(){
      view("buy");
    });

    get("/buy_list", function(){
      $buy_list = specialties::all();
      
      echo json_encode($buy_list);
    });

    get("/mypage", function(){
      view("mypage");
    });

  });

  middleware("type_user", function(){

    post("/tour", function(){
      $data = P;
      $data["user_idx"] = USER["idx"];

      $now_tour = tour::find("user_idx = ? && status != ?", [USER["idx"], "complete"]);
      if($now_tour) move("현재 진행 중인 투어가 존재합니다.");

      tour::insert($data);
      move("투어 신청이 완료되었습니다.");
    });

    get("/chkAllow_basket", function(){
      $find = tour::find("status = ? && user_idx = ?", ["complete", USER["idx"]]);
      if(USER["type"] != "user" || !$find){
        http_response_code(400);
        echo USER["type"];
      }else http_response_code(200); 
    });

    post("/basket_in", function(){
      if(!$_SESSION["basket"]){
        $_SESSION["basket"] = [];
      };

      $find = array_search(P["specialties_idx"], array_column($_SESSION["basket"], "idx"));
      if($find === false){
        $_SESSION["basket"][] = [
          "idx" => P["specialties_idx"],
          "count" => P["count"]
        ];
      }else{
        $_SESSION["basket"][$find]["count"] += P["count"];
      }

      $item = specialties::find("idx = ?",P["specialties_idx"]);
      alert::insert([
        "user_idx" => USER["idx"],
        "content" => $item["name"]." 이(가) 장바구니에 ".P["count"]."개 추가되었습니다."
      ]);

      move("장바구니의 추가되었습니다.");
    });

    get("/buy/basket/$", function($idx){
      $buy = $_SESSION["basket"][$idx];

      $specialties = specialties::find("idx = ?", $buy["idx"]);

      $now_point = point::getPoint(USER["idx"]);
      $minus_point = $specialties["point"] * $buy["count"];

      err($minus_point > $now_point, "포인트가 모자라 구매할 수 없습니다.");

      point::insert([
        "point" => $minus_point * -1,
        "user_idx" => USER["idx"]
      ]);
      buy::insert([
        "specialties_idx" => $buy["idx"],
        "count" => $buy["count"],
        "point" => $minus_point,
        "user_idx" => USER["idx"]
      ]);
      alert::insert([
        "user_idx" => USER["idx"],
        "content" => $specialties["name"]." 을(를) 장바구니에 ".$buy["count"]."개 구매하였습니다. ( 포인트 적립 $minus_point 포인트 )"
      ]);
      unset($_SESSION["basket"][$idx]);

      move("구매가 완료되었습니다.");
    });

    get("/delete/basket/$", function($idx){
      unset($_SESSION["basket"][$idx]);

      move("장바구니에서 제외되었습니다.");
    });

    get("/delete/buy_list/$", function($idx){
      buy::delete("idx = ?", $idx);
      move("삭제되었습니다.");
    });

    post("/review", function(){
      err(emp_vali(P), "모든 값을 입력해주세요.");

      tour::update("idx = ?", [P["tour_idx"]], [
        "status" => "complete"
      ]);

      $review = P;
      $review["write_idx"] = USER["idx"];
      unset($review["tour_idx"]);

      review::insert($review);
      point::insert([
        "point" => 10000,
        "user_idx" => USER["idx"]
      ]);
      move("리뷰 작성이 완료되었습니다.");
    });

    get("/load_alert", function(){
      $data = alert::findAll("user_idx = ? ORDER BY idx DESC", USER["idx"]);

      $data = array_map(function($v){
        $now_date = new DateTime();
        $time = new DateTime($v["create_dt"]);

        $diff = $now_date->diff($time);

        if($diff->d != 0){
          $v["time"] = date("Y-m-d", strtotime($v["create_dt"]));
        }else if($diff->h){
          $hour = $diff->h;
          $v["time"] = $hour."시간 전";
        }else{
          $min = $diff->i;
          $v["time"] = $min."분 전";
        }

        return $v;
      }, $data);

      echo json_encode($data);
    });

    get("/remove_alert/$", function($idx){
      alert::delete("idx = ?", $idx);
    });

  });

  middleware("type_guide", function(){

    get("/tour/accept/$", function($idx){
      $tour = tour::find("idx = ?", $idx);

      tour::update("idx = ?", [$idx], [
        "status" => "accept"
      ]);
      alert::insert([
        "user_idx" => $tour["user_idx"],
        "content" => USER["username"]."님에게 신청한 투어가 수락되었습니다."
      ]);

      move("수락되었습니다.");
    });

    get("/tour/reject/$", function($idx){
      $tour = tour::find("idx = ?", $idx);

      tour::delete("idx = ?", $idx);
      alert::insert([
        "user_idx" => $tour["user_idx"],
        "content" => USER["username"]."님에게 신청한 투어가 거절되었습니다."
      ]);

      move("거절되었습니다.");
    });
    
    get("/tour/complete/$/$", function($tour_idx, $complete){
      $chk = tour::find("idx = ? && complete = ?", [$tour_idx, $complete - 1]);
      err(!$chk, "이전 투어를 먼저 완료해주세요.");

      tour::update("idx = ?", [$tour_idx], [
        "complete" => $complete
      ]);
      alert::insert([
        "user_idx" => $chk["user_idx"],
        "content" => "투어( $complete 번 )가 완료되었습니다."
      ]);
      move("투어( $complete 번 )가 완료되었습니다.");
    });

  });

  middleware("type_admin", function(){

    post("/recommand/set", function(){
      err(emp_vali(P), "먼저 가이드를 선택해주세요.");
  
      user::update("idx = ?", [P["user_idx"]], [
        "recommand" => "on"
      ]);
      move("추천 가이드 설정이 완료되었습니다.");
     });
  
     post("/recommand/delete", function(){
      err(emp_vali(P), "먼저 가이드를 선택해주세요.");
  
      user::update("idx = ?", [P["user_idx"]], [
        "recommand" => null
      ]);
      move("추천 가이드 해제가 완료되었습니다.");
     });

  });

?>