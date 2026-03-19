<?php
require_once __DIR__ . '/security.php';
secure_session_start();
?>
<nav id="sidebar" class="mx-lt-6 bg-white border-right shadow-sm">
    <div class="sidebar-list py-3">

        <a href="index.php?page=home" class="nav-item nav-home">Home</a>
        <a href="index.php?page=laundry" class="nav-item nav-laundry">Laundry List</a>
        <a href="index.php?page=categories" class="nav-item nav-categories">Laundry Category</a>		
        <a href="index.php?page=inventory" class="nav-item nav-inventory">Inventory</a>
        <a href="index.php?page=expenditures" class="nav-item nav-expenditures">Expenditure</a>
        <a href="index.php?page=reports" class="nav-item nav-reports">Reports</a>

        <?php if ($_SESSION['login_type'] == 1): ?>
            <!-- <a href="index.php?page=users" class="nav-item nav-users">Users</a> -->
        <?php endif; ?>

    </div>
</nav>

<style>
#sidebar {
    min-height: 100vh;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
    padding: 1.5rem 1rem;
}

.sidebar-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.nav-item {
    display: flex;
    align-items: center;
    color: #64748b !important;
    padding: 0.875rem 1.25rem;
    border-radius: 10px;
    background: transparent !important;
    border: none !important;
    margin-bottom: 4px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.nav-item:hover::before,
.nav-item.active::before {
    transform: scaleY(1);
}

.nav-item:hover {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%) !important;
    color: #6366f1 !important;
    text-decoration: none;
    transform: translateX(4px);
}

.nav-item.active {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    color: #fff !important;
    text-decoration: none;
    box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3);
    transform: translateX(4px);
}

.nav-item.active:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important;
    color: #fff !important;
}

.bg-dark {
    background: #fff !important;
}

.border-right {
    border-right: 1px solid #e2e8f0 !important;
}

/* Add icons to nav items */
.nav-item::after {
    content: '›';
    margin-left: auto;
    font-size: 1.25rem;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.nav-item:hover::after,
.nav-item.active::after {
    opacity: 1;
    transform: translateX(0);
}
</style>

<script>
    $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');
</script>

<?php if ($_SESSION['login_type'] == 2): ?>
    <style>
        .nav-sales, .nav-users {
            display: none !important;
        }
    </style>
<?php endif ?>
