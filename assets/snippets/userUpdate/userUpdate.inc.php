<?php
// This snippet will create new TV's based on web_user_attributes_extended
// It will also populate fields for each user

$fields = array();
// Change this role ID to the one you wish to choose in the Role 
$roleid = $roleid ?? 0;	// PowerUser

// Change to match your Admin ID account.  This prevents the user_attribute for that account from being changed inadvertently.
$adminid = $adminid ?? 0; // Not always ID 1 so please check

// Whether to update existing roles to the above role id?
// default  0 = no
// 1 = yes
$updateExistingRoles = 0;
if ( empty($adminid) || empty($roleid) ) return "No Admin Specified!";

// You may wish to change the attributes_extended table depending on your old system setup.

$tbExtended 		= $modx->db->config['table_prefix'] . "web_user_attributes_extended";

// These table names can be left alone!
$tbUsers 			= $modx->db->config['table_prefix'] . "users";
$tbUserSettings		= $modx->db->config['table_prefix'] . "user_settings";
$tbUserAttributes 	= $modx->db->config['table_prefix'] . "user_attributes";
$tbUserValues		= $modx->db->config['table_prefix'] . "user_values";
$tbUserRoleVar 		= $modx->db->config['table_prefix'] . "user_role_vars";
$tbSiteTVs 			= $modx->db->config['table_prefix'] . "site_tmplvars";

if ( $_GET['update'] == 'view' || $_GET['commit'] == 'yes')
{
	
	echo "Parametes Using:<br />";
	echo "admin ID: ".$adminid."<br />";
	echo "role ID: ".$roleid."<br />";
	echo $tbUsers."<br />";
	echo $tbUserSettings."<br />";
	echo $tbUserAttributes."<br />";
	echo $tbUserValues."<br />";
	echo $tbUserRoleVar."<br />";
	echo $tbSiteTVs."<br />";
	
	echo "<form><input name='commit' value='yes' type='submit' /></form>";

	// Get the extended attribute field names and create TVs
	$sql = "SHOW COLUMNS FROM ". $tbExtended .";";
	$rs = $modx->db->query($sql);

	while ( $row = $modx->db->getRow($rs) ) {
		$fields[] = $row['Field'];
	}
	
	unset($fields[0]);
	unset($fields[1]);

	echo "<h1>Getting Fields</h1>";

	print_r($fields);


	// Create new TV's based on above field list:
	echo "<h1>Inserting new TVs</h1>";
	foreach ( $fields as $field ) {	
		$insertflds = array(
						'type' => 'text',
						'name' => $field,
						'caption' => $field,
						'description' => $field,
						'editor_type' => 0,
						'category' => 0,
						'editedon' => 0,
						'createdon' => 0,
						'locked' => 0);

		print_r($insertflds);
		echo "Inserting as TV's<br />";
		
		if ( $_GET['commit'] == 'yes' ) {
			$modx->db->insert($insertflds, $tbSiteTVs );
			$newId = $modx->db->getInsertId();
		}
		
		$newRolesID[] = $newId;
		
		// Assign each new field to a role!

		// Insert new ID into roles
		$roleData = array(
						'tmplvarid' => $newId,
						'roleid'	=> $roleid,
						'rank' 		=> 0
						);
		print_r($roleData);
		echo "<br />";
		
		if ( $_GET['commit'] == 'yes' ) $modx->db->insert($roleData, $tbUserRoleVar);	
	}

	echo "<h1>Done! Working</h1>";

	echo "<h1>New Role IDs</h1>";
	print_r($newRolesID);

	echo "<h1>Updating User Roles</h1>";
	// Change User Roles for each user
	$sql = "SELECT * FROM ".$tbUsers.";";
	$rs = $modx->db->query($sql);

	while ( $row = $modx->db->getRow($rs) ) {
		// Change role of each user
		// This will mean inserting a record for users who haven't been backend updated 
		// or changing the role for those that have!
		
		$sql1 = "SELECT * FROM ". $tbUserAttributes . " WHERE `internalKey` = ".$row['id'].";";
		$rs1 = $modx->db->query($sql1);
		$count = $modx->db->getRecordCount($rs1);
		
		if ( $count ) {
			// Update
			$roleUpdate = array(
							'role' => $roleid
							);
			if ( $row['id'] != $adminid ) echo "Updating: ".$row['username']." id ".$row['id']." to role ".$roleid."<br />";
			if ( $_GET['commit'] == 'yes' ) $modx->db->update($roleUpdate, $tbUserAttributes, 'internalKey='.$row['id'].' AND NOT internalKey='.$adminid);
		} 
	}

	echo "<h1>Done!</h1>";

	echo "<h1>Creating User TV Values!</h1>";
	// For each user role var we need to update user values accordingly
	// for each role(field) we need to populate the value from extended
	$i = 0;
	foreach ( $fields as $field ) {
		// first field to first role
		$sql = "SELECT internalKey, ".$field. " FROM ". $tbExtended.";";
		$rs = $modx->db->query($sql);
		echo "Number of Records: ".$modx->db->getRecordCount($rs)."<br />";
		
		while ( $row = $modx->db->getRow($rs) ) 	{
			//Insert TV data for each field for each user!
			$insertTVValue = array(
								'tmplvarid' => $newRolesID[$i],
								'userid' => $row['internalKey'],
								'value' => $row[$field]
								);
			echo "Updating: ".$newRolesID[$i]. " User: ".$row['internalKey']." Value: ".$row[$field]."<br />";
			
			if ( $_GET['commit'] == 'yes' ) $modx->db->insert($insertTVValue,  $tbUserValues);
		}
		$i++;
	}

	echo "<h1>Done!</h1>";
} else {
	echo "<form><input name='update' value='view' type='submit' /></form> ";
}
