<?php
	
	// 设置include路径
	set_include_path('.' . PATH_SEPARATOR . './msg/'. PATH_SEPARATOR . get_include_path());
	set_include_path('.' . PATH_SEPARATOR . './error/'. PATH_SEPARATOR . get_include_path());
	// 导入数据库连接
	require('dbcon.php');
	
	// 执行保存后的校验 进行判断
	if ($_GET['action']=='save') {
		/*
		 * 函数:trim()截去字符串首尾的空格。本函数返回字符串 string 首尾的空白字符去除后的字符串。
		 * 语法: string trim(string str);返回值: 字符串;函数种类: 资料处理
		 */
		// 清除空格 
		$_POST['name'] = trim($_POST['name']);
		$_POST['content'] = trim($_POST['content']);
		// 如果魔术引号关闭使用addslashes转换
		if (!get_magic_quotes_gpc()) {
			/*
			 * 函数:get_magic_quotes_gpc() 取得 PHP 环境变量 magic_quotes_gpc 的值。
			 * 返回值: 长整数 函数种类: PHP 系统功能 
			 * 内容说明: 本函数取得 PHP 环境配置的变量 magic_quotes_gpc (GPC, Get/Post/Cookie) 值。
			 * 返回 0 表示关闭本功能；返回 1 表示本功能打开。
			 * 当 magic_quotes_gpc 打开时，所有的 ' (单引号), " (双引号), \ (反斜线) and 
			 * 空字符会自动转为含有反斜线的溢出字符。 
			 * 
			 * 函数:AddSlashes()字符串加入斜线。语法: string addslashes(string str);返回值: 字符串 函数种类: 资料处理
			 * 内容说明: 本函数使需要让数据库处理的字符串，引号的部份加上斜线，以供数据库查询 (query) 能顺利运作。
			 * 这些会被改的字符包括单引号 (')、双引号 (")、反斜线 backslash (\) 以及空字符 NUL (the null byte)。
			 * 
			 */
			$_POST['name'] = addslashes($_POST['name']);
			$_POST['content'] = addslashes($_POST['content']);
		}
		// 判断名字和内容是否全部填写
		if (!$_POST['name'] || !$_POST['content']) {
//			echo '请输入用户名和内容！';
//			echo '<input type="button" name="back" value="返回" onclick="javascript:history.back();"/>';
			require 'error_empty.php';
			/* 函数:exit()结束 PHP 程序。语法: void exit(void);返回值: 无  函数种类: PHP 系统功能
			   内容说明 本函数直接结束 PHP 程序 (Script)。不需输入参数，亦无返回值。*/
			exit;
		}
		// 判断用户名是否超出长度(数据库php_test表contents字段name定义)
		// Strlen()函数是检查字符串长度的
		if (strlen($_POST['name'])>16) {
//			echo '用户名超出长度！';
//			echo '<input type="button" name="back" value="返回" onclick="javascript:history.back();"/>';
			require 'error_name.php';
			exit;
		}
		// 判断内容是否超出长度(数据库php_test表contents字段content定义)
		if (strlen($_POST['content'])>40) {
//			echo '内容超出长度！';
//			echo '<input type="button" name="back" value="返回" onclick="javascript:history.back();"/>';
			require 'error_content.php';
			exit;
		}
		
		// 保存新增记录 向数据库添加记录 insert SQL语句
		$sql = "insert into contents (name,content,insert_time) values ('".$_POST['name']."','".$_POST['content']."',now())";
		// 执行SQL语句
		mysql_query($sql,$link);
//		echo '添加成功！ <a href="list.php">查看留言</a>';
		include 'add_success.php';
		exit;
	}
	echo '
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>提交留言</title>
			<link rel="stylesheet" type="text/css" href="style.css" media="all" />
		</head>
		
		<br><br><br><br><br><br><br><br>';
		
		echo '<body  bgcolor="honeydew">
			<table align="center" width="500" border="0" cellspacing="0" cellpadding="0" class="tb">
			   <tr>
			      <td align="center" class="bg"><b>【提交留言】</b></td>
			   </tr>
			
			  <tr>
			    <td>
			    	<form id="save" name="save" method="post"  action="add.php?action=save">
				        <table align="center" width="500" border="0" cellspacing="0" cellpadding="0" bgcolor="lightcyan">
				          <tr align="center">
				            <td width="10%" align="right"><font size="4">名字：</font></td>
				            <td width="20%" align="left"><input type="text" name="name" value="'; echo htmlspecialchars($rs['name']); echo'" size="50"/></td>';
				          echo '</tr>
				          <tr align="center">
				            <td width="10%" align="right"><font size="4">内容：</font></td>
				            <td width="40%"align="left"><textarea name="content" cols="40" rows="6">'; echo htmlspecialchars($rs['content']); echo'</textarea></td>';
				          echo '</tr>  
				          <tr>
				            <td width="10%"></td>
				            <td width="10%">
					            <input type="submit" name="save" value="提 交"  />
					            <input type="button" name="back" value="返回" onclick="javascript:history.back();"/>
				            </td>
				          </tr>
				        </table>
			      </form>
			     </td>
			  </tr>
			</table>
		</body>
	</html>'
?>