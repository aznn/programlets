<?php
/**
 * Scrap Attendence from Christ University Website
 * Built Version 3.0
 * Author : Aeon Axan (azaan@outlook.com)
 * 2013
 *
 * Error codes:
 *  BAD_CREDENTIALS = 'username' or 'password' not set in the post request
 *  INTERNAL_ERROR_1 = could not login : check internet connection or cookiefile
 *  INTERNAL_ERROR_2 = could not retrive the attendence page (connection timeout in 15 seconds)
 *  INTERNAL_ERROR_3 = could not delete cookie file
 *  INTERNAL_ERROR_4 = RegEx error : Possibly username/password wrong
 */

// check post data
if(isset($_POST['username']) && isset($_POST['password']))
{
    $_USERNAME = $_POST['username'];
    $_PASSWORD = $_POST['password'];
}
//ERROR CONDITION
else if (isset($_GET['username']) && isset($_GET['password']))
{
    $_USERNAME = $_GET['username'];
    $_PASSWORD = $_GET['password'];
    echo "Debug Mode, Use POST instead! </br>";
}
else
{
    echo "BAD_CREDENTIALS";
    exit;
}

//Cookiefile path
$_COOKIE = dirname(__FILE__) . "/cookies.txt";

// login into christuniversity.in
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://115.119.146.175/KnowledgePro/StudentLoginAction.do");
curl_setopt($ch, CURLOPT_USERAGENT,
  "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2b2) Gecko/20091108 Firefox/3.6b2");

// set post fields and timeout settings
curl_setopt($ch, CURLOPT_POST, true);
$data = array('method' => 'studentLoginAction', 'formName' => 'loginform', 'pageType' => '1',
              'userName' => $_USERNAME, 'password' => $_PASSWORD);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// set where to save cookie
curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);

$ret = curl_exec($ch);
curl_close($ch);

//ERROR CONDITIONS
if ($ret === false) {
  echo "INTERNAL_ERROR_1";
  exit;
}

//logged in and cookie in cookiefile.txt

// Try getting attendance page
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, "http://115.119.146.175/KnowledgePro/studentWiseAttendanceSummary.do?method=getIndividualStudentWiseSubjectAndActivityAttendanceSummary");


// set cookies
curl_setopt($ch2, CURLOPT_COOKIEFILE, $_COOKIE);
curl_setopt($ch2, CURLOPT_COOKIEJAR, $_COOKIE);

curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 15);

$ret2 = curl_exec($ch2);
curl_close($ch2);

//ERROR CONDITIONS
if ($ret2 === false) {
  echo "INTERNAL_ERROR_2";
  exit;
}

// delete cookiefile (ERROR CONDITION)
if (!unlink($_COOKIE))
{
    echo "INTERNAL_ERROR_3";
    //exit;
}

/* RegEx to match required data
 * $names matches the course names
 * $numbers gets the data for the courses
 */
if (preg_match_all('@<td width="40%" height="25"\s?>([^<]*)</td>@', $ret2, $names, PREG_PATTERN_ORDER)) {
    preg_match_all('@<td width="20%" height="25" align="center">\s*[&nbsp;]*([^<\s]*)\s*</td>@', $ret2, $numbers);

    $jsonArray = array();
    $subjects = sizeof($names[1]);
    for($i = 0; $i < $subjects; $i++)
    {
        $pos = $i*3;
        $temp = array(  'name' => htmlspecialchars_decode($names[1][$i]),
                        'type' => $numbers[1][$pos],
                        'conducted' => $numbers[1][$pos+1],
                        'present' => $numbers[1][$pos+2],
                      );
        $jsonArray[$i] = $temp;
    }

    echo(json_encode($jsonArray));

}

// regex parse error, server returned a different page.
else
{
  echo "INTERNAL_ERROR_4";
}
?>
