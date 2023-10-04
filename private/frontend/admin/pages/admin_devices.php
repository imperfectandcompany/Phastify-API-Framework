<?php
// ... (existing PHP code to fetch data)
?>

<!-- Device Management UI -->

<div id="deviceManagementTab">
    <!-- Display the list of devices -->
    <table>
        <!-- Table headers -->
        <thead>
            <tr>
                <th>Device Name</th>
                <th>Device Type</th>
                <th>Associated IPs</th>
                <th>Actions</th>
            </tr>
        </thead>
        <!-- Table data (Loop through the devices and display them) -->
        <tbody>
            <!-- Example row (repeat for each device) -->
            <tr>
                <td>Device Name</td>
                <td>Device Type</td>
                <td><button onclick="showIPsForDevice(deviceId)">View IPs</button></td>
                <td>
                    <button onclick="editDevice(deviceId)">Edit</button>
                    <button onclick="deleteDevice(deviceId)">Delete</button>
                </td>
            </tr>
        </tbody>
    </table>
    
    <!-- Modals, forms, and other UI components for adding, editing, and deleting devices -->
</div>

<script>
// JavaScript functions to handle CRUD operations and AJAX calls to the backend
</script>
