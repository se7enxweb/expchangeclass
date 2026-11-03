<?php
/**
 * File containing the expChangeClassAuditBranch class.
 *
 * @copyright Copyright (C) 1998 - 2026 Exponential Foundation and 7x. All rights reserved.
 * @license GNU General Public License v2.0 (or any later version)
 * @package expchangeclass
 */

/**
 * The audit names of expchangeclass (Exponential 6.0.15 and later, settings/audit.ini.append.php
 * [AuditEventSettings] Branches[expchangeclass]). Each records the object, the class before and after, and under
 * x.expchangeclass the mapping and the restore point; never attribute values.
 */
class expChangeClassAuditBranch implements expAuditTaxonomyBranch
{
    public function events()
    {
        return array(
            'content.expchangeclass_object.convert' => array( 'label' => 'Class of an object changed', 'severity' => 'notice', 'default' => 'on', 'channel' => 'content' ),
            'content.expchangeclass_object.restore' => array( 'label' => 'Class change undone from its restore point', 'severity' => 'notice', 'default' => 'on', 'channel' => 'content' ),
            'content.expchangeclass_restorepoint.discard' => array( 'label' => 'Restore point of a class change discarded', 'severity' => 'notice', 'default' => 'on', 'channel' => 'content' ),
            'content.expchangeclass_bulk.start' => array( 'label' => 'Class change of many objects started as a background job', 'severity' => 'notice', 'default' => 'on', 'channel' => 'content' ),
            'access.expchangeclass_policies.grant' => array( 'label' => 'Class change policies given to a role', 'severity' => 'notice', 'default' => 'on', 'channel' => 'access' ),
            'access.expchangeclass_policies.revoke' => array( 'label' => 'Class change policies taken from a role', 'severity' => 'notice', 'default' => 'on', 'channel' => 'access' ),
        );
    }
}
