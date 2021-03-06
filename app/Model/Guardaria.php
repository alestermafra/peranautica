<?php
App::import('Table', 'Model');

App::import('Produto', 'Model');
App::import('Plano', 'Model');
App::import('Equipamento', 'Model');
App::import('FormaPagamento', 'Model');

class Guardaria extends Table {
	
	public static $_table = 'eguardaria';

	public static $_qry = '
		SELECT
			{{fields}}
		FROM eguardaria
			LEFT JOIN eequipe ON (eequipe.cequipe = eguardaria.cequipe)
			LEFT JOIN eps ON (eps.cps = eequipe.cps)
			LEFT JOIN eplano ON (eplano.cplano = eguardaria.cplano)
			LEFT JOIN eprod ON (eprod.cprod = eguardaria.cprod)
			LEFT JOIN elinha ON (elinha.clinha = eprod.clinha)
			LEFT JOIN escat ON (escat.cscat = elinha.cscat)
			LEFT JOIN epgt ON (epgt.cpgt = eguardaria.cpgt)
			LEFT JOIN eppgt ON (eppgt.cppgt = eguardaria.cppgt)
		WHERE eguardaria.RA = 1
			{{conditions}}
		{{group}}
		{{order}}
		{{limit}}
		{{offset}}
	';
	
	public static $_fields = array(
		'eguardaria.cguardaria',
		'eequipe.cequipe',
		'eequipe.nome',
		'eequipe.flg_venda',
		'eequipe.valor_venda',
		'eprod.cprod',
		'eprod.nprod',
		'eps.cps',
		'eps.nps',
		'eplano.cplano',
		'eplano.nplano',
		'elinha.clinha',
		'elinha.nlinha',
		'escat.cscat',
		'escat.nscat',
		'epgt.cpgt',
		'epgt.npgt',
		'eppgt.cppgt',
		'eppgt.qtd_parcela',
		'eppgt.nppgt',
		'eguardaria.d_vencimento',
		'eguardaria.valor',
		'eguardaria.valor_extra',
		'eguardaria.descricao',
		'eguardaria.ativo',
	);
	
	
	/* métodos de criação de edição */
	public static function save($guardaria) {
		if(!isset($guardaria['cguardaria'])) {
			return static::create($guardaria);
		}
		return static::edit($guardaria);
	}
	
	public static function create($guardaria) {
		$connection = new Connection();
		
		$eguardaria = [
			'cequipe' => (int) _isset($guardaria['cequipe'], 0),
			'cplano' => (int) _isset($guardaria['cplano'], 0),
			'cprod' => (int) _isset($guardaria['cprod'], 0),
			'valor' => (float) _isset($guardaria['valor'], 0),
			'valor_extra' => (float) _isset($guardaria['valor_extra'], 0),
			'cpgt' => (int) _isset($guardaria['cpgt'], 0),
			'd_vencimento' => (int) _isset($guardaria['d_vencimento'], 1),
			'cppgt' => (int) _isset($guardaria['cppgt'], 0),
			'descricao' => (string) _isset($guardaria['descricao'], ''),
			'ativo' => (int) _isset($guardaria['ativo'], 1),
		];
		$cguardaria = $connection->insert('eguardaria', $eguardaria);
		
		return static::findById($cguardaria);
	}
	
	public static function edit($guardaria) {
		$cguardaria = $guardaria['cguardaria'];
		
		$connection = new Connection();
		
		$eguardaria = [
			'cequipe' => (int) _isset($guardaria['cequipe'], 0),
			'cplano' => (int) _isset($guardaria['cplano'], 0),
			'cprod' => (int) _isset($guardaria['cprod'], 0),
			'valor' => (float) _isset($guardaria['valor'], 0),
			'valor_extra' => (float) _isset($guardaria['valor_extra'], 0),
			'cpgt' => (int) _isset($guardaria['cpgt'], 0),
			'd_vencimento' => (int) _isset($guardaria['d_vencimento'], 1),
			'cppgt' => (int) _isset($guardaria['cppgt'], 0),
			'descricao' => (string) _isset($guardaria['descricao'], ''),
			'ativo' => (int) _isset($guardaria['ativo'], 1),
		];
		$query =	"UPDATE eguardaria SET
						cequipe = {$eguardaria['cequipe']},
						cplano = {$eguardaria['cplano']},
						cprod = {$eguardaria['cprod']},
						valor = {$eguardaria['valor']},
						valor_extra = {$eguardaria['valor_extra']},
						cpgt = {$eguardaria['cpgt']},
						d_vencimento = {$eguardaria['d_vencimento']},
						cppgt = {$eguardaria['cppgt']},
						descricao = '{$eguardaria['descricao']}',
						ativo = {$eguardaria['ativo']}
					WHERE cguardaria = $cguardaria";
		$connection->query($query);
		//$connection->update('eguardaria', $eguardaria, "eguardaria.cguardaria = $cguardaria");
		
		return $guardaria;
	}
	
	
	/* métodos de busca */
	public static function find(string $type = 'all', array $params = array()) {
		return parent::_find($type, $params);
	}
	
	public static function findById(int $id, string $type = 'first', array $params = array()) {
		$params['conditions'] = _isset($params['conditions'], '');
		$params['conditions'] .= " AND eguardaria.cguardaria = $id";
		return static::_find($type, $params);
	}

	public static function findByCps(int $cps, string $type = 'all', array $params = array()) {
		$params['conditions'] = _isset($params['conditions'], '');
		$params['conditions'] .= " AND eps.cps = $cps";
		return static::_find($type, $params);
	}
	
	public static function search($value, string $type = 'all', array $params = array()) {
		$value = trim($value);
		$value = preg_replace("/[^0-9a-zA-Z ]/i", "", $value);
		$params['conditions'] = _isset($params['conditions'], '');
		$params['conditions'] .= " AND (
			eps.cps LIKE '$value%'
			OR eps.nps LIKE '%$value%'
			OR eequipe.nome LIKE '%$value%'
			OR eprod.nprod LIKE '%$value%'
			OR eplano.nplano LIKE '%$value%'
			OR elinha.nlinha LIKE '%$value%'
			OR escat.nscat LIKE '%$value%'
		)";
		return static::find($type, $params);
	}
}