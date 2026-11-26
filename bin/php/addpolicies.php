#!/usr/bin/env php
<?php
/**
 * Give the Editor role the policies to change classes: changeclass/convert and changeclass/restore, no duplicates (dry run unless --apply)
 *
 * Usage:
 *   ./console exp:addchangeclasspolicies [options]   (or ext:expchangeclass:addpolicies)
 *   --help shows the options.
 *
 * @alias exp:addchangeclasspolicies
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x; All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

require_once dirname( __FILE__ ) . '/../../../../autoload.php';

// The code is in extension/expchangeclass/classes/runnable/commands/php_addpolicies.php; this file is the entry point.
\Exponential\Command\Extension\Expchangeclass\Addpolicies::main( __FILE__ );
