<?php
// ... (existing PHP code to fetch data)
?>

<script>
    // JavaScript functions to handle CRUD operations and AJAX calls to the backend
    function userList() {
        return {
            searchQuery: '',
            users: [],
            currentPage: 1,
            perPage: 10,
            totalPages: 1,
            fetchUsers: function () {
                this.fetchTotalUsers();  // Fetch total users based on the current search term
                let url = this.searchQuery
                    ? `https://admin.postogon.com/admin/users/${this.searchQuery}/${this.currentPage}/${this.perPage}`
                    : `https://admin.postogon.com/admin/users/${this.currentPage}/${this.perPage}`;
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        this.users = data.data.results;

                    });
            },
            fetchTotalUsers: function () {

                let url = this.searchQuery
                    ? `https://admin.postogon.com/admin/users/count/${this.searchQuery}`
                    : `https://admin.postogon.com/admin/users/count`;
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        this.totalPages = Math.ceil(data.data.results[0].total / this.perPage);
                    });
            },
            handleSearch: function () {
                this.currentPage = 1; // reset the current page to 1 only when search query changes
                this.fetchUsers();
            },
            changePage: function (page) {
                this.currentPage = page;
                this.fetchUsers();
            },
            viewUserDetails: function (userId) {
                // Implement view logic here
            },
            editUser: function (userId) {
                // Implement edit logic here
            },
            deleteUser: function (userId) {
                // Implement delete logic here
            },
            get pages() {
                return Array.from({ length: this.totalPages }, (_, i) => i + 1);
            }
        };
    }

</script>


<div x-data="userList()" x-init="fetchTotalUsers(); fetchUsers()" class="p-8">


    <!-- Search bar -->
    <input x-model="searchQuery" @input="handleSearch" placeholder="Search users..." class="border p-2 rounded">

    <!-- User table -->
    <table class="min-w-full mt-4 border">
        <thead>
            <tr>
                <th class="border p-2">User Name</th>
                <th class="border p-2">Email</th>
                <th class="border p-2">Role</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="user in users" :key="user.id">
                <tr>
                    <td class="border p-2" x-text="user.username"></td>
                    <td class="border p-2" x-text="user.email"></td>
                    <td class="border p-2" x-text="user.admin == 1 ? 'Admin' : 'User'"></td>
                    <td class="border p-2">
                        <button @click="viewUserDetails(user.id)"
                            class="bg-blue-500 text-white p-1 rounded">View</button>
                        <button @click="editUser(user.id)" class="bg-yellow-500 text-white p-1 rounded">Edit</button>
                        <button @click="deleteUser(user.id)" class="bg-red-500 text-white p-1 rounded">Delete</button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        <template x-for="page in pages" :key="page">
            <button @click="changePage(page)" x-text="page" class="border p-2"
                :class="{'bg-blue-500 text-white': currentPage == page}" :disabled="currentPage == page">
            </button>
        </template>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>