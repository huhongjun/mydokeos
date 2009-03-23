REM pre-commit.bat hook for MantisBT integration
set REPOS=%1
set TXN=%2
SET DETAILS_FILE=E:\Subversion\Repos\elcms\log\svnfile
SET LOG_FILE=E:\Subversion\Repos\elcms\log\svnfile_Log
set APR_ICONV_PATH=E:\Subversion\iconv
set PHPRC=C:\xampp\apache
set path=%path%;C:\xampp\php;E:\Subversion\bin;

rem 把注释信息写入到 DETAILS_FILE
svnlook log "%REPOS%" -t "%TXN%"  >>%DETAILS_FILE%

rem 执行checkin_svn_pre_commit.php 进行校验缺陷单号 是否在缺陷系统中存在，如果不存在，返回输出信息到 log_file中
C:\xampp\php\php.exe C:\mantis\core\checkin_svn_pre_commit.php <%DETAILS_FILE%>%LOG_FILE%

rem 校验log_file中是否有内容，如果有内容，转入到出错提示
FOR /F "tokens=1,2* delims= " %%i in (%LOG_FILE%) do goto err

del %DETAILS_FILE%
del %LOG_FILE%
exit 0

:err
echo 缺陷单不存在，请正确填写缺陷单号，提交终止! 1>&2
del %DETAILS_FILE%
del %LOG_FILE%
exit 1
