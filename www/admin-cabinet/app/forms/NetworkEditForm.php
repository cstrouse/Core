<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 5 2018
 *
 */

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Hidden;



class NetworkEditForm extends Form
{
    public function initialize($entity = null, $options = null)
    {
	    $arrRealInterfaces = [];

        $this->add(new Text('hostname'));
        $this->add(new Text('gateway',array('class'=>'ipaddress')));
        $this->add(new Text('primarydns',array('class'=>'ipaddress')));
        $this->add(new Text('secondarydns',array('class'=>'ipaddress')));
        $this->add(new Text('extipaddr',array('class'=>'ipaddress')));
	    $this->add(new Text('exthostname'));

        $cheskArr=array('value'=>null);
        if ($entity->topology=='private') {
            $cheskArr = array('checked' => 'checked','value'=>null);
        }
        $this->add(new Check('usenat',$cheskArr));

        foreach ($options['eths'] as $eth) {
            $this->add(new Hidden('interface_'.$eth->id, array(
                'value' => $eth->interface
            )));

            $this->add(new Text('name_'.$eth->id, array(
                'value' => $eth->name
            )));

            // DHCP
            $cheskarr=array('value'=>null);
            if ($eth->dhcp) {
                $cheskarr = array('checked' => 'checked','value'=>null);
            }

            $this->add(new Check('dhcp_'.$eth->id,$cheskarr));

            $this->add(new Text('ipaddr_'.$eth->id, array(
                'value' => $eth->ipaddr,
                'class'=>'ipaddress'
            )));

            // Выбор подсети интерфейса
            $arrMasks = array(
                "0"=>"0 - 0.0.0.0",
                "1"=>"1 - 128.0.0.0",
                "2"=>"2 - 192.0.0.0",
                "3"=>"3 - 224.0.0.0",
                "4"=>"4 - 240.0.0.0",
                "5"=>"5 - 248.0.0.0",
                "6"=>"6 - 252.0.0.0",
                "7"=>"7 - 254.0.0.0",
                "8"=>"8 - 255.0.0.0",
                "9"=>"9 - 255.128.0.0",
                "10"=>"10 - 255.192.0.0",
                "11"=>"11 - 255.224.0.0",
                "12"=>"12 - 255.240.0.0",
                "13"=>"13 - 255.248.0.0",
                "14"=>"14 - 255.252.0.0",
                "15"=>"15 - 255.254.0.0",
                "16"=>"16 - 255.255.0.0",
                "17"=>"17 - 255.255.128.0",
                "18"=>"18 - 255.255.192.0",
                "19"=>"19 - 255.255.224.0",
                "20"=>"20 - 255.255.240.0",
                "21"=>"21 - 255.255.248.0",
                "22"=>"22 - 255.255.252.0",
                "23"=>"23 - 255.255.254.0",
                "24"=>"24 - 255.255.255.0",
                "25"=>"25 - 255.255.255.128",
                "26"=>"26 - 255.255.255.192",
                "27"=>"27 - 255.255.255.224",
                "28"=>"28 - 255.255.255.240",
                "29"=>"29 - 255.255.255.248",
                "30"=>"30 - 255.255.255.252",
                "31"=>"31 - 255.255.255.254",
                "32"=>"32 - 255.255.255.255"
            );
            $mask = new Select('subnet_'.$eth->id, $arrMasks, array(
                'using' => array(
                    'id',
                    'name'
                ),
                'useEmpty' => false,
                'value' => $eth->subnet,
                'class' => 'ui selection dropdown ipaddress'
            ));
            $this->add($mask);

            $this->add(new Numeric('vlanid_'.$eth->id, array(
                'value' => $eth->vlanid
            )));

            $arrInterfaces[$eth->id]= $eth->name.' ('.$eth->interface.(($eth->vlanid>0)? '.'.$eth->vlanid:'').')';
	        if ( ! in_array( $eth->interface, $arrRealInterfaces ) ) {
		        $arrRealInterfaces[ $eth->id ] = $eth->interface;
	        }

        }

        unset($arrInterfaces['new']);
	    unset( $arrRealInterfaces['new'] );
        // Выбор интернет интерфейса
        $internetInterface = new Select('internet_interface', $arrInterfaces, array(
            'using' => array(
                'id',
                'name'
            ),
            'useEmpty' => false,
            'value' => $entity->id,
            'class' => 'ui selection dropdown'
        ));
        $this->add($internetInterface);


        // Поля для вновь добавляемого интерфейса
	    $newInterface = new Select( 'interface_new', $arrRealInterfaces, array(
            'using' => array(
                'id',
                'name'
            ),
            'useEmpty' => true,
            'class' => 'ui selection dropdown'
        ));
        $this->add($newInterface);

    }
}