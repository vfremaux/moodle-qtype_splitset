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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package     qtype_splitset
 * @category    qtype
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   (C) 2006 onwards Valery Fremaux
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2014121401;
$plugin->requires  = 2015111000;
$plugin->component = 'qtype_splitset';
$plugin->maturity  = MATURITY_RC;
$plugin->release  = "2.9.0 (Build 2012112300)";

// Non moodle attributes.
$plugin->codeincrement = '3.0.0000';