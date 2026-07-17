<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see http://www.gnu.org/licenses/

/**
 * Pathfinder:	view.php
 *
 * Description: Prints a particular instance of pathfinder
 *
 * @package    mod_pathfinder
 * @copyright  2015 Josh Jenney | josh@winministries.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Last updated on March 10th 2016 for Pathfinder v2.0
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // pathfinder instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('pathfinder', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $pathfinder  = $DB->get_record('pathfinder', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $pathfinder  = $DB->get_record('pathfinder', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $pathfinder->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('pathfinder', $pathfinder->id, $course->id, false, MUST_EXIST);
} else {
	error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// API call is now out of date and cannot be used
// add_to_log($course->id, 'pathfinder', 'view', "view.php?id={$cm->id}", $pathfinder->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/pathfinder/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($pathfinder->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('pathfinder-'.$somevar);

// Output starts here
echo $OUTPUT->header();

if ($pathfinder->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('pathfinder', $pathfinder, $cm->id), 'generalbox mod_introbox', 'pathfinderintro');
}

// for reference
#function has_capability($capability, context $context, $user = null, $doanything = true)
#function require_capability($capability, context $context, $userid = null, $doanything = true, $errormessage = 'nopermissions', $stringfile = '')

$context = context_module::instance($cm->id);
if( !has_capability('mod/folder:managefiles', $context )) {
	echo $OUTPUT->heading(get_string('pathfinder_invalid_capability', 'pathfinder'));
} else {
	// custom header on success loading
	# echo $OUTPUT->heading(get_string('pathfinder_valid_capability', 'pathfinder'));
	#echo $OUTPUT->box('');
    
	/// extend the moodleform class with our custom simplehtml_form
	require_once("$CFG->libdir/formslib.php"); 
	class form_simplehtml extends moodleform {
		
		//Add elements to form
		public function definition() {

			global $CFG, $DB, $USER, $PAGE;
			$mform = $this->_form; // Don't forget the underscore! 
			
			// create the pathfinder structure
			$pathfinder = new stdClass();
			$pathfinder->creditValue			=	0;
			$pathfinder->userid					= 	1; // 0 is for null, 1 is for default, users > 1 has course modifications
			$pathfinder->currentSemester		= 	1; #1;
			$pathfinder->currentYear			= 	1; #1;
			$pathfinder->duplicateStatus		= 	'n/a'; #get_string('pathfinder_duplicate_notfound', 'pathfinder');
			$pathfinder->semesterCredits		= 	0; #32;
			$pathfinder->yearCredits			= 	0; #96;
			$pathfinder->totalCredits			=	0; #192;
			$pathfinder->coursesBible			=	0; #17;
			$pathfinder->coursesMinistry		=	0; #14;
			$pathfinder->coursesLeadership		=	0; #11;
			#$pathfinder->requirePayment			=	0;
            $pathfinder->program                =   100;
			
			
			// set default courses per user
			$pathfinder->course[0]	=	0;
			$pathfinder->course[1]	=	0;
			$pathfinder->course[2]	=	0;
			$pathfinder->course[3]	=	0;
			$pathfinder->course[4]	=	0;
			$pathfinder->course[5]	=	0;
			
			
			// if available, read redirected args from URL and filter for use
			$output = (string) http_build_query( $_GET );
			$output = str_replace( "amp;", "", $output );
			$output = explode( '&', $output );
			# echo '<pre>'; print_r( $output ); echo '</pre>';
			# userid
			
            // ======
            $tmp = array();

            # userid
            if (isset($output[1]))
            {
                $tmp = explode( '=', $output[1] );
                if( $tmp[1] != "") {
                    $pathfinder->userid = $tmp[1]; }	
            } else {
                $pathfinder->userid = 1;
            }
            
            // return the program ID for $USER->id in table "mdl_pathfinder_directory"
            $result = $DB->get_record( 'pathfinder_users', array("id"=>$pathfinder->userid), $fields='id,program');

            // use provided values input, otherwise if 0, {}, '' or NULL ---> default to one (1).
            if (isset($result->id)) { $userid = $result->id; } else $userid = 1;
            if (isset($result->program)) { $program = $result->program; } else $program = 100;
            
            # program
            if (isset($output[2]))
            {
                $tmp = explode( '=', $output[2] );
                if( $tmp[1] != "") {
                    $pathfinder->program = $tmp[1]; }
            } else {
                $pathfinder->program = $program;
            }
            

            # semester
            if (isset($output[3]))
            {
                $tmp = explode( '=', $output[3] );
                if( $tmp[1] != "") {
                    $pathfinder->currentSemester = $tmp[1]; }
            } else {
                $pathfinder->currentSemester = 1;
            }

            # year
            $pathfinder->currentYear = floor( ($pathfinder->currentSemester / 4) + 1 );
			
			
			// load list of users into userArray
            // SELECT `mdl_user`.`id`, `mdl_user`.`firstname`, `mdl_user`.`lastname`, IFNULL(`mdl_pathfinder_directory`.`program`,1) FROM { oj `n2ncu_online`.`mdl_user` AS `mdl_user` LEFT OUTER JOIN `n2ncu_online`.`mdl_pathfinder_directory` AS `mdl_pathfinder_directory` ON `mdl_user`.`id` = `mdl_pathfinder_directory`.`id` }
			// $sql = 'SELECT id, firstname, lastname FROM {user}';
            $sql = 'SELECT u.id, u.firstname, u.lastname, pu.program
                    FROM {user} u
                    LEFT OUTER JOIN {pathfinder_users} pu
                    ON u.id = pu.id';
			$result = $DB->get_records_sql($sql);
			$userArray = array();
			$userArray['0'] = '--- Choose a profile ---';
			foreach( $result as $key => $value ){
				$userArray[$value->id] = $value->id .' - ' .$value->firstname .' ' .$value->lastname;
                
                if(!empty($value->program))
                {
                    $userArray[$value->id] .= ' (' .$value->program .')';
                    
                }
                               
				if( $value->id == 1)
					$userArray[$value->id] = '1 - **Default/Guest User**';
			}
            
            
            // load list of programs into programArray
			$sql = 'SELECT id, description FROM {pathfinder_program}';
			$result = $DB->get_records_sql($sql);
            #echo '<pre>'; print_r($result); echo '</pre>';
            $programArray = array();
			$programArray['0'] = '--- Choose a profile ---';
            foreach( $result as $key => $value )
				$programArray[$value->id] = $value->id .' - ' .$value->description;
			
			
			// fetch customized course list based on saved user preferences
			$sql = 'SELECT * FROM {pathfinder_data} WHERE program = ' .$pathfinder->program .' AND sortorder >= ' .( $pathfinder->currentSemester * 10000 + 10000 ) .' AND sortorder < ' .( $pathfinder->currentSemester * 10000 + 20000 ) .' ORDER BY sortorder ASC';
				$result = $DB->get_records_sql($sql);
			
			// load results into course list
			$tmp = 0;
			foreach( $result as $key => $value )
			{
				#if( bcdiv_cust( $value->sortorder, 10000, 4) == (int) ($value->sortorder / 10000) )
				#{
					#$pathfinder->requirePayment = 1;
					# print_r(bcdiv_cust( $value->sortorder, 10000, 4));
					# print_r((int) ($value->sortorder / 10000));
				#}else{
					$pathfinder->course[ $tmp ] = $value->course;
					$tmp++;
				#}

			}
			
			
			// are there duplicated courses? find out and report to dashboard
			#$sql = 'SELECT * FROM {pathfinder_data} WHERE program = ' .$pathfinder->program .' ORDER BY sortorder ASC';
      $sql = 'SELECT `mdl_pathfinder_data`.*, `mdl_pathfinder_data`.`sortorder`, COUNT( `mdl_lesson`.`id` ) AS `sessions` FROM `n2ncu_online`.`mdl_pathfinder_data` AS `mdl_pathfinder_data`, `n2ncu_online`.`mdl_lesson` AS `mdl_lesson` WHERE `mdl_pathfinder_data`.`course` = `mdl_lesson`.`course` AND `mdl_pathfinder_data`.`program` = ' .$pathfinder->program .' GROUP BY `mdl_pathfinder_data`.`course` ORDER BY `mdl_pathfinder_data`.`sortorder` ASC';
			$result = $DB->get_records_sql($sql);
      
      foreach( $result as $key => $value)
        $result[$key]->credits = floorToFraction($value->sessions * 0.375, 2);
      
      
      
      
      // load list of courses into coursePathwayArray
			$sql = 'SELECT id, fullname FROM {course} ORDER BY fullname ASC';
				$tmp_result = $DB->get_records_sql($sql);
			$courseListArray = array();
			$courseListArray['0'] = '--- Choose a course ---';
			
			foreach( $tmp_result as $key => $value ){
        
        $sessions = 'n/a'; $credits = 'n/a';
        
        foreach ($result as $key2 => $value2){
          
          if( $value2->course == $value->id )
          {
              $sessions = $value2->sessions;
              $credits = $value2->credits;
          }
        }
        
        $courseListArray[$value->id] = $value->fullname .' [' .$sessions .' sessions, ' .$credits .' credit hours, moodle id ' .$value->id .']' ;
        
          
        
      }
      
      #print_r( $result );
      
// override 1/8/2021
function bcdiv_cust( $first, $second, $scale = 0 )
{
    $res = $first / $second;
    return round( $res, $scale );
}
      
			// build the data for pathfinder's dashboard
			# search for it!
			foreach( $result as $key => $value ){
				
				# don't include any flags [payment]
				if( bcdiv_cust( $value->sortorder, 10000, 4) != (int) ($value->sortorder / 10000) ) {
          
          # add to total credits
					$pathfinder->totalCredits = $pathfinder->totalCredits + $result[$key]->credits;
					
					# add to [catagory x3] credits
					$pieces = explode("-", $courseListArray[$value->course]);
					
					if( strtoupper( substr( trim($pieces[1]), 0, 1)) == "B")
						$pathfinder->coursesBible = $pathfinder->coursesBible + $result[$key]->credits;
						
					if( strtoupper( substr( trim($pieces[1]), 0, 1)) == "M")
						$pathfinder->coursesMinistry = $pathfinder->coursesMinistry + $result[$key]->credits;
						
					if( strtoupper( substr( trim($pieces[1]), 0, 1)) == "L")
						$pathfinder->coursesLeadership = $pathfinder->coursesLeadership + $result[$key]->credits;
          
          #if( strtoupper( substr( trim($pieces[1]), 0, 1)) == "C")
					#	$pathfinder->coursesLeadership = $pathfinder->coursesCommunityDev + $result[$key]->credits;
          
          #if( strtoupper( substr( trim($pieces[1]), 0, 1)) == "T")
					#	$pathfinder->coursesLeadership = $pathfinder->coursesTheology + $result[$key]->credits;
					
					# add to [current] year credits
					if( floor( ($value->category / 4) + 1 ) == $pathfinder->currentYear )
						$pathfinder->yearCredits = $pathfinder->yearCredits + $result[$key]->credits;

					# add to [current] semester credits
					if( $value->category == $pathfinder->currentSemester )
						$pathfinder->semesterCredits = $pathfinder->semesterCredits + $result[$key]->credits;
					
				}	
			}
			
			# print-friendly n/a
			if( $pathfinder->totalCredits == 0 )
				$pathfinder->totalCredits = 'n/a';
				
			if( $pathfinder->semesterCredits == 0 )
				$pathfinder->semesterCredits = 'n/a';
				
			if( $pathfinder->yearCredits == 0 )
				$pathfinder->yearCredits = 'n/a';
				
			if( $pathfinder->coursesBible == 0 )
				$pathfinder->coursesBible = 'n/a';
				
			if( $pathfinder->coursesMinistry == 0 )
				$pathfinder->coursesMinistry = 'n/a';
				
			if( $pathfinder->coursesLeadership == 0 )
				$pathfinder->coursesLeadership = 'n/a';
			
				
			// debug print
			#$pretty = fu<a href="file:///"></a>nction($v='',$c="&nbsp;&nbsp;&nbsp;&nbsp;",$in=-1,$k=null)use(&$pretty){$r='';if(in_array(gettype($v),array('object','array'))){$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").'<br>';foreach($v as $sk=>$vl){$r.=$pretty($vl,$c,$in+1,$sk).'<br>';}}else{$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").(is_null($v)?'&lt;NULL&gt;':"<strong>$v</strong>");}return$r;};
			#echo '<pre>';echo $pretty( $result );echo '</pre>'; 
			
			
			
			
			
			
			
			
			
			
			
			
			// search for and display duplicates	
			$compareStack = array();
			foreach( $result as $key => $value )
			{
				
				// if currently inspected key has an existing duplicate
				if( in_array( $value->course, $compareStack ))
				{
	
					if( $pathfinder->duplicateStatus == 'n/a' )
					{
						$pathfinder->duplicateStatus = '';
						$mform->addElement('html', '
							<style>
								
								#right #cell .fitem:first-child .fitemtitle .fstaticlabel label {
									color: red;
								}
								
							</style>');
					}

						$pathfinder->duplicateStatus .= "#" .$value->courseid ."\n";
											
				} else {
					
					# filter out any requiredPayment markers
					if( bcdiv_cust( $value->sortorder, 10000, 4) == (int) ($value->sortorder / 10000) )
					{
						
						unset( $compareStack[$key] );
						
					} else {
						
						array_push( $compareStack, $value->course );
						
					}	
				}
			}
			
			
			/*
             *
             * Profile Form Header (select user & program)
             *
             */
			$mform->addElement('header', 'select_userid', get_string('pathfinder_select_userid', 'pathfinder'));
			
            // add comboboxes
            $mform->addElement('select', 'userid', get_string('pathfinder_form_label_user', 'pathfinder'), $userArray );
			$mform->addElement('select', 'program', get_string('pathfinder_form_label_program', 'pathfinder'), $programArray );
            
			#echo '<pre>'; print_r($programArray); echo '</pre>';
            
            // assign defaults
            $mform->setDefault('userid', $pathfinder->userid );
			$mform->setDefault('program', $pathfinder->program );

            	
			// add submit buttons
			$buttonarray2=array();
            $buttonarray2[0] = &$mform->createElement('submit', 'loadUser', get_string('pathway_semester_loaduser','pathfinder'));
            $buttonarray2[1] = &$mform->createElement('submit', 'saveUser', get_string('pathway_semester_saveuser','pathfinder'));
            $mform->addElement('text', 'newUser', get_string('pathway_semester_newUser_label','pathfinder'));
            $mform->setType('newUser', PARAM_TEXT);
            $buttonarray2[2] = &$mform->createElement('submit', 'saveUser', get_string('pathway_semester_newUser','pathfinder'));
			$mform->addGroup($buttonarray2, 'buttonar', '', array(' '), false);
			$mform->closeHeaderBefore('buttonar');

            
            /*
             *
             * Details Form Header (modify course pathway)
             *
			 */ 
			$mform->addElement('header', 'view_overview', get_string('pathfinder_view_overview', 'pathfinder'));
			$mform->addElement('html', '
				<style>
					
					#container {
						display: table;
					}
					#row {
						display: table-row;
					}
					#left, #middle, #right {
						display: table-cell;
					}
					#cell .fitem .fitemtitle {
						width: 135px;
					}
					#cell .fitem select{
						width: 250px;
					}
					#cell .fitem .felement {
						margin-left: 140px;
					}
					
				</style>');
				
			$mform->addElement('html', '<div id="container">');
				$mform->addElement('html', '<div id="row">');
					
					$mform->addElement('html', '<div id="left">');
						$mform->addElement('html', '<div id="cell">');	
						
							// semester, year	
							$mform->addElement( 'static', null, get_string('pathfinder_semester', 'pathfinder'), $pathfinder->currentSemester );
							$mform->addElement( 'hidden', 'semester', $pathfinder->currentSemester );
							$mform->setType('semester', PARAM_INT);
							$mform->addElement( 'static', null, get_string('pathfinder_year', 'pathfinder'), $pathfinder->currentYear );
							#$mform->addElement('html', '<td>Object 1</td><td>Object 1</td><td>Object 2</td><td>Object 3</td>');
						
						$mform->addElement('html', '</div>');
						$mform->addElement('html', '<div id="cell">&nbsp;</div>');
						$mform->addElement('html', '<div id="cell">');
						
							// student	
							$mform->addElement( 'static', null, get_string('pathfinder_student', 'pathfinder'), $userArray[ $pathfinder->userid ] );	
							
							// require payment
							#$mform->addElement('advcheckbox', 'requirePayment', get_string('pathfinder_requirePaymentLabel', 'pathfinder'), get_string('pathfinder_requirePayment', 'pathfinder'), array('group' => 1), array(0, 1));
							#$mform->setDefault('requirePayment', $pathfinder->requirePayment);
							
						$mform->addElement('html', '</div>');	
					$mform->addElement('html', '</div>');
					
					$mform->addElement('html', '<div id="center">');
						$mform->addElement('html', '<div id"cell">&nbsp;</div>');
						$mform->addElement('html', '<div id="cell">');
						
							// Semester(s)
							$mform->addElement('select', 'course1', 'Course 1:', $courseListArray );

								$mform->setDefault('course1', 0 );
								if( $output != 0 )
									$mform->setDefault('course1', $pathfinder->course[0] );
							
							$mform->addElement('select', 'course2', 'Course 2:', $courseListArray );

								$mform->setDefault('course2', 0 );
								if( $output != 0 )
									$mform->setDefault('course2', $pathfinder->course[1] );
							
							$mform->addElement('select', 'course3', 'Course 3:', $courseListArray );

								$mform->setDefault('course3', 0 );
								if( $output != 0 )
									$mform->setDefault('course3', $pathfinder->course[2] );
							
							$mform->addElement('select', 'course4', 'Course 4:', $courseListArray );

								$mform->setDefault('course4', 0 );
								if( $output != 0 )
									$mform->setDefault('course4', $pathfinder->course[3] );
							
							$mform->addElement('select', 'course5', 'Course 5:', $courseListArray );

								$mform->setDefault('course5', 0 );
								if( $output != 0 )
									$mform->setDefault('course5', $pathfinder->course[4] );
							
							$mform->addElement('select', 'course6', 'Course 6:', $courseListArray );

								$mform->setDefault('course6', 0 );
								if( $output != 0 )
									$mform->setDefault('course6', $pathfinder->course[5] );					
						
						$mform->addElement('html', '</div>');
					$mform->addElement('html', '</div>');
					
					$mform->addElement('html', '<div id="right">');
						$mform->addElement('html', '<div id="cell">');
			
							// semester credits
							$mform->addElement( 'static', null, get_string('pathfinder_semester_credits', 'pathfinder'), $pathfinder->semesterCredits );
							
							// Year Credits
							$mform->addElement( 'static', null, get_string('pathfinder_year_credits', 'pathfinder'), $pathfinder->yearCredits );
							
							// Total Credits
							$mform->addElement( 'static', null, get_string('pathfinder_total_credits', 'pathfinder'), $pathfinder->totalCredits );
							
							// Bible Courses
							$mform->addElement( 'static', null, get_string('pathfinder_courses_bible', 'pathfinder'), $pathfinder->coursesBible );
						
							// Ministry Courses
							$mform->addElement( 'static', null, get_string('pathfinder_courses_ministry', 'pathfinder'), $pathfinder->coursesMinistry );
						
							// Community Development Courses
							$mform->addElement( 'static', null, get_string('pathfinder_courses_leadership', 'pathfinder'), $pathfinder->coursesLeadership );
							
							// duplicates found
							$mform->addElement( 'static', null, get_string('pathfinder_duplicate', 'pathfinder'), $pathfinder->duplicateStatus );
							
						$mform->addElement('html', '</div>');
					$mform->addElement('html', '</div>');
					
				$mform->addElement('html', '</div>');				
			$mform->addElement('html', '</div>');
			
			/*		
			// email
			$mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"' ); // Add elements to your form
			$mform->setType('email', PARAM_NOTAGS);                   //Set type of element
			$mform->addRule('email', get_string('pathfinder_missing_email','pathfinder'), 'required', null, 'server');
			#$mform->setDefault('email', 'Please enter email');        //Default value
			$mform->setDefault('email', $this->_customdata['email']);
			*/
			
