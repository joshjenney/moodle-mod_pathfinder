<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

set_exception_handler('exception_handler');

// Report all PHP errors (see changelog)
error_reporting(-1);

global $CFG, $DB, $USER;

// N2NCU 2026-07-29: was pinned to the production database by name, so this
// failed on every clone. Uses {table} placeholders now; aliases unchanged.
$sql = "SELECT mdl_course.id, mdl_course.shortname, mdl_course.fullname, COUNT( mdl_lesson.name ) as sessions
          FROM {course} AS mdl_course, {lesson} AS mdl_lesson
         WHERE mdl_course.id = mdl_lesson.course
      GROUP by mdl_course.fullname
      ORDER BY mdl_course.fullname";

$result = $DB->get_records_sql($sql);

foreach( $result as $key => $value)
  $result[$key]->credits = floorToFraction($value->sessions * 0.375, 2);

echo '<pre>'; print_r($result);

exit;

////////////////// HELPER FUNCTIONS //////////////////

function exception_handler($exception) {
  error_log("Uncaught exception: " .$exception->getMessage() .PHP_EOL, 3, '/opt/lampp/logs/php_error_log');
}

function floorToFraction($number, $denominator = 1)
{
    $x = $number * $denominator;
    $x = round($x);
    $x = $x / $denominator;
    return $x;
}


// debug print
# $pretty = fu<a href="file:///"></a>nction($v='',$c="&nbsp;&nbsp;&nbsp;&nbsp;",$in=-1,$k=null)use(&$pretty){$r='';if(in_array(gettype($v),array('object','array'))){$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").'<br>';foreach($v as $sk=>$vl){$r.=$pretty($vl,$c,$in+1,$sk).'<br>';}}else{$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").(is_null($v)?'&lt;NULL&gt;':"<strong>$v</strong>");}return$r;};
#echo '<pre>';echo $pretty( $result );echo '</pre>'; 

?>