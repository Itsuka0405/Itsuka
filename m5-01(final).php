<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission5-1</title>
    </head>
<body>
    <?php
    //データベース
    $dsn = 'データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

  //テーブルを作成
  $sql = "CREATE TABLE IF NOT EXISTS tbtest2" //tbtestがまだデータベースに存在しない場合、tbtestを作成する
  //登録する項目
  ." ("
  . "id INT AUTO_INCREMENT PRIMARY KEY," 
  . "name char(32)," 
  . "comment TEXT," 
  . "date TEXT," 
  . "password TEXT" 
  .");";
  $stmt = $pdo->query($sql); 
      
  
  //日付データ
  $date = date("Y/m/d H:i:s");
  
      //投稿フォーム
      //削除フォームと編集フォームがともに空の場合
      if(empty($_POST["delete"]) && empty($_POST["edit_num"]))
      {
          //新規投稿の場合
          //名前とコメントどちらかに入力がある場合
          if((!empty($_POST["name"]) || !empty($_POST["comment"])) && !empty($_POST["pass1"]) 
          && empty($_POST["pass2"])
          && empty($_POST["pass3"]))
          {
              $name = $_POST["name"]; 
              $comment = $_POST["comment"];
              $pass1 = $_POST["pass1"];
              $sql = $pdo -> prepare("INSERT INTO tb1 (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
              $sql -> bindParam(':name', $name, PDO::PARAM_STR);
              $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
              $sql -> bindParam(':date', $date, PDO::PARAM_STR);
              $sql -> bindParam(':password', $pass1, PDO::PARAM_STR);
              $sql -> execute(); 
              $pass1 = "";
          }
      }

      //編集機能として投稿された場合
//編集フォームとそのパスワード(pass3)にのみ入力有、削除フォームは空
if(!empty($_POST["edit_num"])&&!empty($_POST["pass3"])&&empty($_POST["delete"])){
    //テーブルに登録されたデータの取得→画面表示
    $sql = 'SELECT * FROM tbtest2';
    $stmt = $pdo->query($sql); //$stml=PDOStatementオブジェクトを表す。$stml=$pdo->query($sql)はSQLの実行を示している。
    $results = $stmt->fetchAll(); //fetchALL=全ての結果行を含む配列を繰り返す。
    $edit_num=$_POST["edit_num"];
    foreach ($results as $row){ //行の数だけ繰り返す
        if($row['id']==$edit_num && $row['password']== $_POST["pass3"])  { //投稿番号と一致したときに画面に表示
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            if(empty($_POST["name"]) && empty($_POST["comment"])){
                $name = $row["name"];
                $comment = $row["comment"];
            }
        }
        else if($row['id']== $edit_num && $row['password']!=$_POST["pass3"]) {  //パスワードが一致しなかった時
            $edit_num="";
            $name = "";
            $comment = "";
        }    
    }
}
//全てのパス空 or 削除フォーム入力かつ削除パス空 or 編集フォーム入力かつ削除パス空
  //パスワードが入力されていないと送信されない
  if((empty($_POST["pass1"]) && empty($_POST["pass2"]) && empty($_POST["pass3"])) ||(!empty($_POST["delete"]) && empty($_POST["pass2"])) || (!empty($_POST["edit_num"]) && empty($_POST["pass3"])) ||(!empty($_POST["pass1"]) 
  && !empty($_POST["pass3"]))){
    $name="";
    $comment="";
    $edit_num="";
}
?>

<form action="" method="post">
    ー小林のページー<br><br>
    【投稿フォーム】<br>
      <input type="text" name="name" value="<?php if(!empty($_POST["edit_num"])){echo $name;}?>" placeholder="名前"><br>
      <input type="text" name="comment" value="<?php if(!empty($_POST["edit_num"])){echo $comment;}?>" placeholder="コメント"><br>
      <input type="text" name="pass1" placeholder="パスワード">
      <input type="submit" name="submit" value="投稿"><br> <br>
      
    【削除フォーム】<br>
      <input type="text" name="delete" placeholder="削除番号"><br>
      <input type="text" name="pass2" placeholder="パスワード">
      <input type="submit" name="submit_del" value="削除"><br><br>
      
    【編集フォーム】<br>
      <input type="text" name="edit_num" value="<?php if(!empty($_POST["edit_num"])){echo $edit_num;}?>" placeholder="編集対象番号"><br>
      <input type="text" name="pass3" placeholder="パスワード">
      <input type="submit" name="edit" value="編集"><br><br>
     
      <input type="hidden" name="edit" >
  </form>

  <?php
  //投稿一覧
  //新規投稿時
  if(empty($_POST["delete"]) && empty($_POST["pass2"]) && empty($_POST["edit"]) && empty($_POST["pass3"])){
      if(empty($_POST["name"]) && empty($_POST["comment"]) && empty($_POST["pass1"])){
          echo "データが入力されていません。<br>";
      }
  }
  
  //投稿フォームのデータが入力されている時
  if(!empty($_POST["name"]) && !empty($_POST["comment"])){
      echo $_POST["name"]. "". $_POST["comment"]. " を受け付けました。<br>";
  }


  //削除フォームにのみ入力有
  if(!empty($_POST["delete"]) && empty($_POST["edit_num"]) && !empty($_POST["pass2"]) && empty($_POST["pass1"])){
                
    $id = $_POST["delete"];  //削除番号を変数に代入
    $password=$_POST["pass2"]; //削除パスワードを変数に代入

    //入力した投稿番号のデータ削除
    $sql = 'delete from tbtest2 where id=:id AND password=:password'; //指定した投稿番号削除
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();

    //データベースのテーブル一覧を表示
    $sql = 'SELECT * FROM tbtest2';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();

    //テーブルを削除
    $sql = 'DROP TABLE tbtest2';
    $stmt = $pdo->query($sql);

    //削除し、テーブルを作成し直す。削除更新後の画面を表示
    $sql = "CREATE TABLE IF NOT EXISTS tbtest2"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "date TEXT,"
    . "password TEXT"
    .");";
    $stmt = $pdo->query($sql);
    $id=1;
    foreach ($results as $row){
    $name=$row['name'];
    $comment=$row['comment'];
    $date=$row['date'];
    $password=$row['password'];   
    $sql = $pdo -> prepare("INSERT INTO tbtest2 (id, name, comment , date , password) VALUES (:id,:name, :comment, :date, :password )");
    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
    $sql -> bindParam(':date', $date, PDO::PARAM_STR);
    $sql -> bindParam(':password', $password, PDO::PARAM_STR);
    $sql ->bindParam(':id', $id, PDO::PARAM_INT);
    $sql -> execute();
    $id=$id+1;
        }
}

 //編集フォームから取得した名前とコメントを編集して書き込む      
 if(!empty($_POST["edit_num"])&& empty($_POST["delete"])&&(!empty($_POST["name"])||!empty($_POST["comment"]))&&!empty($_POST["pass3"]) && empty($_POST["pass1"])){
     //データベースのテーブル一覧を表示
     $sql = 'SELECT * FROM tbtest2';
     $stmt = $pdo->query($sql);
     $results = $stmt->fetchAll();
     $idmax=count($results);
     $id = $_POST["edit_num"]; 
     if($idmax>=$id){
     $name = $_POST["name"];
     $comment = $_POST["comment"]; 
     $password = $_POST["pass3"];

     //データベースの編集
     $sql = 'UPDATE tbtest2 SET name=:name,comment=:comment,date=:date, password=:password WHERE id=:id';
     $stmt = $pdo->prepare($sql);
     $stmt->bindParam(':name', $name, PDO::PARAM_STR);
     $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
     $stmt->bindParam(':date', $date, PDO::PARAM_STR);
     $stmt->bindParam(':password', $password, PDO::PARAM_STR);
     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
     $stmt->execute();
     }
     //含まない場合
     else{
     $name=$row['name'];
     $comment=$row['comment'];
     $date=$row['date'];
     $password=$row['password'];   
     $sql = $pdo -> prepare("INSERT INTO tbtest2 (id,name, comment , date , password) VALUES (:id,:name, :comment, :date, :password )");
     $sql -> bindParam(':name', $name, PDO::PARAM_STR);
     $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
     $sql -> bindParam(':date', $date, PDO::PARAM_STR);
     $sql -> bindParam(':password', $password, PDO::PARAM_STR);
     $sql ->bindParam(':id', $id, PDO::PARAM_INT);
     $sql -> execute();
     $name="";
     $comment="";
     $date="";
     $password="";
     }
 }


?>
</body>
</html>
