<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>留言本--保存留言</title>
	</head>
	<body>
		<form action="post.htm" method="post" name="name1">
			<tr>
				<?php
					$conn=mysql_connect ("localhost:3306", "root", "root"); //打开MySQL服务器连接
					mysql_select_db("php_test"); //链接数据库
					mysql_query("set names utf-8"); //解决中文乱码问题
					$exec="select * from contents"; //sql语句
					$result=mysql_query($exec); //执行sql语句，返回结果
					while($rs=mysql_fetch_object($result))
					{
						echo "<table><tr><td>姓名:".$rs->name."</td></tr>";
						echo "<tr><td>留言:".$rs->content."</td></tr></table><br/>";
					}
				?>
			</tr>
			<tr>
				<input type="submit" name="" value="去留言">
			</tr>
		</form>
	</body>
</html>
