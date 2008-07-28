<?php

	set_include_path('.' . PATH_SEPARATOR . './msg/'. PATH_SEPARATOR . get_include_path());
	set_include_path('.' . PATH_SEPARATOR . './error/'. PATH_SEPARATOR . get_include_path());
	
	// 导入数据库连接 dbcon.php
	require('dbcon.php');

	/*
	 * 函数:mysql_query() 
	 * 
	 * 送出一个 query 字符串。语法: int mysql_query(string query, int [link_identifier]);
	 * 返回值: 整数           函数种类: 数据库功能
	 * 内容说明 本函数送出 query 字符串供 MySQL 做相关的处理或者执行。
	 * 若没有指定 link_identifier 参数，则程序会自动寻找最近打开的 ID。
	 * 当 query 查询字符串是 UPDATE、INSERT 及 DELETE 时，返回的可能是 true 或者 false；
	 * 查询的字符串是 SELECT 则返回新的 ID 值。joey@samaritan.com (09-Feb-1999) 指出，
	 * 当返回 false 时，并不是执行成功但无返回值，而是查询的字符串有错误。
	 * 
	 */
	if ($_GET['id'] != 0) {
			// 根据参数id删除对应记录
			mysql_query('delete from contents where id='.intval($_GET['id']));
			
		//	echo '已删除！<a href="list.php">查看留言</a>';
			require 'del_success.php';
	} else {
		require 'error_del.php';
	}

	
?>