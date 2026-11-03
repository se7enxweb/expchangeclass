<?php /* #?ini charset="utf-8"?

[RegionalSettings]
TranslationExtensions[]=expchangeclass

# "Verify it's you" (Exponential 6.0.15 and later, doc/eng-us/guides/verify-its-you.md): changing the class of an
# object, undoing it, discarding a restore point and starting a bulk change ask the signed-in user to confirm who they
# are first, once per [ReauthSettings] Minutes. The group appears on setup/reauth as "changeclass" and can be switched
# off there. Giving or taking the editor policies is in the group of roles and policies.
[ReauthSettings]
Groups[]=changeclass

[ReauthGroup_changeclass]
Enabled=true
Rules[]
Rules[]=changeclass/map_attributes ApplyButton
Rules[]=changeclass/action ModifyObjectContentClassButton
Rules[]=changeclass/result RestoreButton DiscardButton
Rules[]=changeclass/dashboard RestoreButton DiscardButton
Rules[]=changeclass/bulk StartButton

[ReauthGroup_users]
Rules[]=changeclass/dashboard GrantEditorPoliciesButton RevokeEditorPoliciesButton

*/ ?>
