<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

<style>
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        transition: background-color 5000s ease-in-out 0s;
    }
</style>

<div class="w-full container mx-auto px-6 pt-6">
    <div class="flex justify-between items-center">
        <a class="flex items-center text-purple-600 no-underline hover:no-underline font-bold text-2xl lg:text-4xl select-none"
            href="./">
            <!-- Brand Icon-->
            <svg class="h-8 fill-current text-purple-600 pr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 601 572">
                <path
                    d="M549.528 354.832C587.161 317.204 549.528 231.128 549.528 163.867C549.528 96.6061 525.301 139.644 473.555 87.9045C421.81 36.1652 428.395 77.7918 374.533 23.936C320.67 -29.9199 277.627 23.936 199.773 23.936C121.684 23.936 59.824 163.867 59.824 163.867C-93.5319 163.867 104.278 326.611 38.4201 392.461C-27.4383 458.311 108.277 462.309 183.544 537.566C258.81 612.823 342.309 537.566 438.98 537.566C535.886 537.566 417.576 427.267 549.293 427.267C681.48 427.502 511.894 392.461 549.528 354.832ZM426.043 357.184C359.715 357.184 419.222 412.686 370.534 412.686C321.846 412.686 279.744 450.55 241.875 412.686C204.007 374.822 135.561 372.706 168.725 339.546C201.89 306.385 102.397 224.308 179.545 224.308C179.545 224.308 210.593 153.755 250.108 153.755C289.387 153.755 311.026 126.709 338.075 153.755C365.124 180.8 361.831 159.869 387.94 185.974C414.048 212.079 426.278 190.442 426.278 224.308C426.278 258.174 445.33 301.447 426.278 320.496C406.991 339.546 492.372 357.184 426.043 357.184Z" />
            </svg>
            <!-- Text -->
            POSTOGON
        </a>
    </div>
</div>
<!-- BEGIN ELEMENTS -->
<main
    class="bg-white max-w-md mx-auto p-8 md:border-t-8 md:border-purple-700 md:p-12 md:my-10 rounded lg:shadow-2xl md:shadow-lg sm:shadow-sm">
    <section>
        <div class="pb-8">
            <h3 class="font-bold text-2xl text-center">Log into Postogon Admin</h3>
            <?php if (isset($isAuthorized) && $isAuthorized === true): ?>
                <div class="pt-8">
                    <div class="bg-green-200 border-l-4 border-green-300 text-green-800 p-4">
                        <p class="font-bold">Success!</p>
                        <p>You have logged in as an admin.</p>
                        <p>Loading...<?php header('Refresh: 0;');?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="pt-8">
                    <div class="bg-red-200 border-l-4 border-red-300 text-red-800 p-4">
                        <p class="font-bold">Error!</p>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </section>
    <section>
        <form class="flex flex-col" method="POST" action="">
            <div class="mb-6 pt-3 rounded bg-gray-200">
                <label class="block text-gray-400 text-small font-bold mb-2 ml-3 select-none" for="emailoruser">Email /
                    Username</label>
                <input type="text" id="emailoruser" name="login_emailoruser" value=""
                    class="bg-gray-200 rounded w-full text-gray-700 focus:outline-none border-b-4 border-gray-300 focus:border-purple-600 transition duration-500 px-3 pb-3" />
            </div>

            <div class="mb-6 pt-3 rounded bg-gray-200">

                <div class="relative w-full">
                    <label class="block text-gray-400 focus:text-gray-500 text-small font-bold mb-2 ml-3 select-none"
                        for="password">Password</label>
                    <input type="password" id="password" autocomplete="off" name="login_password"
                        class="bg-gray-200 rounded w-full text-gray-700 focus:outline-none border-b-4 border-gray-300 focus:border-purple-600 transition duration-500 js-password px-3 pb-3" />
                </div>
            </div>
            <input type="hidden" name="form_type" value="user_login">
            <button name="login"
                class="border border-purple-600 bg-white hover:bg-gray-100 hover:border-purple-300 hover:text-purple-400 text-purple-500 font-bold py-2 focus:outline-none rounded shadow-sm hover:shadow-md transition duration-200"
                type="submit">Log In</button>
        </form>
    </section>
</main>
<!-- Imprint -->
<div class="md:flex items-center text-center text-sm">
    <div class="py-3 text-gray-500 select-none text-center mx-auto border-b md:border-b-0">
        © 2021 Imperfect and Company
    </div>
    <div class="mx-auto mb-4 md:mb-0">
        <div class="w-48 text-gray-500 transition inline-block relative space-x-8 mb-4 mt-4 md:mt-0 md:mb-0">
            <a href="https://imperfectandcompany.com/careers/" target="_blank" class="hover:text-gray-800">Jobs</a>
            <a href="https://imperfectandcompany.com/terms-of-service/" target="_blank"
                class="hover:text-gray-800">Legal</a>
            <a href="https://imperfectandcompany.com/privacy-policy/" target="_blank"
                class="hover:text-gray-800">Privacy</a>
        </div>
        <div>
        </div>
    </div>
</div>
