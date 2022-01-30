<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 */
if (class_exists("dev2fun_stopemail")) return;

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\ModuleManager,
    Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option;

Loader::registerAutoLoadClasses(
    'dev2fun.stopemail',
    [
        'Dev2fun\\StopEmail\\Base' => 'include.php',
    ]
);

class dev2fun_stopemail extends CModule
{
    public $MODULE_ID = 'dev2fun.stopemail';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        include(__DIR__ . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('D2F_MODULE_NAME_STOPEMAIL');
        $this->MODULE_DESCRIPTION = Loc::getMessage('D2F_MODULE_DESCRIPTION_STOPEMAIL');
        $this->PARTNER_NAME = 'dev2fun';
        $this->PARTNER_URI = 'https://dev2fun.com';
    }

    public function DoInstall()
    {
        global $APPLICATION;
        if (!check_bitrix_sessid()) {
            return false;
        }
        try {
            $this->installDB();
            $this->registerEvents();
            ModuleManager::registerModule($this->MODULE_ID);
            \CAdminNotify::Add([
                'MESSAGE' => Loc::getMessage('D2F_STOPEMAIL_NOTICE_THANKS'),
                'TAG' => $this->MODULE_ID . '_install',
                'MODULE_ID' => $this->MODULE_ID,
            ]);
        } catch (Exception $e) {
            $GLOBALS['D2F_STOPEMAIL_ERROR'] = $e->getMessage();
            $GLOBALS['D2F_STOPEMAIL_ERROR_NOTES'] = Loc::getMessage('D2F_STOPEMAIL_INSTALL_ERROR_NOTES');
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('D2F_STOPEMAIL_STEP_ERROR'),
                __DIR__ . '/error.php'
            );
            return false;
        }
        $APPLICATION->IncludeAdminFile(Loc::getMessage('D2F_STOPEMAIL_STEP1'), __DIR__ . '/step1.php');
    }

    public function installDB()
    {
        Option::set($this->MODULE_ID, 'enable', 'N');
        return true;
    }

    public function registerEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler(
            'subscribe',
            'BeforePostingSendMail',
            $this->MODULE_ID,
            'Dev2fun\\StopEmail\\Base',
            'BeforePostingSendMail'
        );
        $eventManager->registerEventHandler(
            'main',
            'OnBeforeMailSend',
            $this->MODULE_ID,
            'Dev2fun\\StopEmail\\Base',
            'OnBeforeMailSend'
        );
        $eventManager->registerEventHandler(
            'main',
            'OnBeforeEventAdd',
            $this->MODULE_ID,
            'Dev2fun\\StopEmail\\Base',
            'OnBeforeEventAdd'
        );
        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        if (!check_bitrix_sessid()) {
            return false;
        }
        try {
            $this->unInstallDB();
            $this->unRegisterEvents();
            \CAdminNotify::Add([
                'MESSAGE' => Loc::getMessage('D2F_STOPEMAIL_NOTICE_WHY'),
                'TAG' => $this->MODULE_ID . '_uninstall',
                'MODULE_ID' => $this->MODULE_ID,
            ]);
            ModuleManager::unRegisterModule($this->MODULE_ID);
        } catch (Exception $e) {
            $GLOBALS['D2F_STOPEMAIL_ERROR'] = $e->getMessage();
            $GLOBALS['D2F_STOPEMAIL_ERROR_NOTES'] = Loc::getMessage('D2F_STOPEMAIL_UNINSTALL_ERROR_NOTES');
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('D2F_STOPEMAIL_STEP_ERROR'),
                __DIR__ . '/error.php'
            );
            return false;
        }

        $APPLICATION->IncludeAdminFile(GetMessage('D2F_STOPEMAIL_UNSTEP1'), __DIR__ . '/unstep1.php');
    }

    public function unInstallDB()
    {
        Option::delete($this->MODULE_ID);
        return true;
    }

    public function unRegisterEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'subscribe',
            'BeforePostingSendMail',
            $this->MODULE_ID
        );
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBeforeMailSend',
            $this->MODULE_ID
        );
        $eventManager->unRegisterEventHandler(
            'main',
            'OnBeforeEventAdd',
            $this->MODULE_ID
        );
        return true;
    }
}
