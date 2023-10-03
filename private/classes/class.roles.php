<?php
/*
define('PERMISSION_READ', 1);
define('PERMISSION_WRITE', 2);
define('PERMISSION_DELETE', 4);
define('PERMISSION_ADMIN', 8);

// Define bitwise permission flags
define('PERMISSION_READ', 1);
define('PERMISSION_WRITE', 2);
define('PERMISSION_DELETE', 4);
define('PERMISSION_ADMIN', 8);

// Assign multiple permissions to a user
$userPermissions = PERMISSION_READ | PERMISSION_WRITE;

// Check if the user has a specific permission using bitwise AND
if ($userPermissions & PERMISSION_READ) {
    // User has read permission
}

// Check if the user has a specific combination of permissions
if (($userPermissions & PERMISSION_READ) && ($userPermissions & PERMISSION_WRITE)) {
    // User has both read and write permissions
}

// Check if the user has at least one of the specified permissions
if (($userPermissions & PERMISSION_READ) || ($userPermissions & PERMISSION_WRITE)) {
    // User has either read or write permissions
}

// Check if the user has all of the specified permissions
if (($userPermissions & PERMISSION_READ) && ($userPermissions & PERMISSION_WRITE)) {
    // User has both read and write permissions
}

// Check if the user has no permissions
if ($userPermissions === 0) {
    // User has no permissions
}

// Check if the user has all permissions
if ($userPermissions === (PERMISSION_READ | PERMISSION_WRITE | PERMISSION_DELETE | PERMISSION_ADMIN)) {
    // User has all permissions
}

// Check if the user has at least one permission
if ($userPermissions !== 0) {
    // User has at least one permission
}

this is going to used for the bitwise permissions when user 
is accessing like a post to see if they own it or not, more dynamic from 
the systembecause it has to do with the relationship user is 
interacting with (their own post, a followed account but not contact,
a contact etc.)

*/

class Roles
{
    private $dbConnection;
    const ADMIN_ROLE_NAME = 'admin';


    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Create a new role.
     *
     * @param string $name The name of the role.
     * @return int|false The ID of the newly created role or false on failure.
     */
    public function createRole($name)
    {

        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }

        $query = "INSERT INTO roles (name, createdAt, updatedAt) VALUES (:name, NOW(), NOW())";
        $params = [':name' => [$name, PDO::PARAM_STR]];

        $result = $this->dbConnection->runQuery("insert", $query, $params);

