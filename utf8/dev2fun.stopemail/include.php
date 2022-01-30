<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 */

namespace Dev2fun\StopEmail;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::registerAutoLoadClasses(
    'dev2fun.stopemail',
    [
        'Dev2fun\StopEmail\Base' => __FILE__,
    ]
);

use Bitrix\Main\Config\Option;

class Base
{
    public static $moduleId = 'dev2fun.stopemail';

    /**
     * @param string $name
     * @param mixed $default
     * @return string
     */
    public static function getOption($name, $default='')
    {
        return Option::get(self::$moduleId, $name, $default);
    }

    /**
     * @param array $arFields
     * @return bool|array
     */
    public static function BeforePostingSendMail($arFields)
    {
        if(self::getOption('enable', 'Y') !== 'Y') {
            self::initCustomMail();
            return $arFields;
        }
        return true;
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return \Bitrix\Main\Event
     */
    public static function OnBeforeMailSend($event)
    {
        if(self::getOption('enable', 'Y') === 'Y') {
            self::initCustomMail();
            if(!\defined('ONLY_EMAIL')) {
                \define('ONLY_EMAIL', 'Y');
            }
            $event->addResult(new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                [
//                    'TO' => ONLY_EMAIL,
                    'RESULT' => 'SUCCESS',
                ],
                self::$moduleId
            ));
            return $event;
        }

        return $event;
    }

    /**
     * @return false
     */
    public static function OnBeforeEventAdd()
    {
        return false;
    }

    /**
     * @return void
     */
    public static function initCustomMail()
    {
        if(!function_exists('custom_mail')) {
            function custom_mail($to, $subject, $message, $additional_headers = null, $additional_parameters = null) {
                return true;
            }
        }
    }

    /**
     * @return void
     */
    public static function ShowThanksNotice()
    {
        \CAdminNotify::Add([
            'MESSAGE' => \Bitrix\Main\Localization\Loc::getMessage(
                'D2F_STOPEMAIL_DONATE_MESSAGE',
                ['#URL#' => '/bitrix/admin/settings.php?lang=ru&mid=dev2fun.stopemail&mid_menu=1&tabControl_active_tab=donate']
            ),
            'TAG' => 'dev2fun_stopemail_update',
            'MODULE_ID' => self::$moduleId,
        ]);
    }
}