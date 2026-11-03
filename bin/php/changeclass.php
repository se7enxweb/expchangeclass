#!/usr/bin/env php
<?php
/**
 * Change the class of objects with a preview, a restore point and one transaction per object (dry run unless --apply)
 *
 * Usage:
 *   ./console exp:changeclass [options]   (or ext:expchangeclass:changeclass)
 *   --help shows the options.
 *
 * @alias exp:changeclass
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

require_once dirname( __FILE__ ) . '/../../../../autoload.php';

// The code is in extension/expchangeclass/classes/runnable/commands/php_changeclass.php; this file is the entry point.
\Exponential\Command\Extension\Expchangeclass\Changeclass::main( __FILE__ );
