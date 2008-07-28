<?php

	// PHP只是以HTTP协议将HTML文档的标头送到浏览器，告诉浏览器具体怎么处理这个页面
	@header('content-Type: text/html; charset=utf-8');
	
	// 连接数据库服务器  如果不成功则报错
	$link = mysql_connect('localhost', 'root', 'root') or die('数据库连接错误: ' . mysql_error());  
	// die()函数的作用是：退出当前脚本程序并输出一段信息。die()函数与exit()函数的功能大致相同。
	/* 函数:exit()结束 PHP 程序。语法: void exit(void);返回值: 无  函数种类: PHP 系统功能  内容说明 本函数直接结束 PHP 程序 (Script)。不需输入参数，亦无返回值。*/
	// die(message) 必要参数。在退出脚本程序运行之前指定需要输出的信息和表示状态的数字[status number]。这个状态数字将不会被写入结果中
	// string mysql_error ( [resource link_identifier])返回上一个 MySQL 函数的错误文本，如果没有出错则返回 ''（空字符串）。如果没有指定连接资源号，则使用上一个成功打开的连接从 MySQL 服务器提取错误信息。
	
	// 选择数据库  
	mysql_select_db('php_eg');
	
	/*
	 * mysql_query(); 送出一个 query 字符串。
	 * 本函数送出 query 字符串供 MySQL 做相关的处理或者执行。
	 * 若没有指定 link_identifier 参数，则程序会自动寻找最近打开的 ID。
	 * 当 query 查询字符串是 UPDATE、INSERT 及 DELETE 时，返回的可能是 true 或者 false；
	 * 查询的字符串是 SELECT 则返回新的 ID 值。joey@samaritan.com (09-Feb-1999) 指出，
	 * 当返回 false 时，并不是执行成功但无返回值，而是查询的字符串有错误。
	 */
	
	// 解决中文乱码问题 在创建数据时候就把字符集设置成utf8所以不需要下面的代码
//	mysql_query("set names utf-8"); 
?>