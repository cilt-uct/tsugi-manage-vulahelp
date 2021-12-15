<?php
require_once('../config.php');
include 'tool-config-dist.php';
include 'src/Template.php';

use \Tsugi\Util\U;
use \Tsugi\Core\Cache;
use \Tsugi\Core\Settings;
use \Tsugi\Core\LTIX;
use \Tsugi\Util\LTI;
use \Tsugi\UI\SettingsForm;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$linkId = $LINK->id;
$p = $CFG->dbprefix;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$menu = false;

$config = [
    'instructor' => $USER->instructor, 
    'styles'     => [ addSession('static/user.css') ],
    'scripts'    => [],
    'updateURL'  => addSession('actions/UpdateList.php')
];

// Start of the output
$OUTPUT->header();

Template::view('templates/header.html', $config);

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

if ($tool['debug']) {
    echo '<pre>'; print_r($config); echo '</pre>';
}

    $ch = curl_init();
    $headers  = [
                'x-api-key: '. $tool['api']['key'],
                'Content-Type: text/plain'
            ];
    $postData = [
        'get' => 'true',
        'username' => $tool['api']['username'],
        'password' => $tool['api']['password']
    ];

    curl_setopt($ch, CURLOPT_URL, $tool['api']['url']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));           
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $resp     = curl_exec ($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// if ($statusCode == 200) {
//     echo "received (". $statusCode ."): <pre>";
//     print_r ($result);
//     echo "</pre>";
// }
// exit();

$resulting_json = json_decode($resp, true);
$result = [];

if ($tool['debug']) {
    echo '<pre>'; 
    echo $resp;
    echo '</pre>';
}

if (isset($resulting_json['success'])) {
    // echo '<p>'. $resulting_json['success'] .'</p>'; 
    if ($resulting_json['success'] == 0) {
        echo "<p>Authentication credentials were not provided.</p>";
    } else {

        $jira_list = array_map(function($a) {
            return array( 'name' => $a->displayname, 'email' => $a->email, 'selected' => 0 );
        }, json_decode($resp)->result);

        // echo '<pre>'; 
        // echo var_dump($jira_list);
        // echo '</pre></hr>';

        $query = "SELECT * FROM {$p}vulahelp_users WHERE link_id = :linkId and active = 1;";
        $arr = array(':linkId' => $linkId);
        $row = $PDOX->rowDie($query, $arr);

        if (!$row) {
            $selected_list = [];
        } else {

            // echo '<pre>'; 
            // echo var_dump($row);
            // echo '</pre>';

            $selected_list = array_map(function($a) {
                return array( 'name' => $a->name, 'email' => $a->email, 'selected' => 1 );
            }, json_decode($row['list']));
        }

        // echo '<pre>'; 
        // echo var_dump($selected_list);
        // echo '</pre>';

        // Filter out selected items from jira_list;
        foreach ($selected_list as $o) {
            $not = $o['email'];
            $jira_list = array_filter($jira_list, function($x) use ($not) { return $x['email'] != $not; });
        }

        // both arrays will be merged including duplicates
        $result = array_merge($selected_list, $jira_list );
        // duplicate objects will be removed
        $result = array_map("unserialize", array_unique(array_map("serialize", $result)));
        //array is sorted on the bases of id
        sort( $result );

        // echo '<pre>'; 
        // echo var_dump($result);
        // echo '</pre>';
    }

    $config['list'] = $result;
    Template::view('templates/body.html', $config);
}

$OUTPUT->footerStart();

Template::view('templates/footer.html', $config);
// include('templates/tmpl.html');

$OUTPUT->footerEnd();