        return ($result !== false) ? $result["insertID"] : false;
    }

    /**
     * Read all roles.
     *
     * @return array|false An array of roles or false on failure.
     */
    public function getAllRoles()
    {
        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }
        $query = "SELECT * FROM roles";
        $result = $this->dbConnection->query($query);

        return ($result !== false) ? $result : false;
    }

    /**
     * Read a role by ID.
     *
     * @param int $roleId The ID of the role to retrieve.
     * @return array|false The role data or false if the role does not exist.
     */
    public function getRoleById($roleId)
    {
        $query = "SELECT * FROM roles WHERE id = :id";
        $params = [':id' => [$roleId, PDO::PARAM_INT]];

        $result = $this->dbConnection->viewSingleData('roles', '*', 'WHERE id = :id', $params);

        return ($result !== false && $result["count"] > 0) ? $result["result"] : false;
    }

    /**
     * Get all roles grouped by permissions.
     *
     * @return array|false An array of roles grouped by permissions or false on failure.
     */
    public function getAllRolesGroupedByPermissions()
    {
        $query = "
            SELECT r.name AS role_name, p.name AS permission_name
            FROM roles AS r
            LEFT JOIN role_permissions AS rp ON r.id = rp.role_id
            LEFT JOIN permissions AS p ON rp.permission_id = p.id
            ORDER BY r.name, p.name
        ";
        $result = $this->dbConnection->query($query);

        if ($result === false) {
            return false;
        }

        $rolesGroupedByPermissions = [];

        foreach ($result as $row) {
            $roleName = $row['role_name'];
            $permissionName = $row['permission_name'];

            if (!isset($rolesGroupedByPermissions[$roleName])) {
                $rolesGroupedByPermissions[$roleName] = [];
            }

            if ($permissionName) {
                $rolesGroupedByPermissions[$roleName][] = $permissionName;
            }
        }

        return $rolesGroupedByPermissions;
    }

    /**
     * Get user roles and permissions.
     *
     * @param int $userId The ID of the user.
     * @return array|false An array containing user roles and permissions or false on failure.
     */
    public function whois($userId)
    {
        $userRolesQuery = "SELECT role_id FROM user_roles WHERE user_id = :user_id";
        $userRolesParams = [':user_id' => [$userId, PDO::PARAM_INT]];
        $userRolesResult = $this->dbConnection->query($userRolesQuery, $userRolesParams);

        if ($userRolesResult === false) {
            return false;
        }

        $userPermissions = [];
        $userRoles = [];

        foreach ($userRolesResult as $row) {
            $roleId = $row['role_id'];
            $roleName = $this->getRoleById($roleId)['name'];

            if ($roleName) {
                $userRoles[] = $roleName;

                $permissionsQuery = "
                    SELECT p.name
                    FROM role_permissions AS rp
                    LEFT JOIN permissions AS p ON rp.permission_id = p.id
                    WHERE rp.role_id = :role_id
                ";
                $permissionsParams = [':role_id' => [$roleId, PDO::PARAM_INT]];
                $permissionsResult = $this->dbConnection->query($permissionsQuery, $permissionsParams);

                if ($permissionsResult !== false) {
                    foreach ($permissionsResult as $permRow) {
                        $userPermissions[] = $permRow['name'];
                    }
                }
            }
        }

        return [
            'user_roles' => $userRoles,
            'user_permissions' => $userPermissions,
        ];
    }

    /**
     * Grant admin privileges to a user.
     *
     * @param int $userId The ID of the user.
     * @return bool True if the operation was successful, false on failure.
     */
    public function grantAdminPrivileges($userId)
    {
        // Check if the user already has admin privileges
        if ($this->userHasRole($userId, self::ADMIN_ROLE_NAME)) {
            return true; // User is already an admin
        }

        // Get the role ID for the "admin" role from the database
        $adminRole = $this->getRoleByName(self::ADMIN_ROLE_NAME);

        if (!$adminRole) {
            // The "admin" role does not exist
            return false;
        }

        // Grant admin privileges to the user by assigning the admin role
        return $this->assignRoleToUser($userId, $adminRole['id']);
    }

    /**
     * Get a role by name.
     *
     * @param string $name The name of the role.
     * @return array|false The role data or false if the role does not exist.
     */
    public function getRoleByName($name)
    {
        $query = "SELECT * FROM roles WHERE name = :name";
        $params = [':name' => [$name, PDO::PARAM_STR]];

        $result = $this->dbConnection->viewSingleData('roles', '*', 'WHERE name = :name', $params);

        return ($result !== false && $result["count"] > 0) ? $result["result"] : false;
    }

    /**
     * Check if a user is an admin.
     *
     * @param int $userId The ID of the user.
     * @return bool True if the user is an admin, false otherwise.
     */
    public function isUserAdmin($userId)
    {
        return $this->userHasRoleByName($userId, self::ADMIN_ROLE_NAME);
    }


    /**
     * Update a role by ID.
     *
     * @param int $roleId The ID of the role to update.
     * @param string $name The new name for the role.
     * @return true|false True if the update was successful, false on failure.
     */
    public function updateRole($roleId, $name)
    {
        $query = "UPDATE roles SET name = :name, updatedAt = NOW() WHERE id = :id";
        $params = [
            ':name' => [$name, PDO::PARAM_STR],
            ':id' => [$roleId, PDO::PARAM_INT]
        ];

        return $this->dbConnection->runQuery("update", $query, $params);
    }

    /**
     * Delete a role by ID.
     *
     * @param int $roleId The ID of the role to delete.
     * @return true|false True if the deletion was successful, false on failure.
     */
    public function deleteRole($roleId)
    {

        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }

        $query = "DELETE FROM roles WHERE id = :id";
        $params = [':id' => [$roleId, PDO::PARAM_INT]];

        return $this->dbConnection->runQuery("delete", $query, $params);
    }

    /**
     * Assign a role to a user.
     *
     * @param int $userId The ID of the user.
     * @param int $roleId The ID of the role.
     * @return bool True if the assignment was successful, false on failure.
     */
    public function assignRoleToUser($userId, $roleId)
    {

        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }

        $query = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $params = [
            ':user_id' => [$userId, PDO::PARAM_INT],
            ':role_id' => [$roleId, PDO::PARAM_INT]
        ];

        return $this->dbConnection->runQuery("insert", $query, $params);
    }

    /**
     * Grant a permission to a role.
     *
     * @param int $roleId The ID of the role.
     * @param int $permissionId The ID of the permission.
     * @return bool True if the grant was successful, false on failure.
     */
    public function grantPermissionToRole($roleId, $permissionId)
    {

        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }

        $query = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
        $params = [
            ':role_id' => [$roleId, PDO::PARAM_INT],
            ':permission_id' => [$permissionId, PDO::PARAM_INT]
        ];

        return $this->dbConnection->runQuery("insert", $query, $params);
    }

    /**
     * Grant a permission to a user.
     *
     * @param int $userId The ID of the user.
     * @param int $permissionId The ID of the permission.
     * @return bool True if the grant was successful, false on failure.
     */
    public function grantPermissionToUser($userId, $permissionId)
    {

        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }

        $query = "INSERT INTO user_permissions (user_id, permission_id) VALUES (:user_id, :permission_id)";
        $params = [
            ':user_id' => [$userId, PDO::PARAM_INT],
            ':permission_id' => [$permissionId, PDO::PARAM_INT]
        ];

        return $this->dbConnection->runQuery("insert", $query, $params);
    }

    /**
     * Check if a user has a specific role by name.
     *
     * @param int $userId The ID of the user.
     * @param string $roleName The name of the role to check.
     * @return bool True if the user has the role, false otherwise.
     */
    public function userHasRoleByName($userId, $roleName)
    {
        try {
            $query = "
            SELECT COUNT(*) AS count
            FROM user_roles AS ur
            JOIN roles AS r ON ur.role_id = r.id
            WHERE ur.user_id = :user_id AND r.name = :role_name
            ";
    
        $query = 'SELECT COUNT(*) AS count FROM user_roles AS ur JOIN roles AS r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name = ?';
        $paramsArray = array($userId, $roleName);
        $result = $this->dbConnection->query($query, $paramsArray);
                // Check if $result is null or not an array
            if ($result === null || !is_array($result)) {
                // return false in a sec
                throwError("Database query failed or returned non-array result.");
                return false;
            }
            if (isset($result[0]) && isset($result[0]['count']) && $result[0]['count'] > 0) {
                return true; // User has the role
            }
        } catch (Exception $e) {
            throwError("Error: " . $e->getMessage());
            return false;
        }
    
        return false; // User does not have the role
    }
    

    public function revokeRoleFromUser($roleId, $userId)
    {

        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }
        // TODO: Implement logic to remove a role from a user
        // Delete the record from the 'user_roles' table
    }

    public function userHasRole($userId, $roleId)
    {
        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }
        // TODO: Implement logic to check if a user has a specific role
        // Query the 'user_roles' table to check if the user has the specified role
    }

    public function revokeAdminPrivileges($userId)
    {
        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }
        // TODO: Implement logic to remove admin privileges from a user
        // Delete the admin role record from the 'user_roles' table for the user
    }

    public function userHasPermission($userId, $permissionId)
    {
        // Check if the current user is an admin
        if (!$this->isUserAdmin($GLOBALS['user_id'])) {
            return false; // Only admins can create roles
        }
        // Check user-specific permissions
        $queryUser = "
            SELECT COUNT(*) AS count
            FROM user_permissions AS up
            WHERE up.user_id = :user_id AND up.permission_id = :permission_id
        ";

        // Check permissions inherited from roles
        $queryRole = "
            SELECT COUNT(*) AS count
            FROM user_roles AS ur
            JOIN role_permissions AS rp ON ur.role_id = rp.role_id
            WHERE ur.user_id = :user_id AND rp.permission_id = :permission_id
        ";

        $params = [
            ':user_id' => [$userId, PDO::PARAM_INT],
            ':permission_id' => [$permissionId, PDO::PARAM_INT]
        ];

        $resultUser = $this->dbConnection->query($queryUser, $params);
        $resultRole = $this->dbConnection->query($queryRole, $params);

        // Check if the user has the permission either directly or through roles
        return ($resultUser !== false && $resultUser[0]['count'] > 0) || ($resultRole !== false && $resultRole[0]['count'] > 0);
    }

}

?>