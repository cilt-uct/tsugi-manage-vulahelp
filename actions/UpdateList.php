<?php
require_once("../../config.php");

include '../tool-config.php';

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$linkId = $LINK->id;
$userid = $USER->id;
$p = $CFG->dbprefix;

header('Content-Type: application/json');
ini_set('max_execution_time', '3600');

$list = $_POST['list'];

$result = $PDOX->queryDie("update {$p}vulahelp_users set active=0 where active=1;");
if ($result->errorCode() === '00000') {

    $query = "INSERT INTO {$p}vulahelp_users (`link_id`,`list`,`created_at`,`created_by`,`active`) VALUES (:linkId, :list, NOW(), :userid, 1);";
    $arr = array(':linkId' => $linkId,
        ':list' => json_encode($_POST['list']),
        ':userid' => $userid
    );
    $result = $PDOX->queryDie($query, $arr);
}

if ($result->errorCode() != '00000') {
    echo '{ "err": 1, "msg": '. $result-> errorInfo() .'}';
} else {
    echo '{ "err": 0 }';
}
exit;