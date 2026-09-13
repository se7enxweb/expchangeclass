<?php
//
// expchangeclass - change an existing content object's content class, mapping each
// attribute of the old class onto one of the new. A hardened fork of eZChangeclass
// by Bartek Modzelewski, for Exponential Legacy / Exponential 6 on PHP 8.
// Copyright (C) 1998 - 2026 7x. All rights reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//

class expchangeclassInfo
{
    public static function info()
    {
        return array( 'Name' => "expchangeclass",
                      'Version' => "1.0.0",
                      'Copyright' => "Copyright (C) 1998 - 2026 7x. All rights reserved.",
                      'License' => "GNU General Public License v2.0 (or any later version)",
                      'info_url' => "https://github.com/se7enxweb/expchangeclass",
                      'Includes' => array(
                          array( 'Name' => "eZChangeclass",
                                 'Copyright' => "Copyright (C) 2007 Bartek Modzelewski",
                                 'License' => "GNU General Public License v2.0" ) ) );
    }
}

?>
