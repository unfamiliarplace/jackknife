<?php

/*
 * =============================================================================
 * Include all Jackknife files.
 * =============================================================================
 */

/*
 * =============================================================================
 * Core functionality
 * =============================================================================
 */

// Registry, state, and notices
require_once 'includes/core/JKNRegistry.php';
require_once 'includes/core/JKNNotices.php';

// API and options
require_once 'includes/core/api/JKNAPI.php';
require_once 'includes/core/api/JKNOpts.php';

// Space
require_once 'includes/core/space/JKNSpace.php';
require_once 'includes/core/space/JKNMenu.php';
require_once 'includes/core/space/JKNSubmenu.php';

// Module
require_once 'includes/core/module/JKNModule.php';
require_once 'includes/core/module/JKNMode.php';
require_once 'includes/core/module/JKNLifecycle.php';

// Dependency
require_once 'includes/core/dependency/JKNDependency.php';
require_once 'includes/core/dependency/JKNUnregisteredDependency.php';
require_once 'includes/core/dependency/JKNModuleDependency.php';
require_once 'includes/core/dependency/JKNPluginDependency.php';
require_once 'includes/core/dependency/JKNThemeDependency.php';

// Settings page
require_once 'includes/core/settings_page/JKNSettingsPage.php';
require_once 'includes/core/settings_page/JKNSettingsPageWP.php';
require_once 'includes/core/settings_page/JKNSettingsPageACF.php';
require_once 'includes/core/settings_page/JKNSettingsPageCPT.php';

// Space's modules page
require_once 'includes/core/space/modules_page/JKNModulesPage.php';
require_once 'includes/core/space/modules_page/JKNModulesPageSettings.php';


/*
 * =============================================================================
 * Tools
 * =============================================================================
 */

// Functions
require_once 'includes/tools/functions/JKNAjax.php';
require_once 'includes/tools/functions/JKNArrays.php';
require_once 'includes/tools/functions/JKNCDN.php';
require_once 'includes/tools/functions/JKNColours.php';
require_once 'includes/tools/functions/JKNClasses.php';
require_once 'includes/tools/functions/JKNCSS.php';
require_once 'includes/tools/functions/JKNDebugging.php';
require_once 'includes/tools/functions/JKNEditing.php';
require_once 'includes/tools/functions/JKNFilesystem.php';
require_once 'includes/tools/functions/JKNFormatting.php';
require_once 'includes/tools/functions/JKNJavascript.php';
require_once 'includes/tools/functions/JKNLayouts.php';
require_once 'includes/tools/functions/JKNMinifying.php';
require_once 'includes/tools/functions/JKNPosts.php';
require_once 'includes/tools/functions/JKNStrings.php';
require_once 'includes/tools/functions/JKNTaxonomies.php';
require_once 'includes/tools/functions/JKNTime.php';

// Solutions > acf
require_once 'includes/tools/solutions/acf/JKNACF.php';

// Solutions > cpt
require_once 'includes/tools/solutions/cpt/JKNCPT.php';

// Solutions > cache
require_once 'includes/tools/solutions/cache/JKNCacheParentDir.php';
require_once 'includes/tools/solutions/cache/JKNCacheRoot.php';
require_once 'includes/tools/solutions/cache/JKNCacheDir.php';
require_once 'includes/tools/solutions/cache/JKNCacheObject.php';

// Solutions > cron
require_once 'includes/tools/solutions/cron/JKNCron.php';
require_once 'includes/tools/solutions/cron/JKNCron_MultiHook.php';
require_once 'includes/tools/solutions/cron/JKNCron_MultiHook_Static.php';
require_once 'includes/tools/solutions/cron/JKNCron_OneHook.php';
require_once 'includes/tools/solutions/cron/JKNCron_OneHook_Static.php';

// Solutions > renderer
require_once 'includes/tools/solutions/renderer/JKNRenderer.php';
require_once 'includes/tools/solutions/renderer/JKNRendererSwitch.php';

// Solutions > schedule
require_once 'includes/tools/solutions/schedule/JKNSchedule.php';
require_once 'includes/tools/solutions/schedule/JKNScheduleSingle.php';
