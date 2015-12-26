<?php
if (!defined('MEDIAWIKI')) {
    die('Wrong Entracne Point');
}

class FlowThread
{
    public static function onBeforePageDisplay(OutputPage & $out, Skin & $skin) {
        $title = $out->getTitle();
        // Disallow commenting on pages without article id
        if ($title->getArticleID() == 0) return;
        if ($title->isSpecialPage()) return;

        // These could be explicitly allowed in later version
        if (!$title->canTalk()) return;
        if ($title->isTalkPage()) return;
        //if ($title->isMainPage()) return;

        if (in_array($title->getNamespace() , array(
            NS_MEDIAWIKI,
            NS_TEMPLATE,
            NS_CATEGORY,
        ))) return;

        if($out->getUser()->isAllowed('commentadmin-restricted')) {
            $out->addJsConfigVars(array( 'commentadmin' => ''));
        }

        global $wgExtAvatar, $wgDefaultAvatar;
        if($wgExtAvatar) {
            $out->addJsConfigVars(array( 'wgExtAvatar' => true));    
        }
        $out->addJsConfigVars(array( 'wgDefaultAvatar' => $wgDefaultAvatar));
        $out->addModules('ext.flowthread');
    }

    public static function onLoadExtensionSchemaUpdates( $updater ) {
        $dir = __DIR__ . '/sql';

        $dbType = $updater->getDB()->getType();
        // For non-MySQL/MariaDB/SQLite DBMSes, use the appropriately named file
        if ( !in_array( $dbType, array( 'mysql', 'sqlite' ) ) ) {
            throw new Exception('Database type not currently supported');
        } else {
            $filename = 'mysql.sql';
        }

        $updater->addExtensionTable('FlowThread', "{$dir}/{$filename}");
        $updater->addExtensionTable('FlowThreadAttitude', "{$dir}/{$filename}");

        return true;
    }
}
