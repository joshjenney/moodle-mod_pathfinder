<?php //<>

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
 * Pathfinder:	pathfinder.php
 *
 * Description:	English strings for module pathfinder.
 *
 * @package    mod_pathfinder
 * @copyright  2014 Josh Jenney | josh@winministries.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

#global
$string['pluginname']			=		'Pathfinder';
$string['pathfinder']			=		'Pathfinder';


# new instance
$string['modulename']			=		'Pathfinder';
$string['modulenameplural']		=		'Pathfinders';
$string['modulename_help']		=		'The pathfinder module enables staff to create and maintain student diploma/degree programs.';


# Navigation - Administration
$string['pluginadministration']	=		'Pathfinder Settings';


# mod_form.php
$string['pathfinder_name']		=		'Pathfinder instance name';
$string['pathfinder_name_help']	=		'This instance of Pathfinder can be assigned a unique name.';

$string['pathfinder_setting1']	=		'Setting 1';
$string['pathfinder_field1']	=		'Place your first fields here!';

$string['pathfinder_fieldset1']	=		'Fieldset One';
$string['pathfinder_setting2']	=		'Setting 2';
$string['pathfinder_field2']	=		'Place your second fields here!';


# view.php
$string['pathfinder_valid_capability']		=	"Success! You have permission to view this page.";
$string['pathfinder_invalid_capability']	=	"Sorry, you don't have permission to view this page.";
$string['pathfinder_missing_email']			=	'The email address you provided is not valid.';

$string['pathfinder_select_userid']			=	'Profile';
$string['pathfinder_view_overview']			=	'Details';

$string['pathfinder_form_label_user']		=	'User:';
$string['pathfinder_form_label_program']	=	'Program:';
$string['pathfinder_semester']				=	'Semester:';
$string['pathfinder_year']					=	'Year:';
$string['pathfinder_student']				=	'Student:';
$string['pathfinder_duplicate']				=	'Duplicate found:';
$string['pathfinder_duplicate_found']		=	'Yes';
$string['pathfinder_duplicate_notfound']	=	'No';
$string['pathfinder_semester_credits']		=	'Semester Credits:';
$string['pathfinder_year_credits']			=	'Current Year Credits:';
$string['pathfinder_total_credits']			=	'Total Credits:';
$string['pathfinder_courses_bible']			=	'Bible Credits:';
$string['pathfinder_courses_ministry']		=	'Ministry Credits:';
$string['pathfinder_courses_leadership']	=	'Leadership Credits:';
$string['pathway_semester_previous']		=	'Previous Semester';
$string['pathway_semester_next']			=	'Next Semester';
$string['pathfinder_view_savechanges']		=	'Save changes';
$string['pathfinder_view_revert']			=	'Revert';
$string['pathfinder_view_cancel']			=	'Cancel';

$string['pathway_semester_loaduser']		=	"Load User's Program";
$string['pathway_semester_saveuser']		=	"Change User's Program";
$string['pathway_semester_newUser']         =   'Create New Program';
$string['pathway_semester_newUser_label']   =   'New Program Name:';

$string['pathfinder_requirePaymentLabel']	=	'Payment Required for semester enrollment:';
$string['pathfinder_requirePayment']		=	'';

$string['pathfinder_cancelled']				=	'Your changes have been reverted back to their orignal values.';

# index.php
$string['pathfinder_found_instances']		=	'Pathfinder Instances';
$string['pathfinder_notfound_instances']	=	'There are no instances found in the database associated with Pathfinder.';
