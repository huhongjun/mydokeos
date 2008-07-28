<?php

	/*很多人都以为htmlentities跟htmlspecialchars的功能是一样的，都是格式化html代码的，
	 * 我以前也曾这么认为，但是今天我发现并不是这样的。
	 * 这两个函数在格式化带有英文字符的html代码的时候基本没啥问题，但是htmlentities对中文字符也不放过，
	 * 这样得出来的结果是中文字符部分变为一堆乱码。当时做英文站的时候根本就没觉察到这个问题，
	 * 而今天公司的一个收藏站却因为有有非英文字符而出现了问题，我最终查出来是htmlentities这个函数的问题，
	 * 同时我也找到了htmlspecialchars这个函数。对于这两个函数，php手册上都是英文做的解释，
	 * 其中在htmlentities函数的说明部分有这么一段英文：
	 * This function is identical to htmlspecialchars() in all ways, except with htmlentities(), 
	 * all characters which have HTML character entity equivalents are translated into these entities.
	 * 从这句话中我们也可以看出来这两个函数虽然基本功能差不多，但是还是有细微的差别在里面的。再仔细看htmlspecialchars函数里面的一段话：
	 * The translations performed are:
	 * ‘&’ (ampersand) becomes ‘&’
	 * ‘”‘ (double quote) becomes ‘”‘ when ENT_NOQUOTES is not set.
	 * ”’ (single quote) becomes ”’ only when ENT_QUOTES is set.
	 * ‘<’ (less than) becomes ‘<’
	 * ‘>’ (greater than) becomes ‘>’
	 * 可以了解到htmlspecialchars只转化上面这几个html代码，而htmlentities却会转化所有的html代码，
	 * 连同里面的它无法识别的中文字符也给转化了。我们可以拿一个简单的例子来做比较：*/
	$str='<a href="test.html">测试页面</a>';
	echo htmlentities($str);
	
	$str='<a href="test.html">测试页面</a>';
	echo htmlspecialchars($str);

?>

