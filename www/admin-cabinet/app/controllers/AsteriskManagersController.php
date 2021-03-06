<?php
/**
 * Copyright (C) MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Nikolay Beketov, 5 2018
 *
 */

use Models\AsteriskManagerUsers,
	Models\NetworkFilters;
use Phalcon\Mvc\Model\Resultset;

class AsteriskManagersController extends BaseController {

	private $arrCheckBoxes;

	/**
	 * Инициализация базового класса
	 */
	public function initialize() :void{
		$this->arrCheckBoxes = [
			'call',
			'cdr',
			'originate',
			'reporting',
			'agent',
			'config',
			'dialplan',
			'dtmf',
			'log',
			'system',
			'user',
			'verbose',
		];
		parent::initialize();
	}

	/**
	 * Построение списка пользователей Asterisk Managers
	 */
	public function indexAction() {
		$amiUsers = AsteriskManagerUsers::find();
		$amiUsers->setHydrateMode(
			Resultset::HYDRATE_ARRAYS
		);

		$arrNetworkFilters = [];
		$networkFilters    = NetworkFilters::find();
		foreach ( $networkFilters as $filter ) {
			$arrNetworkFilters[ $filter->id ] = $filter->getRepresent();
		}
		$this->view->networkFilters = $arrNetworkFilters;
		$this->view->amiUsers       = $amiUsers;
	}


	/**
	 * Форма настроек пользователя Asterisk Managers
	 *
	 * @param string $id идентификатор редактируемого пользователя
	 */
	public function modifyAction( $id = NULL ) {

		$manager = AsteriskManagerUsers::findFirstById( $id );
		if ( ! $manager ) {
			$manager = new AsteriskManagerUsers();
		}

		$arrNetworkFilters = [];
		$networkFilters    = NetworkFilters::getAllowedFiltersForType( [
			'AJAM',
			'AMI',
		] );
		$arrNetworkFilters['none']
		                   = $this->translation->_( 'ex_NoNetworkFilter' );
		foreach ( $networkFilters as $filter ) {
			$arrNetworkFilters[ $filter->id ] = $filter->getRepresent();
		}


		$this->view->form = new AsteriskManagerEditForm( $manager,
			[
				'network_filters'     => $arrNetworkFilters,
				'array_of_checkboxes' => $this->arrCheckBoxes,
			] );

		$this->view->arrCheckBoxes = $this->arrCheckBoxes;
		$this->view->represent     = $manager->getRepresent();
	}


	/**
	 * Сохраенение настроек Asterisk Manager
	 */
	public function saveAction() {
		if ( ! $this->request->isPost()) return;

		$data    = $this->request->getPost();
		$manager = false;
		if (isset($data['id'])){
			$manager = AsteriskManagerUsers::findFirst( $data['id'] );
		}
		if (!$manager) {
			$manager = new AsteriskManagerUsers();
		}

		foreach ( $manager as $name => $value ) {
			if ( in_array( $name, $this->arrCheckBoxes ) ) {
				$manager->$name = '';
				$manager->$name .= ( $data[ $name . '_read' ] == 'on' ) ? 'read'
					: '';
				$manager->$name .= ( $data[ $name . '_write' ] == 'on' )
					? 'write' : '';
				continue;
			}

			if ( ! array_key_exists( $name, $data ) ) {
				continue;
			}
			$manager->$name = $data[ $name ];

		}
		$errors = FALSE;
		if ( ! $manager->save() ) {
			$errors = $manager->getMessages();
		}

		if ( $errors ) {
			$this->flash->warning( implode( '<br>', $errors ) );
			$this->view->success = false;
		} else {
			$this->flash->success( $this->translation->_( 'ms_SuccessfulSaved' ) );
			$this->view->success = TRUE;
			$this->view->reload = "asterisk-managers/modify/{$manager->id}";
		}
	}

	/**
	 * Удаление Asterisk Manager
	 */
	public function deleteAction( $amiId = NULL ) {

		$manager = AsteriskManagerUsers::findFirstByid( $amiId );
		if ( $manager ) {
			$manager->delete();
		}

		return $this->forward( 'asterisk-managers/index' );

	}
}