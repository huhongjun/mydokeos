<?php
$cidreset=true;
include_once('../../main/inc/global.inc.php');
header('location:'.api_get_path(WEB_COURSE_PATH).$_POST['cs_pay'].'/index.php?cs_pay=true');
exit;
?>
