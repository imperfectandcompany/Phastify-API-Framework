<?php   
//for admin dashboard stuff

// Instantiate the Roles class
$roles = new Roles($db);

// Create a new role
$createdRoleId = $roles->createRole("Admin");

if ($createdRoleId !== false) {
    echo "Role created with ID: $createdRoleId<br>";
} else {
    echo "Failed to create role<br>";
}

// Get all roles
$allRoles = $roles->getAllRoles();

if ($allRoles !== false) {
    foreach ($allRoles as $role) {
        echo "Role ID: {$role['id']}, Name: {$role['name']}<br>";
    }
} else {
    echo "Failed to retrieve roles<br>";
}

// Get a role by ID
$roleId = 1; // Replace with the desired role ID
$roleById = $roles->getRoleById($roleId);

if ($roleById !== false) {
    echo "Role ID: {$roleById['id']}, Name: {$roleById['name']}<br>";
} else {
    echo "Role not found<br>";
}

// Update a role
$newRoleName = "Super Admin";
$updateResult = $roles->updateRole($roleId, $newRoleName);

if ($updateResult !== false) {
    echo "Role updated successfully<br>";
} else {
    echo "Failed to update role<br>";
} 
