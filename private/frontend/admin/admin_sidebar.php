<aside id="sidebar"
    class="fixed top-0 left-0 z-20 flex flex-col flex-shrink-0 hidden w-64 h-full pt-16 font-normal duration-75 lg:flex transition-width"
    aria-label="Sidebar">
    <div class="relative flex flex-col flex-1 min-h-0 pt-0 bg-white border-r border-gray-200">
        <div class="flex flex-col flex-1 pt-5 pb-4 overflow-y-auto">
            <div class="flex-1 px-3 space-y-1 bg-white divide-y divide-gray-200">
                <ul class="pb-2 space-y-2">
                    <li>
                        <form action="https://admin.postogon.com/admin/search" method="GET" class="lg:hidden">
                            <input type="hidden" name="category" value="all" />
                            <label for="mobile-search" class="sr-only">Searchs</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <input type="text" name="query" id="mobile-search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5"
                                    placeholder="Search">
                            </div>
                        </form>
                    </li>
                    <li>

                        <a href="https://admin.postogon.com/admin/dashboard"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group  ">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                            </svg>
                            <span
                                class="ml-3 <?php echo (strtolower($page) == "dashboard" ? "text-gray-900" : "text-gray-500"); ?>"
                                sidebar-toggle-item>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <button type="button"
                            class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100  "
                            aria-controls="dropdown-crud" data-collapse-toggle="dropdown-crud"
                            aria-expanded="<?php echo (strtolower($page) == "service" ? "true" : "false"); ?>">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 "
                                fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M.99 5.24A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25l.01 9.5A2.25 2.25 0 0116.76 17H3.26A2.267 2.267 0 011 14.74l-.01-9.5zm8.26 9.52v-.625a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75v.615c0 .414.336.75.75.75h5.373a.75.75 0 00.627-.74zm1.5 0a.75.75 0 00.627.74h5.373a.75.75 0 00.75-.75v-.615a.75.75 0 00-.75-.75H11.5a.75.75 0 00-.75.75v.625zm6.75-3.63v-.625a.75.75 0 00-.75-.75H11.5a.75.75 0 00-.75.75v.625c0 .414.336.75.75.75h5.25a.75.75 0 00.75-.75zm-8.25 0v-.625a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75v.625c0 .414.336.75.75.75H8.5a.75.75 0 00.75-.75zM17.5 7.5v-.625a.75.75 0 00-.75-.75H11.5a.75.75 0 00-.75.75V7.5c0 .414.336.75.75.75h5.25a.75.75 0 00.75-.75zm-8.25 0v-.625a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75V7.5c0 .414.336.75.75.75H8.5a.75.75 0 00.75-.75z">
                                </path>
                            </svg>
                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap <?php echo (strtolower($page) == "users" || strtolower($page) == "services" || strtolower($page) == "integrations" ? "text-gray-600" : "text-gray-500"); ?>"
                                sidebar-toggle-item>Management</span>
                            <svg sidebar-toggle-item class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <ul id="dropdown-crud"
                            class="<?php echo (strtolower($page) == "users" || strtolower($page) == "services" || strtolower($page) == "integrations" ? "" : "hidden"); ?> py-2 space-y-2">
                            <li>
                                <a href="https://admin.postogon.com/admin/users" aria-current="page"
                                    class="flex items-center p-2 text-base <?php echo (strtolower($page) == "users" ? "text-gray-900" : "text-gray-500"); ?> transition duration-75 rounded-lg pl-11 group hover:bg-gray-100  ">Users</a>
                            </li>
                            <li>
                                <a href="https://admin.postogon.com/admin/services" aria-current="page"
                                    class="flex items-center p-2 text-base <?php echo (strtolower($page) == "services" ? "text-gray-900" : "text-gray-500"); ?> transition duration-75 rounded-lg pl-11 group hover:bg-gray-100  ">Services</a>
                            </li>
                            <li>
                                <a href="https://admin.postogon.com/admin/integrations" aria-current="page"
                                    class="flex items-center p-2 text-base <?php echo (strtolower($page) == "integrations" ? "text-gray-900" : "text-gray-500"); ?> transition duration-75 rounded-lg pl-11 group hover:bg-gray-100  ">Integrations</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="https://admin.postogon.com/admin/tests/"
                            class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group ">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 "
                                fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M8.34 1.804A1 1 0 019.32 1h1.36a1 1 0 01.98.804l.295 1.473c.497.144.971.342 1.416.587l1.25-.834a1 1 0 011.262.125l.962.962a1 1 0 01.125 1.262l-.834 1.25c.245.445.443.919.587 1.416l1.473.294a1 1 0 01.804.98v1.361a1 1 0 01-.804.98l-1.473.295a6.95 6.95 0 01-.587 1.416l.834 1.25a1 1 0 01-.125 1.262l-.962.962a1 1 0 01-1.262.125l-1.25-.834a6.953 6.953 0 01-1.416.587l-.294 1.473a1 1 0 01-.98.804H9.32a1 1 0 01-.98-.804l-.295-1.473a6.957 6.957 0 01-1.416-.587l-1.25.834a1 1 0 01-1.262-.125l-.962-.962a1 1 0 01-.125-1.262l.834-1.25a6.957 6.957 0 01-.587-1.416l-1.473-.294A1 1 0 011 10.68V9.32a1 1 0 01.804-.98l1.473-.295c.144-.497.342-.971.587-1.416l-.834-1.25a1 1 0 01.125-1.262l.962-.962A1 1 0 015.38 3.03l1.25.834a6.957 6.957 0 011.416-.587l.294-1.473zM13 10a3 3 0 11-6 0 3 3 0 016 0z">
                                </path>
                            </svg>
                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap <?php echo (strtolower($page) == "tests" ? "text-gray-900" : "text-gray-500"); ?>"
                                sidebar-toggle-item="">Tests</span>
                        </a>
                    </li>

                    <li>
                        <button type="button"
                            class="flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100  "
                            aria-controls="dropdown-layouts" data-collapse-toggle="dropdown-layouts"
                            aria-expanded="<?php echo (strtolower($page) == "service" ? "true" : "false"); ?>">
                            <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd"
                                    d="M.99 5.24A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25l.01 9.5A2.25 2.25 0 0116.76 17H3.26A2.267 2.267 0 011 14.74l-.01-9.5zm8.26 9.52v-.625a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75v.615c0 .414.336.75.75.75h5.373a.75.75 0 00.627-.74zm1.5 0a.75.75 0 00.627.74h5.373a.75.75 0 00.75-.75v-.615a.75.75 0 00-.75-.75H11.5a.75.75 0 00-.75.75v.625zm6.75-3.63v-.625a.75.75 0 00-.75-.75H11.5a.75.75 0 00-.75.75v.625c0 .414.336.75.75.75h5.25a.75.75 0 00.75-.75zm-8.25 0v-.625a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75v.625c0 .414.336.75.75.75H8.5a.75.75 0 00.75-.75zM17.5 7.5v-.625a.75.75 0 00-.75-.75H11.5a.75.75 0 00-.75.75V7.5c0 .414.336.75.75.75h5.25a.75.75 0 00.75-.75zm-8.25 0v-.625a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75V7.5c0 .414.336.75.75.75H8.5a.75.75 0 00.75-.75z">
                                </path>
                            </svg>
                            <span
                                class="flex-1 ml-3 text-left whitespace-nowrap <?php echo (strtolower($page) == "service" || strtolower($page) == "logs" ? "text-gray-600" : "text-gray-500"); ?>"
                                sidebar-toggle-item>Metrics</span>
                            <svg sidebar-toggle-item class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <ul id="dropdown-layouts"
                            class="<?php echo (strtolower($page) == "service" || strtolower($page) == "logs" ? "" : "hidden"); ?> py-2 space-y-2">
                            <li>
                                <a href="https://admin.postogon.com/admin/service" aria-current="page"
                                    class="flex items-center p-2 text-base <?php echo (strtolower($page) == "service" ? "text-gray-900" : "text-gray-500"); ?> transition duration-75 rounded-lg pl-11 group hover:bg-gray-100  ">Service</a>
                            </li>
                            <li>
                                <a href="https://admin.postogon.com/admin/logs" aria-current="page"
                                    class="flex items-center p-2 text-base <?php echo (strtolower($page) == "logs" ? "text-gray-900" : "text-gray-500"); ?> transition duration-75 rounded-lg pl-11 group hover:bg-gray-100  ">Logs</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</aside>

<div class="fixed inset-0 z-10 hidden bg-gray-900/50 dark:bg-gray-900/90" id="sidebarBackdrop"></div>