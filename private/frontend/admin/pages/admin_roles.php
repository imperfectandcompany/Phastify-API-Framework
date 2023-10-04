<?php

?>
<!-- Button to trigger edit role modal -->
<button data-toggle="modal" data-target="#editRoleModal">Edit Role</button>

<!-- Modal for editing role -->
<div id="editRoleModal" class="modal">
    <!-- Modal content for editing role: role name, description, etc. -->
    <!-- ... -->
    <button onclick="submitRoleUpdate()">Save Changes</button>
</div>
<!-- Button to trigger delete confirmation -->
<button onclick="confirmDeleteRole(roleId)">Delete Role</button>

<script>
    function submitRoleUpdate() {
    // Gather data from the form
    // ...
    // Make an AJAX call to the backend to update the role
    // ...
}

function confirmDeleteRole(roleId) {
    if (confirm("Are you sure you want to delete this role?")) {
        // Make an AJAX call to the backend to delete the role
        // ...
    }
}
</script>