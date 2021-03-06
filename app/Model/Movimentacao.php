<?php
App::import('Table', 'Model');

class Movimentacao extends Table {
	public static $_table = 'emov';

	public static $_qry = '
		SELECT
			{{fields}}
		FROM emov
		WHERE emov.RA = 1
			{{conditions}}
		{{group}}
		{{order}}
		{{limit}}
		{{offset}}
	';
	
	public static $_fields = array(
		'emov.cmov',
		'emov.nmov',
		'emov.OBS'
	);

	
	/* métodos de busca */
	public static function find(string $type = 'all', array $params = array()) {
		return parent::_find($type, $params);
	}
	
	public static function findById(int $id, string $type = 'first', array $params = array()) {
		$params['conditions'] = _isset($params['conditions'], '');
		$params['conditions'] .= " AND emov.cmov = $id";
		return static::_find($type, $params);
	}
	
}