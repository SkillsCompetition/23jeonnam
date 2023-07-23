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
    view("index");
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
                      AND (tour.status = ? OR tour.status = ? OR tour.status IS NULL)
                      GROUP BY guide.idx
                      ORDER BY score_avg DESC", ["guide", "reject", "complete"])->fetchAll();

      echo json_encode($data);
    });

    get("/buy", function(){
      view("buy");
    });

    get("/buy_list", function(){
      $buy_list = specialties::all();
      
      echo json_encode($buy_list);
    });

  });

  middleware("type_user", function(){

    post("/tour", function(){
      $data = P;

      $data["user_idx"] = USER["idx"];

      tour::insert($data);
      move("투어 신청이 완료되었습니다.");
    });

  });

?>