<?php
/**
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2019
 */

namespace Models;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class PbxSettings extends ModelsBase
{

    /**
     * @Primary
     * @Column(type="string", nullable=true)
     */
    public $key;

    /**
     * @Column(type="string", nullable=true)
     */
    public $value;

    public function getSource(): string
    {
        return 'm_PbxSettings';
    }

    public function validation(): bool
    {

        $validation = new Validation();
        $validation->add('key', new UniquenessValidator([
            'message' => $this->t('mo_ThisKeyMustBeUniqueForPbxSettingsModels'),
        ]));

        return $this->validate($validation);
    }

    /**
     * Значения по умолчанию для переменных станции
     *
     * @return array - значения по умолчанию
     */
    public static function getDefaultArrayValues(): array
    {
        return [
            'Version'                         => '1',
            'Name'                            => 'PBX system',
            'VirtualHardwareType'             => 'REAL',//VMWARE,HYPERV,AWS,AZURE
            'Description'                     => '',
            'RestartEveryNight'               => '0',
            'SIPPort'                         => '5060',
            'SIPDefaultExpiry'                => '120',
            'SIPMinExpiry'                    => '60',
            'SIPMaxExpiry'                    => '3600',
            'RTPPortFrom'                     => '10000',
            'RTPPortTo'                       => '10200',
            'IAXPort'                         => '4569',
            'AMIEnabled'                      => '1',
            'AMIPort'                         => '5038',
            'AJAMEnabled'                     => '1',
            'AJAMPort'                        => '8088',
            'AJAMPortTLS'                     => '8089',
            'SSHPort'                         => '22',
            'SSHPassword'                     => 'admin',
            'SSHRsaKey'                       => '',
            'SSHDssKey'                       => '',
            'SSHAuthorizedKeys'               => '',
            'SSHecdsaKey'                     => '',
            'WEBPort'                         => '80',
            'WEBHTTPSPort'                    => '443',
            'WEBHTTPSPublicKey'               => '',
            'WEBHTTPSPrivateKey'              => '',
            'MailSMTPUseTLS'                  => '0',
            'MailSMTPCertCheck'               => '0',
            'MailSMTPHost'                    => '',
            'MailSMTPPort'                    => '25',
            'MailSMTPUsername'                => '',
            'MailSMTPPassword'                => '',
            'MailSMTPFromUsername'            => 'PBX',
            'MailSMTPSenderAddress'           => '',
            'MailEnableNotifications'         => '0',
            'MailTplMissedCallSubject'        => 'You have missing call from <MailSMTPSenderAddress>',
            'MailTplMissedCallBody'           => 'You have missed calls (NOTIFICATION_MISSEDCAUSE) from <NOTIFICATION_CALLERID> at <NOTIFICATION_DATE>',
            'MailTplMissedCallFooter'         => '',
            'MailTplVoicemailSubject'         => 'VoiceMail from PBX',
            'MailTplVoicemailBody'            => 'See attach',
            'VoicemailNotificationsEmail'     => 'admin@mycompany.com',
            'PBXLanguage'                     => 'ru-ru',
            'PBXInternalExtensionLength'      => '3',// Длина внутреннего номера
            'PBXRecordCalls'                  => '1',
            'PBXCallParkingExt'               => '800',
            'PBXCallParkingStartSlot'         => '801',
            'PBXCallParkingEndSlot'           => '820',
            'PBXFeatureAttendedTransfer'      => '##',
            'PBXFeatureBlindTransfer'         => '**',
            'PBXFeatureDigitTimeout'          => '2500',
            'PBXFeatureAtxferNoAnswerTimeout' => '45',
            'PBXFirewallEnabled'              => '0',
            'PBXFail2BanEnabled'              => '0',
            'PBXTimezone'                     => 'Europe/Moscow',
            'WebAdminLogin'                   => 'admin',
            'WebAdminPassword'                => 'admin',
            'SystemNotificationsEmail'        => 'admin@mycompany.com',
            'SendMetrics'                     => '1',

        ];
    }

    /**
     * Значение по умолчанию для переменных станции
     *
     * @param $parameters string - ключ значения
     *
     * @return string - результат из базы или значение по умолчанию
     */
    public static function getValueByKey($parameters = null): string
    {
        $result = parent::findFirstByKey($parameters);
        if ( ! $result || $result->value === null) {
            $arrOfDefaultValues = PbxSettings::getDefaultArrayValues();
            if ( ! array_key_exists($parameters, $arrOfDefaultValues)) {
                return '';
            } else {
                return $arrOfDefaultValues[$parameters];
            }
        }

        return $result->value;
    }

