<?php
App::import('Table', 'Model');


class TipoTelefone extends Table {
	
	public static $_table = 'tfone';

	public static $_qry = '
		SELECT
			{{fields}}
		FROM tfone
		WHERE tfone.RA = 1
			{{conditions}}
		{{group}}
		{{order}}
		{{limit}}
		{{offset}}
	';
	
	public static $_fields = array(
		'tfone.ctfone',
		'tfone.ntfone',
	);
	
	
	/* métodos de criação de edição */
	public static function save($data) {
		/* implementar */
	}
	
	public static function create($data) {
		/* implementar */
	}
	
	public static function edit($data) {
		/* implementar */
	}
	
	
	/* métodos de busca */
	public static function find(string $type = 'all', array $params = array()) {
		return parent::_find($type, $params);
	}
	
	public static function findById(int $id, string $type = 'first', array $params = array()) {
		$params['conditions'] = _isset($params['conditions'], '');
		$params['conditions'] .= " AND tfone.ctfone = $id";
		return static::_find($type, $params);
	}
}