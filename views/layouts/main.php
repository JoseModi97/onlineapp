<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\Breadcrumbs;
use app\widgets\Alert;
use app\assets\AppAsset;

AppAsset::register($this);
Yii::$app->name = 'Online Application';

$currentControllerId = Yii::$app->controller->id;
$currentActionId = Yii::$app->controller->action->id;
$currentRoute = $currentControllerId . '/' . $currentActionId;

// Helper function to check if a link or any of its children (for submenus) is active
function is_active_nav_item($linkRouteOrChildren, $currentRouteStr, $isParent = false, $allSubmenuRoutes = []) {
    if ($isParent) {
        foreach ($allSubmenuRoutes as $childRoute) {
            // Normalize childRoute: remove leading slash
            $normalizedChildRoute = ltrim($childRoute, '/');
            if ($normalizedChildRoute === $currentRouteStr) {
                return true; // Active if any child matches
            }
        }
        return false; // No child matches
    } else {
        // Normalize linkRouteOrChildren: remove leading slash
        $normalizedLinkRoute = ltrim($linkRouteOrChildren, '/');
        return $normalizedLinkRoute === $currentRouteStr;
    }
}
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php $this->head() ?>

    <style>
        body {
            overflow-x: hidden;
        }

        #sidebar {
            width: 280px;
            transition: all 0.3s ease;
            background-color: #212529;
            overflow-y: auto;
            scrollbar-width: thin; /* Make scrollbar thin */
            scrollbar-color: #888 #2c3e50; /* thumb color track color */
        }

        #sidebar.collapsed {
            margin-left: -280px;
        }

        /* Default styles for larger screens (sidebar visible) */
        @media (min-width: 992px) {
            #sidebar:not(.collapsed) {
                margin-left: 0;
            }
            #main-content:not(.expanded) {
                margin-left: 280px;
            }
            #top-navbar:not(.expanded) {
                margin-left: 280px;
            }
            /* Ensure the toggle button is visible */
            #sidebarToggleBtn {
                display: inline-block;
            }
        }

        /* Styles for smaller screens (sidebar hidden by default) */
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: -280px; /* Fallback/default for small screens */
            }
            /* If sidebar is manually toggled open on small screen */
            #sidebar:not(.collapsed) {
                margin-left: 0;
            }
            #main-content {
                margin-left: 0; /* Full width */
            }
            #top-navbar {
                margin-left: 0; /* Full width */
            }
            /* Ensure the toggle button is visible */
            #sidebarToggleBtn {
                display: inline-block;
            }
        }

        .sidebar-fixed {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }

        .sidebar-nav a {
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
            border-radius: 0.375rem;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background-color: #343a40;
        }

        #top-navbar {
            margin-left: 280px; /* Same as #main-content's original margin */
            transition: margin-left 0.3s ease; /* Same as #main-content's transition */
            position: sticky; /* Make it sticky to the top */
            top: 0;
            z-index: 1020; /* Standard z-index for navbars, above most content */
            border-bottom: 1px solid #dee2e6; /* Restored */
        }

        #top-navbar.expanded {
            margin-left: 0;
        }

        #main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            padding: 0 1.5rem 1.5rem 1.5rem; /* Top padding removed/set to 0, others remain */
            /* min-height: 100vh; Removed for sticky footer */
        }

        #main-content.expanded {
            margin-left: 0;
        }

        #setups-icon {
            transition: transform 0.2s ease-in-out;
        }

        #setups-icon.rotate-icon {
            transform: rotate(180deg);
        }

        /* For Webkit browsers (Chrome, Safari, Edge) */
        #sidebar::-webkit-scrollbar {
            width: 8px; /* Slim scrollbar */
        }

        #sidebar::-webkit-scrollbar-track {
            background: #2c3e50; /* Slightly lighter than sidebar bg or a neutral dark grey */
            border-radius: 4px;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #888; /* A medium grey for the thumb */
            border-radius: 4px;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background: #555; /* Darker grey on hover */
        }

        /* For Firefox */
        /* Properties moved to the main #sidebar rule */

        .page-content-wrapper {
            padding-top: 1.5rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-light"> <!-- Added d-flex, flex-column, min-vh-100 -->
    <?php $this->beginBody() ?>

    <div class="d-flex flex-grow-1"> <!-- Added flex-grow-1 to allow this div to expand -->

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-fixed p-3 text-white bg-dark">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="<?= \yii\helpers\Url::home() ?>" class="fw-bold text-white text-decoration-none"><?= Yii::$app->name ?></a>
            </div>

            <nav class="sidebar-nav d-flex flex-column">

                <!-- Home -->
                <a href="<?= \yii\helpers\Url::to(['/site/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/site/index', $currentRoute) ? 'active' : '' ?>">
                    <i class="bi bi-house me-2"></i> Home
                </a>

                <!-- Adapted navigation items from web/Uon/index.html -->
                <a href="<?= \yii\helpers\Url::to(['/dashboard/index']) // Assuming 'dashboard/index' for My Dashboard ?>" class="nav-link text-white <?= is_active_nav_item('/dashboard/index', $currentRoute) ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> My Dashboard
                </a>

                <a href="<?= Yii::getAlias('@web/documents/how_to_apply.pdf') // Assuming PDF is in web/documents ?>" class="nav-link text-white" target="_blank" title="Download a help guide on how to apply">
                    <i class="bi bi-file-earmark-arrow-down me-2"></i> Download Application tutorial
                </a>

                <a href="<?= \yii\helpers\Url::to(['/profile/update']) // Assuming 'profile/update' ?>" class="nav-link text-white <?= is_active_nav_item('/profile/update', $currentRoute) ? 'active' : '' ?>">
                    <i class="bi bi-person-badge me-2"></i> Update Your Profile
                </a>

                <a href="<?= \yii\helpers\Url::to(['/documents/upload']) // Assuming 'documents/upload' for additional documents ?>" class="nav-link text-white <?= is_active_nav_item('/documents/upload', $currentRoute) ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i> Upload Additional Documents
                </a>

                <a href="<?= \yii\helpers\Url::to(['/site/instructions']) // Assuming 'site/instructions' ?>" class="nav-link text-white <?= is_active_nav_item('/site/instructions', $currentRoute) ? 'active' : '' ?>">
                    <i class="bi bi-card-text me-2"></i> Application Instructions
                </a>

                <a href="<?= \yii\helpers\Url::to(['/application/create']) // Assuming 'application/create' or similar for applying ?>" class="nav-link text-white <?= is_active_nav_item('/application/create', $currentRoute) ? 'active' : '' ?>">
                    <i class="bi bi-journal-plus me-2"></i> Apply for Admission
                </a>
                <!-- End of adapted items -->

                <!-- Setups collapsible group -->
                <?php
                $setupsSubmenuLinks = [
                    'admission-status/index', 'applicant/index', 'applicant-contacts/index',
                    'applicant-education/index', 'applicant-work-exp/index', 'application/index',
                    'app-application080420160909/index', 'application-deleted/index', 'application-fees/index',
                    'application-intake/index', 'application-payments080420160909/index', 'application-tracking/index',
                    'application-without-ref/index', 'contact-types/index', 'notifications/index'
                ];
                $isSetupsActive = is_active_nav_item(null, $currentRoute, true, $setupsSubmenuLinks);
                ?>
                <a class="nav-link text-white <?= $isSetupsActive ? 'active' : '' ?> d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#setupsCollapse" role="button" aria-expanded="<?= $isSetupsActive ? 'true' : 'false' ?>" aria-controls="setupsCollapse">
                    <span><i class="bi bi-sliders me-2"></i> Setups</span>
                    <i class="bi bi-chevron-down" id="setups-icon"></i>
                </a>

                <div class="collapse ps-3 <?= $isSetupsActive ? 'show' : '' ?>" id="setupsCollapse">
                    <a href="<?= \yii\helpers\Url::to(['/admission-status/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/admission-status/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-check2-square me-2"></i> Admission Status</a>
                    <a href="<?= \yii\helpers\Url::to(['/applicant/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/applicant/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-person me-2"></i> Applicant</a>
                    <a href="<?= \yii\helpers\Url::to(['/applicant-contacts/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/applicant-contacts/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-telephone me-2"></i> Applicant Contacts</a>
                    <a href="<?= \yii\helpers\Url::to(['/applicant-education/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/applicant-education/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-mortarboard me-2"></i> Applicant Education</a>
                    <a href="<?= \yii\helpers\Url::to(['/applicant-work-exp/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/applicant-work-exp/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-briefcase me-2"></i> Work Experience</a>
                    <a href="<?= \yii\helpers\Url::to(['/application/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-journal-text me-2"></i> Application</a>
                    <a href="<?= \yii\helpers\Url::to(['/app-application080420160909/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/app-application080420160909/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-clock-history me-2"></i> App 080420160909</a>
                    <a href="<?= \yii\helpers\Url::to(['/application-deleted/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application-deleted/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-trash me-2"></i> Application Deleted</a>
                    <a href="<?= \yii\helpers\Url::to(['/application-fees/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application-fees/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-currency-dollar me-2"></i> Application Fees</a>
                    <a href="<?= \yii\helpers\Url::to(['/application-intake/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application-intake/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-calendar-check me-2"></i> Application Intake</a>
                    <a href="<?= \yii\helpers\Url::to(['/application-payments080420160909/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application-payments080420160909/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-credit-card me-2"></i> Application Payments</a>
                    <a href="<?= \yii\helpers\Url::to(['/application-tracking/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application-tracking/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-search me-2"></i> Application Tracking</a>
                    <a href="<?= \yii\helpers\Url::to(['/application-without-ref/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/application-without-ref/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-exclamation-triangle me-2"></i> Without Ref</a>
                    <a href="<?= \yii\helpers\Url::to(['/contact-types/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/contact-types/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-diagram-3 me-2"></i> Contact Types</a>
                    <a href="<?= \yii\helpers\Url::to(['/notifications/index']) ?>" class="nav-link text-white <?= is_active_nav_item('/notifications/index', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-bell me-2"></i> Notification</a>
                </div>

                <!-- Other Links -->
                <a href="<?= \yii\helpers\Url::to(['/site/about']) ?>" class="nav-link text-white <?= is_active_nav_item('/site/about', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-info-circle me-2"></i> About</a>
                <a href="<?= \yii\helpers\Url::to(['/site/contact']) ?>" class="nav-link text-white <?= is_active_nav_item('/site/contact', $currentRoute) ? 'active' : '' ?>"><i class="bi bi-envelope me-2"></i> Contact</a>

            </nav>
        </div>

        <!-- Main Content and Footer Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column flex-grow-1"> <!-- Added d-flex, flex-column, flex-grow-1 -->
            <!-- Top Navigation Bar -->
            <nav id="top-navbar" class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button id="sidebarToggleBtn" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-chevron-double-left"></i>
                    </button>
                    <h5 class="mb-0"><?= Html::encode($this->title ?? 'Dashboard') ?></h5>
<div class="ms-auto"> <!-- Wrapper to ensure right alignment for both states -->
    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="d-flex align-items-center"> <!-- Flex container for username and button -->
            <span class="navbar-text me-2"> <!-- me-2 for spacing -->
                <i class="bi bi-person-circle me-1"></i> <?= Html::encode(Yii::$app->user->identity->username) ?>
            </span>
            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex']) ?>
            <?= Html::submitButton(
                'Logout', // Simpler text, icon can be added if desired
                ['class' => 'btn btn-outline-secondary btn-sm'] // Using btn-sm for a less prominent button
            ) ?>
            <?= Html::endForm() ?>
        </div>
    <?php else: ?>
        <a href="<?= \yii\helpers\Url::to(['/site/login']) ?>" class="nav-link text-dark">
            <i class="bi bi-box-arrow-in-right me-2"></i> Login
        </a>
    <?php endif; ?>
</div>
                </div>
            </nav>

            <!-- Main Content -->
            <div id="main-content" class="flex-grow-1 bg-white"> <!-- flex-grow-1 remains -->
                <div class="page-content-wrapper"> <!-- New inner wrapper -->
                    <?php if (!empty($this->params['breadcrumbs'])): ?>
                        <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
                    <?php endif ?>

                    <?= Alert::widget() ?>
                    <?= $content ?>
                </div>
            </div> <!-- Closing main-content -->

            <!-- Footer -->
            <footer class="pt-5 mt-auto border-top text-muted small"> <!-- Added mt-auto -->
                <div class="row">
                    <div class="col-md-6">&copy; My Company <?= date('Y') ?></div>
                    <div class="col-md-6 text-end"><?= Yii::powered() ?></div>
                </div>
            </footer>
        </div> <!-- Closing content-wrapper -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const mainContent = document.getElementById('main-content');
        const topNavbar = document.getElementById('top-navbar');
        const toggleBtnIcon = toggleBtn.querySelector('i');

        function updateLayout(isResizing = false) {
            const isSmallScreen = window.innerWidth < 992;
            const icon = toggleBtnIcon; // Use cached element

            if (isSmallScreen) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                if (topNavbar) topNavbar.classList.add('expanded');
                icon.classList.remove('bi-chevron-double-left');
                icon.classList.add('bi-chevron-double-right');
            } else {
                // On large screens, respect manual toggle state unless it's an initial load (not resizing)
                // or if the sidebar is meant to be open by default.
                // The CSS now handles the default open state for large screens.
                // This JS ensures classes are correct if a manual toggle happened.
                if (!sidebar.classList.contains('collapsed')) {
                    // If sidebar is not collapsed (i.e., open)
                    sidebar.classList.remove('collapsed'); // Ensure it's not collapsed
                    mainContent.classList.remove('expanded');
                    if (topNavbar) topNavbar.classList.remove('expanded');
                    icon.classList.remove('bi-chevron-double-right');
                    icon.classList.add('bi-chevron-double-left');
                } else {
                    // Sidebar is collapsed (could be from small screen transition or manual toggle on large screen)
                    // Ensure other elements are expanded and icon is correct for collapsed state
                    mainContent.classList.add('expanded');
                    if (topNavbar) topNavbar.classList.add('expanded');
                    icon.classList.remove('bi-chevron-double-left');
                    icon.classList.add('bi-chevron-double-right');
                }
            }
        }

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (topNavbar) {
                topNavbar.classList.toggle('expanded');
            }

            const icon = toggleBtnIcon; // Use cached element
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('bi-chevron-double-left');
                icon.classList.add('bi-chevron-double-right');
            } else {
                icon.classList.remove('bi-chevron-double-right');
                icon.classList.add('bi-chevron-double-left');
            }
        });

        window.addEventListener('resize', () => updateLayout(true));

        // Initial layout setup on page load
        updateLayout(false);

        const setupsCollapseElement = document.getElementById('setupsCollapse');
        const setupsIcon = document.getElementById('setups-icon');

        if (setupsCollapseElement && setupsIcon) {
            setupsCollapseElement.addEventListener('show.bs.collapse', function () {
                setupsIcon.classList.add('rotate-icon');
            });

            setupsCollapseElement.addEventListener('hide.bs.collapse', function () {
                setupsIcon.classList.remove('rotate-icon');
            });

            // Initial state check in case the menu is open on page load
            if (setupsCollapseElement.classList.contains('show')) {
                setupsIcon.classList.add('rotate-icon');
            }
        }
    </script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>