<?php 
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>所有留言</title>
		<link rel="stylesheet" type="text/css" href="style.css" media="all" />
	</head>

	<body bgcolor="honeydew">
	<br><br><br><br><br>
	<table align="center">
		<tr>
			<td>
				<a href="add.php"><H2>发表留言</H2></a>
			</td>
		</tr>
	<table>';

	
		// 获得数据库连接
		require ('dbcon.php');
		// 查询数据
		$result = mysql_query("SELECT * FROM contents ORDER BY id desc"); 
		
		/*
		 * mysql_fetch_array()返回数组资料。  语法: array mysql_fetch_array(int result, int [result_typ]);
		 * 返回值: 数组                         函数种类: 数据库功能
		 * 本函数用来将查询结果 result 拆到数组变量中。若 result 没有资料，则返回 false 值。
		 * 而本函数可以说是 mysql_fetch_row() 的加强函数，除可以将返回列及数字索引放入数组之外，还可以将文字索引放入数组中。
		 * 若是好几个返回字段都是相同的文字名称，则最后一个置入的字段有效，
		 * 解决方法是使用数字索引或者为这些同名的字段 (column) 取别名 (alias)。
		 * 注意的是使用本函数的处理速度其实不会比 mysql_fetch_row() 函数慢，要用哪个函数还是看使用的需求决定。
		 * 参数 result_typ 是一个常量值，
		 * 有以下几种常量 MYSQL_ASSOC、MYSQL_NUM 与 MYSQL_BOTH。
		 * 
		 */
		
		// 取数据 用while循环遍历
		while ($rs = mysql_fetch_array($result, MYSQL_BOTH)) { 

	echo '<table align="center" width="800" border="0" cellspacing="0" cellpadding="0" class="tb" bgcolor="seashell">
	  <tr align="center">
	    <td align="right" class="bg" width="10%"><font size="4">姓名：</font></td>
	    <td align="left" bgcolor="lightcyan" width="10%"><p style="font-weight:normal"><font color="#0000a0">['; echo htmlspecialchars($rs['name']); echo']</font></p></td>';

//		    PHP中htmlentities跟htmlspecialchars的区别
//			htmlentities跟htmlspecialchars的功能都是格式化html代码的
//			这两个函数在格式化带有英文字符的html代码的时候基本没啥问题
//			但htmlentities对中文字符也不放过，这样得出来的结果是中文字符部分变为一堆乱码。
//			对于中文的处理用函数htmlspecialchars

		
   echo '<td align="right" class="bg" width="10%"><font size="4">留言:</font></td>
	    <td align="left" bgcolor="lightcyan" width="30%"><p style="font-weight:normal"><font color="#0080ff">★'; echo htmlspecialchars($rs['content']);echo'★</font></p></td>'; 


   echo '<td align="right" class="bg" width="15%"><font size="4">发表时间：</font></td>
	    <td align="left" bgcolor="lightcyan" width="20%"><p style="font-weight:normal"><font color="#ff8000">"'; echo htmlentities($rs['insert_time']);echo'"</font></p></td>'; 


   echo '<td align="center" class="bg" width="5%">
	    	<a href="edit.php?id='; echo $rs['id'];echo'"><b>修改</b></a> '; 
   echo '<a href="delete.php?id='; echo $rs["id"];echo'" 
	    onclick="javascript:if(confirm( \'删除后不可恢复,确认要删除吗？\'))return   true;return   false;">
	    <b>删除</b> </a></td></tr></table>'; 

		}
		/*
		 * mysql_free_result($result);释放返回占用内存。
		 * 语法: boolean mysql_free_result(int result);
		 * 返回值: 布尔值      * 函数种类: 数据库功能
		 * 内容说明: 本函数可以释放目前 MySQL 数据库 query 返回所占用的内存。
		 * 一般只有在非常担心在内存的使用上可能会不足的情形下才会用本函数。
		 * PHP 程序会在结束时自动释放。
		 * 
		 */
		mysql_free_result($result);

	echo '<br/><br/><br/><br/><br/></body></html>' 
	?>