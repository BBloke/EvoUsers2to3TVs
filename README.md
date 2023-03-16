# EvoUsers2to3TVs
Migrate users with extended attributes to User TVs

This Snippet migrates users extended attributes (created from WebLoginPE) from v2.0.4 to v3 user TV roles.

** Please ensure you backup your database before continuing.  This will help with any issues **

This can now be installed via the Extras Module.

Example: [[userUpdate? &adminid=`600` &roleid=`2`]]

adminid is the id of the admin account
roleid is the user role id // PowerUser = 2

Update: 
You can omit the parameter adminid and the program will search the users for the 'admin' user and select it's id automatically.

## Getting the new TV data to a form with FormLister

I'm not sure if this is the correct way to do things but it gets the job done.  I do not know whether it works when it comes to saving any changes.

1. add **&prepare=`fmUserTVValues`** into your FormLister call.
2. Create a new Snippet called **fmUserTVValues**
3. Paste the following into your snippet

```php
<?php
$data = \UserManager::getValues(['id' => $modx->getLoginUserID()]);

foreach ( $data as $detail => $value ) {
	$FormLister->setField($detail, $value);
}
```
4. Normal template variables will be populated in the same way as document.

### Example:

A user template variable company can be populate using [+company.value+]