/////////////////////////////////////////

		// pathway buttons
		$buttonarray3=array();
		$buttonarray3[] = &$mform->createElement('submit', 'prevSemester', get_string('pathway_semester_previous','pathfinder'));
		
		// TODO: disable details of current user if user is not selected
		$mform->disabledIf( 'previousbutton', $pathfinder->currentSemester, 'eq', 1 );
		
		$buttonarray3[] = &$mform->createElement('submit', 'nextSemester', get_string('pathway_semester_next','pathfinder'));
		$mform->addGroup($buttonarray3, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
		
			
		// normally you use add_action_buttons instead of this code
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'save', get_string('pathfinder_view_savechanges','pathfinder'));
		$buttonarray[] = &$mform->createElement('reset', 'revert', get_string('pathfinder_view_revert','pathfinder'));
		$buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('pathfinder_view_cancel','pathfinder'));
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
			
		}
		
		//Custom validation should be added here
		function validation($data, $files){ return array(); }
	}
		
	//include simplehtml_form.php
	# require_once('PATH_TO/simplehtml_form.php');
	 
	//Instantiate simplehtml_form 
	$mform = new form_simplehtml( null, array('id'=>$id) );
	 
	// Form processing and displaying is done here
	# do stuff here
	
	if ($mform->is_cancelled()) {
	
		// Handle form cancel operation, if cancel button is present on form
		echo $OUTPUT->heading(get_string('pathfinder_cancelled', 'pathfinder'));
		
	} else if ($fromform = $mform->get_data()) {
	  
	  
	  	// In this case you process validated data. $mform->get_data() returns data posted in form.
	  	# print_r($mform->get_data());
		$pretty = function($v='',$c="&nbsp;&nbsp;&nbsp;&nbsp;",$in=-1,$k=null)use(&$pretty){$r='';if(in_array(gettype($v),array('object','array'))){$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").'<br>';foreach($v as $sk=>$vl){$r.=$pretty($vl,$c,$in+1,$sk).'<br>';}}else{$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").(is_null($v)?'&lt;NULL&gt;':"<strong>$v</strong>");}return$r;};
		# echo '<pre>';echo $pretty( $mform->get_data() );echo '</pre>';
		
		
		// store incoming data into structure
		$data = new stdClass();
		foreach( $fromform as $key => $value )
			$data->$key = $value;
		
	
		// process prev/next semester request with limiters
		if( isset( $data->nextSemester )) {
			$data->semester++; }
		if( isset($data->prevSemester) && isset($data->semester)) {
			if( $data->semester > 1)
				$data->semester--; 
		}
		
		
		// process requests for saving data...
		if( isset($data->save) ){
			
			// retired - obtain data to overwrite
			# global $CFG, $DB; # $USER, $PAGE;
			# $sql = 'SELECT * FROM {course_pathfinder} WHERE userid = ' .$data->userid .' AND sortorder > ' .( $data->semester * 10000 + 10000 ) .' AND sortorder < ' .( $data->semester * 10000 + 20000 ) .' ORDER BY sortorder ASC';
			# 	$result = $DB->get_records_sql($sql);
			# echo '<pre>';echo $pretty( $result );echo '</pre>';

			// delete old data
			$table = 'pathfinder_data';
			$select = 'program = ? AND sortorder >= ? AND sortorder < ?';
			$param = array(
				$data->program,
				$data->semester * 10000 + 10000,
				$data->semester * 10000 + 20000,
			);
			# $DB->set_debug(true);
			$result = $DB->delete_records_select( $table, $select, $param);
			#echo '<pre>';echo $pretty( $result );echo '</pre>';
			# $DB->set_debug(false);
			
			
			// save each record into database
			$table = 'pathfinder_data';
			#  $DB->set_debug(true);
			# $result = $DB->delete_records_select( $table, $select, $param);
			$record = new stdClass();
			$record->program		=	$data->program;
			$record->category	=	$data->semester;
			
			#$record->courseid	=	$data->course1;
			#$record->sortorder	=	$data->semester * 10000 + 10000 + 0;
			#if($data->requirePayment == 1)
				#$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			$record->course	=	$data->course1;
			$record->sortorder	=	$data->semester * 10000 + 10000 + 1;
			if(!$record->course == 0)
				$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			$record->course	=	$data->course2;
			$record->sortorder	=	$data->semester * 10000 + 10000 + 2;
			if(!$record->course == 0)
				$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			$record->course	=	$data->course3;
			$record->sortorder	=	$data->semester * 10000 + 10000 + 3;
			if(!$record->course == 0)
				$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			$record->course	=	$data->course4;
			$record->sortorder	=	$data->semester * 10000 + 10000 + 4;
			if(!$record->course == 0)
				$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			$record->course	=	$data->course5;
			$record->sortorder	=	$data->semester * 10000 + 10000 + 5;
			if(!$record->course == 0)
				$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			$record->course	=	$data->course6;
			$record->sortorder	=	$data->semester * 10000 + 10000 + 6;
			if(!$record->course == 0)
				$result = $DB->insert_record( $table, $record, $returnid=false, $bulk=false );
			
			#echo '<pre>';echo $pretty( $record );echo '</pre>';
			#$DB->set_debug(false);

			
		}
        
        if(isset($data->saveUser))
        {
            // save user data
            # expected format: $data->userid, $data->program, $data->description
            
            #echo '<pre>'; print_r($data); echo '</pre>';
            
            if( $result = $DB->get_records_sql('SELECT * FROM {pathfinder_users}'))
            {
                foreach ($result as $key => $value)
                {
                    if ($value->id == $data->userid)
                    {
                        // update directory entry for selected user
                        # $update->id          =  $data->userid;
                        # $update->program     =  $data->program;
                        # $update->description =  $value->description;
                        $result = $DB->execute('UPDATE {pathfinder_users} SET program=0' .$data->program .' WHERE id=0' .$data->userid);
                    }
                }
            }                
            
            
            
            // return the program ID for $USER->id in table "mdl_pathfinder_directory"
            # $result = $DB->get_record( 'pathfinder_directory', array("id"=>$pathfinder->userid), $fields='id,program');

            // use provided values input, otherwise if 0, {}, '' or NULL ---> default to one (1).
            # if (isset($result->id)) { $userid = $result->id; } else $userid = 1;
            # if (isset($result->program)) { $program = $result->program; } else $program = 100;
            
        }
		
        // add new program
        if( isset($data->newUser) && !empty($data->newUser))
        {
            $update = new stdClass();
            $update->description    = $data->newUser;
            $DB->insert_record('pathfinder_program', $update);
        }
		
        // return the program ID for $USER->id in table "mdl_pathfinder_directory"
        $result = $DB->get_record( 'pathfinder_users', array("id"=>$data->userid), $fields='id,program');

        // use provided values input, otherwise if 0, {}, '' or NULL ---> default to one (1).
        if (isset($result->program)) { $data->program = $result->program; } else $data->program = 100;
        
		// redirect page with userid
		$courseurl = new moodle_url('/mod/pathfinder/view.php', array(
			'id'		=>	(int) $id,
			'userid'	=>	(int) $data->userid,
            'program'	=>	(int) $data->program,
			'semester'	=>	(int) $data->semester,
		));
	    redirect($courseurl);
		
		
		// let the user decide what to do next
		#$mform->addHelpButton('api_key_field', 'api_key', 'block_extsearch');
		#$mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
		

	} else {
	  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
	  // or on the first display of the form.
	 
	  //Set default data (if any)
	  #$mform->set_data($toform);
	  //displays the form
	  $mform->display();
	}
	
	
	
	
}

// Finish the page
echo $OUTPUT->footer();




////////////////// HELPER FUNCTIONS //////////////////

function floorToFraction($number, $denominator = 1)
{
    $x = $number * $denominator;
    $x = round($x);
    $x = $x / $denominator;
    return $x;
}