    /**
     * Значениея по умолчанию для переменных станции
     *
     * @return array - результат из базы или значение по умолчанию
     */
    public static function getAllPbxSettings(): array
    {
        $arrOfDefaultValues = PbxSettings::getDefaultArrayValues();
        foreach ($arrOfDefaultValues as $key => $record) {
            $arrOfDefaultValues[$key] = PbxSettings::getValueByKey($key);
        }

        return $arrOfDefaultValues;
    }

    /**
     * Проверяем на наличие параметров сетевых портов и firewall
     *
     * @return bool
     */
    public function itHasFirewallParametersChanges(): bool
    {
        switch ($this->key) {
            case 'SIPPort':
            case 'RTPPortFrom':
            case 'RTPPortTo':
            case 'IAXPort':
            case 'AMIPort':
            case 'AJAMPort':
            case 'AJAMPortTLS':
            case 'WEBPort':
            case 'WEBHTTPSPort':
            case 'SSHPort':
            case 'PBXFirewallEnabled':
            case 'PBXFail2BanEnabled':
                return true;
                break;
            default:
                if (strpos($this->key, 'FirewallSettings') !== false) {
                    return true;
                }
        }

        return false;
    }

    /**
     * Проверяем на наличие параметров SIP
     *
     * @return bool
     */
    public function itHasSipParametersChanges(): bool
    {
        switch ($this->key) {
            case 'SIPPort':
            case 'RTPPortFrom':
            case 'RTPPortTo':
            case 'SIPDefaultExpiry':
            case 'SIPMinExpiry':
            case 'SIPMaxExpiry':
            case 'PBXLanguage':
                return true;
                break;
            default:
                return false;
        }
    }

    /**
     * Проверяем на наличие параметров IAX портов
     *
     * @return bool
     */
    public function itHasIaxParametersChanges(): bool
    {
        switch ($this->key) {
            case 'IAXPort':
                return true;
                break;
            default:
                return false;

        }
    }

    /**
     * Проверяем на наличие параметров AMI AJAM портов
     *
     * @return bool
     */
    public function itHasAMIParametersChanges(): bool
    {
        switch ($this->key) {
            case 'AMIPort':
            case 'AJAMPort':
            case 'AJAMPortTLS':
                return true;
                break;
            default:
                return false;

        }
    }

    /**
     * Проверяем на наличие параметров изменения настроек features
     *
     * @return bool
     */
    public function itHasFeaturesSettingsChanges(): bool
    {
        switch ($this->key) {
            case 'PBXLanguage':
            case 'PBXInternalExtensionLength':
            case 'PBXRecordCalls':
            case 'PBXCallParkingExt':
            case 'PBXCallParkingStartSlot':
            case 'PBXCallParkingEndSlot':
            case 'PBXFeatureAttendedTransfer':
            case 'PBXFeatureBlindTransfer':
            case 'PBXFeatureDigitTimeout':
            case 'PBXFeatureAtxferNoAnswerTimeout':
                return true;
                break;
            default:
                return false;

        }
    }

    /**
     * Проверяем на наличие параметров SSH
     *
     * @return bool
     */
    public function itHasSSHParametersChanges(): bool
    {
        switch ($this->key) {
            case 'SSHPort':
            case 'SSHPassword':
            case 'SSHAuthorizedKeys':
            case 'SSHRsaKey':
            case 'SSHDssKey':
            case 'SSHecdsaKey':
                return true;
                break;
            default:
                return false;

        }
    }

    /**
     * Проверяем на наличие параметров Web
     *
     * @return bool
     */
    public function itHasWebParametersChanges(): bool
    {
        switch ($this->key) {
            case 'WEBPort':
            case 'WEBHTTPSPort':
            case 'WEBHTTPSPublicKey':
            case 'WEBHTTPSPrivateKey':
                return true;
                break;
            default:
                return false;

        }
    }

    /**
     * Проверяем на наличие параметров требующих перезапуска cron
     *
     * @return bool
     */
    public function itHasCronParametersChanges(): bool
    {
        switch ($this->key) {
            case 'RestartEveryNight':
                return true;
                break;
            default:
                return false;

        }
    }

    /**
     * Проверяем на наличие параметров требующих перезапуска dialplan
     *
     * @return bool
     */
    public function itHasDialplanParametersChanges(): bool
    {
        switch ($this->key) {
            case 'PBXLanguage':
                return true;
                break;
            default:
                return false;

        }
    }


}