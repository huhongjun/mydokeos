REM Post-commit hook for MantisBT integration

rem REPOS svn版本库的路径
rem REV 版本信息
rem DETAILS_FILE 是版本库的目录，在版本库目录下建立一个log的目录，用来存放日志
rem set PHPRC=D:\EasyPHP\apache 默认会使用windows 目录下的 php.ini 修改后，读取 easyphp\apach 目录下 

rem 拷贝了 checkin.php 进行修改，重新命名 为 checkin_svn.php 如果有中文传输，需要修改 checkin_svn.php
rem 增加抓换函数 $t_comment = mb_convert_encoding($t_comment, "utf-8","GB2312"); 
rem 修改位置在 “ foreach ( $t_issues as $t_issue_id ) { ”前面
rem mb_convert_encoding 此函数需要扩展文件php_mbstring.dll的支持~~  
rem $t_comment   = iconv("UTF-8","GB2312//TRANSLIT",$t_comment);  据说这个编码转换效率更高

SET REPOS=%1
SET REV=%2
SET DETAILS_FILE=E:\Subversion\Repos\elcms\log\svnfile_%REV%
SET LOG_FILE=E:\Subversion\Repos\elcms\log\svnfile_%REV%_Log
set APR_ICONV_PATH=E:\Subversion\iconv
set PHPRC=C:\xampp\apache
set path=%path%;C:\xampp\php;E:\Subversion\bin;

echo ****** Source code change ******>>%DETAILS_FILE%

echo SVN 修改人: >>%DETAILS_FILE%
svnlook author -r %REV% %REPOS%>>%DETAILS_FILE%

echo SVN 修改日期: >>%DETAILS_FILE%
svnlook date -r %REV% %REPOS%>>%DETAILS_FILE%

echo SVN 版本:  >>%DETAILS_FILE%
echo %REV%>>%DETAILS_FILE%

echo SVN 提交注释: >>%DETAILS_FILE%
svnlook log -r %REV% %REPOS%>>%DETAILS_FILE%

echo SVN 修改明细: >>%DETAILS_FILE%
svnlook diff -r %REV% %REPOS%>>%DETAILS_FILE%

rem php.exe .\checkin_svn.php 信息文件日志文件

rem unix-like system
rem C:\xampp\php\php.exe C:\mantis\core\checkin_svn.php<%DETAILS_FILE%>%LOG_FILE%
rem WINDOWS equivalent.
rem type <%DETAILS_FILE%>|C:\xampp\php\php.exe C:\mantis\core\checkin_svn.php

del %DETAILS_FILE%
del %LOG_FILE%
