REM pre-commit.bat hook for MantisBT integration
set REPOS=%1
set TXN=%2
SET DETAILS_FILE=D:\svn\no1\log\svnfile
SET LOG_FILE=D:\svn\no1\log\svnfile_Log
set APR_ICONV_PATH=D:\EasyPHP\Subversion\iconv
set PHPRC=D:\EasyPHP\apache
set path=%path%;D:\EasyPHP\php5;D:\EasyPHP\Subversion\bin;

rem ��ע����Ϣд�뵽 DETAILS_FILE
svnlook log "%REPOS%" -t "%TXN%"  >>%DETAILS_FILE%

rem ִ��checkin_svn_pre_commit.php ����У��ȱ�ݵ��� �Ƿ���ȱ��ϵͳ�д��ڣ���������ڣ����������Ϣ�� log_file��
php.exe D:\EasyPHP\www\mantis\core\checkin_svn_pre_commit.php <%DETAILS_FILE%>%LOG_FILE%

rem У��log_file���Ƿ������ݣ���������ݣ�ת�뵽������ʾ
FOR /F "tokens=1,2* delims= " %%i in (%LOG_FILE%) do goto err

del %DETAILS_FILE%
del %LOG_FILE%
exit 0

:err
echo ȱ�ݵ������ڣ�����ȷ��дȱ�ݵ��ţ��ύ��ֹ! 1>&2
del %DETAILS_FILE%
del %LOG_FILE%
exit 1

