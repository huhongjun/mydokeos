<?php

	/*
	 * require和include就只是错误处理的方式不同。
	 * require是无条件导入。include是有条件导入。
	 * require是静态导入，include是动态装入。
	 * 如果 require的文件不存在，程序停止运行 如果 include的文件不存在，给出错误信息后还要运行
	 * include() && include_once()：出错时试图继续执行；require() && require_once()：出错时中止脚本运行。
	 * 
	 * 总结：
	 * 1.如果 require的文件不存在，程序停止运行 如果 include的文件不存在，给出错误信息后还要运行
	 * 2.require用相对路径的时候当A引用B，而B又引用了其他文件C时，C的路径如果是相对路径，则是相对于A的路径，而不是相对于B的
	 * 3. include() && include_once()：程序执行到那里才执行require() && require_once()：不管放在页面的哪个地方都先执行
	 */

	require 'list.php';
?>