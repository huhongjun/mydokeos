REM Post-commit hook for MantisBT integration

rem REPOS svn�汾���·��
rem REV �汾��Ϣ
rem DETAILS_FILE �ǰ汾���Ŀ¼���ڰ汾��Ŀ¼�½���һ��log��Ŀ¼�����������־
rem set PHPRC=D:\EasyPHP\apache Ĭ�ϻ�ʹ��windows Ŀ¼�µ� php.ini �޸ĺ󣬶�ȡ easyphp\apach Ŀ¼�� 

rem ������ checkin.php �����޸ģ��������� Ϊ checkin_svn.php ��������Ĵ��䣬��Ҫ�޸� checkin_svn.php
rem ����ץ������ $t_comment = mb_convert_encoding($t_comment, "utf-8","GB2312"); 
rem �޸�λ���� �� foreach ( $t_issues as $t_issue_id ) { ��ǰ��
rem mb_convert_encoding �˺�����Ҫ��չ�ļ�php_mbstring.dll��֧��~~  
rem $t_comment   = iconv("UTF-8","GB2312//TRANSLIT",$t_comment);  ��˵�������ת��Ч�ʸ���

 

SET REPOS=%1
SET REV=%2
SET DETAILS_FILE=D:\svn\no1\log\svnfile_%REV%
SET LOG_FILE=D:\svn\no1\log\svnfile_%REV%_Log
set APR_ICONV_PATH=D:\EasyPHP\Subversion\iconv
set PHPRC=D:\EasyPHP\apache
set path=%path%;D:\EasyPHP\php5;D:\EasyPHP\Subversion\bin;


echo ****** Source code change ******>>%DETAILS_FILE%

echo SVN �޸���: >>%DETAILS_FILE%
svnlook author -r %REV% %REPOS%>>%DETAILS_FILE%

echo SVN �޸�����: >>%DETAILS_FILE%
svnlook date -r %REV% %REPOS%>>%DETAILS_FILE%

echo SVN �汾:  >>%DETAILS_FILE%
echo %REV%>>%DETAILS_FILE%

echo SVN �ύע��: >>%DETAILS_FILE%
svnlook log -r %REV% %REPOS%>>%DETAILS_FILE%

echo SVN �޸���ϸ: >>%DETAILS_FILE%
svnlook diff -r %REV% %REPOS%>>%DETAILS_FILE%


rem php.exe .\checkin_svn.php ��Ϣ�ļ���־�ļ�
php.exe D:\EasyPHP\www\mantis\core\checkin_svn.php <%DETAILS_FILE%>%LOG_FILE%

del %DETAILS_FILE%
del %LOG_FILE%



