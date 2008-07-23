<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>留言本--保存留言</title>
	</head>
	<body>
		<form action="index.php" method="post" name="name1">
			<tr>
				<?php
					$name=$_POST['user_name'];
					$content=$_POST['post_contents'];
					$conn=mysql_connect("localhost:3306", "root", "root");
					mysql_query("set names utf-8"); //解决中文乱码问题
					mysql_select_db("php_test");
					$exec="insert into contents (name,content) values ('".$_POST['user_name']."','".$_POST['post_contents']."')";
					$result=mysql_query($exec);
					?>
				<?php
					echo "留言成功，感谢！";
				?>
			</tr>
			<tr>
				<input type="submit" name="" value="看留言">
			</tr>
		</form>
	</body>
</html>
