<?php
App::import('Table', 'Model');	

class Segmentacao extends Table {
	
	public static $_table = 'eseg';

	public static $_qry = '
		SELECT
			{{fields}}
		FROM eseg
		WHERE eseg.RA = 1
			{{conditions}}
		{{group}}
		{{order}}
		{{limit}}
		{{offset}}
	';
	
	public static $_fields = array(
		'eseg.cseg',
		'eseg.sseg',
		'eseg.nseg',
	);
	
	public static function find(string $type = 'all', array $params = array()) {
		return parent::_find($type, $params);
	}
	
	public static function findById(int $id, string $type = 'first', array $params = array()) {
		$params['conditions'] = _isset($params['conditions'], '');
		$params['conditions'] .= " AND eseg.cseg = $id";
		return static::_find($type, $params);
	}
}