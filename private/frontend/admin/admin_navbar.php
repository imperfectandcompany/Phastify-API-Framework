<nav class="fixed z-30 w-full bg-white border-b border-gray-200 ">
  <div class="px-3 py-3 lg:px-5 lg:pl-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center justify-start">
        <button id="toggleSidebarMobile" aria-expanded="true" aria-controls="sidebar"
          class="p-2 text-gray-600 rounded cursor-pointer lg:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 focus:ring-2 focus:ring-gray-100">
          <svg id="toggleSidebarMobileHamburger" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
              clip-rule="evenodd"></path>
          </svg>
          <svg id="toggleSidebarMobileClose" class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"></path>
          </svg>
        </button>
        <a href="https://admin.postogon.com/admin/dashboard" class="flex ml-2 md:mr-24">
          <img src="https://cdn.postogon.com/assets/img/logo/logo.svg" class="h-8 mr-3" alt="Postogon Logo" />
          <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap">Postogon</span>
        </a>
        <form action="https://admin.postogon.com/admin/search" method="GET" class="hidden lg:block lg:pl-3.5">
            <input type="hidden" name="category" value="all"/>
            <label for="topbar-search" class="sr-only">Sesarch</label>
          <div class="relative mt-1 lg:w-96">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd"></path>
              </svg>
            </div>
            <input type="text" name="query" id="topbar-search"
              class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5"
              placeholder="Search">
          </div>
        </form>
      </div>
      <div class="flex items-center">
        <button id="toggleSidebarMobileSearch" type="button"
          class="p-2 text-gray-500 rounded-lg lg:hidden hover:text-gray-900 hover:bg-gray-100 ">
          <span class="sr-only">Search</span>
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd"
              d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
              clip-rule="evenodd"></path>
          </svg>
        </button>
        <div class="flex items-center ml-3">
          <div>
            <button type="button"
              class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300"
              id="user-menu-button-2" aria-expanded="false" data-dropdown-toggle="dropdown-2">
              <span class="sr-only">Open user menu</span>
              <img class="w-8 h-8 rounded-full" src="<?php echo $avatarUrl ?>" alt="user photo">
            </button>
          </div>
          <div
            class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow"
            id="dropdown-2">
            <div class="px-4 py-3" role="none">
              <p class="text-sm text-gray-900" role="none">
                <?php echo $username; ?>
              </p>
              <p class="text-sm font-medium text-gray-900 truncate" role="none">
                <?php echo $email; ?>
              </p>
            </div>
            <ul class="py-1" role="none">
              <li>
                <a href="https://admin.postogon.com/admin/dashboard"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  role="menuitem">Dashboard</a>
              </li>
              <li>
                <form method="get" role="menuitem">
                  <input type="hidden" name="confirm" value="true">
                  <button type="submit"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    role="menuitem">
                    Logout
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
