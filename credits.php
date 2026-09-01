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
  global $CFG;

  // N2NCU: $CFG has to be imported here. Without it, the previous
  // isset($CFG->logroot) test was reading an undefined variable and was always
  // false, so every exception took the fallback - which wrote error.log into
  // this directory, i.e. inside the web root, where the server can serve it.
  //
  // $CFG->logroot is also not a Moodle setting: core does not set it and it is
  // not in the N2NCU config.php, so the intended branch could never have been
  // reached even with $CFG in scope. $CFG->dataroot is the supported location
  // for runtime files and sits outside the docroot by design.
  //
  // Deliberately not using make_writable_directory(): it throws on failure, and
  // throwing from inside an exception handler discards the original exception.
  $logpath = null;
  if (!empty($CFG->dataroot)) {
    $logdir = $CFG->dataroot . '/mod_pathfinder';
    if (!is_dir($logdir)) {
      @mkdir($logdir, 0777, true);
    }
    if (is_dir($logdir) && is_writable($logdir)) {
      $logpath = $logdir . '/error.log';
    }
  }

  $message = date('[Y-m-d H:i e] ') . 'Uncaught exception: ' . $exception->getMessage() . PHP_EOL;

  if ($logpath !== null) {
    // A log that cannot be written must not break the thing it is logging.
    @error_log($message, 3, $logpath);
  } else {
    // Last resort: the server's own error log, never the web root.
    error_log($message);
  }
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
