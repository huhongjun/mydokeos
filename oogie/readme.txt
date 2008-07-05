来源：
	取自dokeos的ppt2png目录，引入eclipse作为独立项目调试；
	实际上在dokeos中也是作为外部程序用exec函数调用的；
原理：
	本机上运行open office，通过JavaSDK远程操作，将ppt转换为图片，word转换为html
	
存在问题：
	中文文件名的乱码

main\inc\lib\ppt2png ==> oogie 文件转换（java类远程连接openoffice）

	用exec函数执行
	
	cd E:\xampp-lms\htdocs\dokeos-1.8.5\\main\inc\lib\ppt2png
	java -cp .;jodconverter-2.2.1.jar;jodconverter-cli-2.2.1.jar DokeosConverter -p 2002 -d woogie "E:/xampp-lms/htdocs/dokeos-1.8.5//courses/ZZXWX/document//xyxxyxxx-huhj.doc" "E:/xampp-lms/htdocs/dokeos-1.8.5//courses/ZZXWX/document/xyxxyxxx-huhj3/xyxxyxxx-huhj.html" 
	
	java -cp E:/xampp-lms/htdocs/dokeos-1.8.5//main/inc/lib/ppt2png;E:/xampp-lms/htdocs/dokeos-1.8.5//main/inc/lib/ppt2png/jodconverter-2.2.1.jar;E:/xampp-lms/htdocs/dokeos-1.8.5//main/inc/lib/ppt2png/jodconverter-cli-2.2.1.jar DokeosConverter -p 2002 -d woogie "E:/xampp-lms/htdocs/dokeos-1.8.5//courses/ZZXWX/document//huhj.odt" "E:/xampp-lms/htdocs/dokeos-1.8.5//courses/ZZXWX/document/xyxxyxxx-huhj9/xyxxyxxx-huhj.html" 
	
	
C:\OpenOffice.org 2.4\program\soffice -accept=socket,host=localhost,port=2002;urp;StarOffice.ServiceManager	

eclipse调试
	DocumentConverter
	DokeosConverter：