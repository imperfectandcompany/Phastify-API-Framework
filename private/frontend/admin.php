<?php
include_once($GLOBALS['config']['private_folder'] . '/backend/admin.php');
?>
<html class="light">
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Admin <?php echo $page ?> - Postogon</title>
  </head>

<script src="https://cdn.tailwindcss.com"></script>

<body class="bg-gray-50 ">
    <?php
    include_once($GLOBALS['config']['private_folder'] . '/frontend/admin/admin_navbar.php');
    ?>
    <div class="flex pt-16 overflow-hidden bg-gray-50">
        <?php
        include_once($GLOBALS['config']['private_folder'] . '/frontend/admin/admin_sidebar.php');
        ?>
        <div class="relative w-full h-full overflow-y-auto bg-gray-50 lg:ml-64 ">
            
            <main>
                <div class="px-4 pt-6">
                <h1 class="text-3xl mb-4">Admin
                <?php echo isset($page) ? $page : "" ?>
            </h1>
                    <div class="grid gap-4 xl:grid-cols-2 2xl:grid-cols-3">
                        
                        <?php
                        $page_include = strtolower(isset($page) ? "admin_" . $page : "admin_dashboard");
                        include_once($GLOBALS['config']['private_folder'] . '/frontend/admin/pages/' . $page_include . '.php');
                        ?>
                    </div>
                </div>
            </main>
        </div>
</div>
    
  <script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="https://flowbite-admin-dashboard.vercel.app//app.bundle.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.2/datepicker.min.js"></script>
  </body>

  </html>
<?php die(); ?>