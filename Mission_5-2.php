<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset = "UTF-8">
    <title>Mission_5-2</title>
</head>
    <body>
        
        <?php
        
        $dsn = 'データベース名';
    	$user = 'ユーザー名';
    	$password = 'パスワード';
    	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        $sql = "CREATE TABLE IF NOT EXISTS user"
    	." ("
    	. "id INT AUTO_INCREMENT PRIMARY KEY,"
    	. "name char(32),"
    	. "comment TEXT,"
    	. "datetime TEXT,"
    	. "password char(32)"
    	.");";
    	$stmt = $pdo->query($sql);
    	
    	function input_data($get_name, $get_comment, $get_datetime, $get_password){
    	    // DB接続設定
        	
        	//function関数内と外では同じ名前の変数でも別の変数と見なされるので、global宣言をする事で共通化させる
    	    global $sql,$pdo;
    	    $sql = $pdo -> prepare("INSERT INTO user (name, comment, datetime, password) 
        	VALUES (:name, :comment, :datetime, :password)");
        	$sql -> bindParam(':name', $user_name, PDO::PARAM_STR);
        	$sql -> bindParam(':comment', $user_comment, PDO::PARAM_STR);
        	$sql -> bindParam(':datetime', $user_datetime, PDO::PARAM_STR);
        	$sql -> bindParam(':password', $user_password, PDO::PARAM_STR);
        	$user_name = $get_name;
        	$user_comment = $get_comment; //好きな名前、好きな言葉は自分で決めること
        	$user_datetime = $get_datetime;
        	$user_password = $get_password;
        	$sql -> execute();
    	}
    	

        if (isset($_POST['text']) && $_POST['text'] != "" && isset($_POST['name']) &&  ($_POST['name']) != "" && empty($_POST['edit_out'])){
            //名前
            $name = $_POST["name"];
            //コメント
            $text = $_POST["text"];
            //投稿日時を記録
            $date = date("Y/m/d/ H:i:s");
            // 投稿番号を取得
            $num = count(file($filename)); // ファイルのデータの行数を数えて$numに代入
            $num++; // $num = $num + 1 と同じ意味
            //パスワードを記録
            $password = $_POST["password"];
            
            //sqlに書き込むための関数を呼び出す
            input_data($name, $text, $date, $password);

            echo "書き込み成功<br>";
            
        //書き換え機能
        }elseif(isset($_POST['text']) && $_POST['text'] != "" && isset($_POST['name']) 
        &&  ($_POST['name']) != "" && !empty($_POST['edit_out'])){
                $edit_num = ($_POST['edit_out']);
    	        $id = $edit_num; //変更する投稿番号
            	$user_name = $_POST["name"];
            	$user_comment = $_POST["text"];
            	$user_datetime = date("Y/m/d/ H:i:s");
            	$user_password = $_POST["password"];
            	$sql = 'UPDATE user SET name=:name,comment=:comment,datetime=:datetime,password=:password WHERE id=:id';
            	$stmt = $pdo->prepare($sql);
            	$stmt -> bindParam(':id', $id, PDO::PARAM_INT);
            	$stmt -> bindParam(':name', $user_name, PDO::PARAM_STR);
            	$stmt -> bindParam(':comment', $user_comment, PDO::PARAM_STR);
            	$stmt -> bindParam(':datetime', $user_datetime, PDO::PARAM_STR);
            	$stmt -> bindParam(':password', $user_password, PDO::PARAM_STR);
            	$stmt -> execute();
            	echo "編集成功<br>";
        	        
            
            
        //編集読み込み機能--
        }elseif(isset($_POST["edit"]) && !empty($_POST['edit_num'])){
            //入力された編集番号
            $edit_num = $_POST['edit_num'];
            //入力された編集用パスワード
            $edit_pass = ($_POST['edit_pass']);
            $error_num = 1;

            $sql = 'SELECT * FROM user';
        	$stmt = $pdo->query($sql);
        	$file_texts = $stmt->fetchAll();
        	foreach ($file_texts as $file_text){
        		//↓参考用
        		//echo "番号:" . $file_text['id'] . "　" ;
        		//echo "名前:" . $file_text['name'] . "　" ;
        		//echo "コメント:" . $file_text['comment'] . "　";
        		//echo "（" . $file_text['datetime'] . "）";
        		//echo "（" . $file_text['password'] . "）";

                
                //パスワード検証機能
                //パスワードが一致しているか調べ、一致していれば何もしない
                if (($edit_num == $file_text['id'] && $edit_pass == $file_text['password']) 
                && (!empty($file_text['password']) ||  $file_text['password'] == "0")){
                    $key = 1;
                }elseif($edit_num == $file_text['id'] && $edit_pass != $file_text['password'] && isset($file_text['password'])){
                    $error_num = 2;
                }elseif(($edit_num == $file_text['id'])  && (empty($file_text['password']) ||  $file_text['password'] != "0")){
                    $error_num = 3;
                }
        	}
            

            if($key == 1){
                foreach($file_texts as $file_text){
                //取り出したテキストを<>を区切りとして分割し、$exp_textsに代入。 配列であるためそのまま表示は出来ない事に注意。
                if (($edit_num == $file_text['id'] && $edit_pass == $file_text['password']) 
                && (!empty($file_text['password']) ||  $file_text['password'] == "0")){
                    $edit_num = $file_text['id'];
                    $edit_name = $file_text['name'];
                    $edit_text = $file_text['comment'];
                    $edit_pass = $file_text['password'];
                    $error_num = 0;
                }
                
            }
            }

                ////入力された番号の投稿が存在しなかった場合
                if ($error_num == 1){
                    //入力フォームに間違っていた値が残り続けるのでリセット
                    $edit_num = NULL ;
                    echo "入力された番号の投稿はありません<br>"; 
                }elseif($error_num == 2){
                    echo "パスワードを正しく入力してください<br>";
                }elseif($error_num == 3){
                    echo "パスワードが無い投稿は編集できません<br>";
                }else{
                    echo "編集中<br>";
                }
                
            //}
        //削除機能--
        //テキストファイル内からテキストを配列へ読み込み、その後テキストファイル自体を空にする
        //フォームで入力された削除番号と照らし合わせ、同じ物だった場合何もせず、異なる場合だったらテキストファイルにまた保存する
        //そしてそのテキストファイルを表示すれば、指定された番号のテキストだけ消されたように見える
        //このようにして削除機能が実現できる。
        
        //フォームで削除番号が入力され、かつ削除ボタンが押された時に動作
        }elseif(isset($_POST["delete"]) && !empty($_POST['delete_num'])){
            $delete_num = $_POST['delete_num'];
            $delete_pass = ($_POST['delete_pass']);
            $error_num = 1;
            
            $sql = 'SELECT * FROM user';
        	$stmt = $pdo->query($sql);
        	$file_texts = $stmt->fetchAll();
        	foreach ($file_texts as $file_text){
        		//↓参考用
        		//echo "番号:" . $file_text['id'] . "　" ;
        		//echo "名前:" . $file_text['name'] . "　" ;
        		//echo "コメント:" . $file_text['comment'] . "　";
        		//echo "（" . $file_text['datetime'] . "）";
        		//echo "（" . $file_text['password'] . "）";

                
                //パスワード検証機能
                //パスワードが一致しているか調べ、一致していれば何もしない
                if (($delete_num == $file_text['id'] && $delete_pass == $file_text['password']) 
                && (!empty($file_text['password']) ||  $file_text['password'] == "0")){
                    $key = 1;
                }elseif($delete_num == $file_text['id'] && $delete_pass != $file_text['password'] && isset($file_text['password'])){
                    $error_num = 2;
                }elseif(($delete_num == $file_text['id'])  && (empty($file_text['password']) ||  $file_text['password'] != "0")){
                    $error_num = 3;
                }
        	}
            

                if($key == 1){
                    $id = $delete_num;
                	$sql = 'delete from user where id=:id';
                	$stmt = $pdo->prepare($sql);
                	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
                	$stmt->execute();
                	$error_num = 0;
            }
            
            if ($error_num == 1){
                    //入力フォームに間違っていた値が残り続けるのでリセット
                    $delete_num = NULL ;
                    echo "入力された番号の投稿はありません<br>"; 
                }elseif($error_num == 2){
                    echo "パスワードを正しく入力してください<br>";
                }elseif($error_num == 3){
                    echo "パスワードが無い投稿は削除できません<br>";
                }else{
                    echo "削除成功<br>";
            }
            
        
        //何も入力されなかった時用
        }elseif(isset($_POST["submit"]) && (empty($_POST['text']) or empty($_POST['name']))){
            echo "名前やコメントを入力してください<br>";
        }elseif(isset($_POST["delete"]) && (empty($_POST['delete_num']))){
            echo "削除番号を入力してください<br>";
        }elseif(isset($_POST["edit"]) && (empty($_POST['edit_num']))){
            echo "編集番号を入力してください<br>";
        }
        
        
        ?>
        <!-- formは一つにまとめる。（複数フォームを用意すると、phpで読み込む際に挙動がおかしくなる） -->
        <form action = "" method = "POST">
            <input type = "name" name = "name" placeholder ="名前" value = "<?php echo $edit_name; ?>">
            <input type = "text" name = "text" placeholder ="コメント" value = "<?php echo $edit_text; ?>">
            <input type = "password" name = "password" placeholder ="パスワード（任意）" value = "<?php echo $edit_pass; ?>">
            <input type = "submit" name = "submit">
            <br>
        <!--削除用form-->
            <input type = "text" name = "delete_num" placeholder ="削除対象番号">
            <input type = "password" name = "delete_pass" placeholder ="パスワード（必須）">
            <input type = "submit" name = "delete" value ="削除">
            <br>
        <!--編集用form-->
            <input type = "text" name = "edit_num" placeholder ="編集対象番号">
            <input type = "password" name = "edit_pass" placeholder ="パスワード（必須）">
            <input type = "submit" name = "edit" value = "編集">
            <input type = "text" name = "edit_out" readonly value = "<?php echo $edit_num; ?>">
        </form>

        
        <?php
            //sql内のカラムからデータを表示
            $sql = 'SELECT * FROM user';
        	$stmt = $pdo->query($sql);
        	$results = $stmt->fetchAll();
        	foreach ($results as $row){
        		//$rowの中にはテーブルのカラム名が入る
        		echo "番号:" . $row['id'] . "　" ;
        		echo "名前:" . $row['name'] . "　" ;
        		echo "コメント:" . $row['comment'] . "　";
        		echo "（" . $row['datetime'] . "）";
        	echo "<hr>";
        	}
        ?>
        
    </body>
    
</html>