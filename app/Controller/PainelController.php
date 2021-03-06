<?php
App::import('AppController', 'Controller');

App::import('Auth', 'Model');

App::import("Pessoa", "Model");
App::import('ClientePF', 'Model');
App::import('ClientePJ', 'Model');

App::import('Calendario', 'Model');
App::import('Canal', 'Model');
App::import('CanalContato', 'Model');
App::import('ClienteCanal', 'Model');
App::import('Interesse', 'Model');
App::import('ClienteInteresse', 'Model');
App::import('Segmentacao', 'Model');
App::import('Classificacao', 'Model');
App::import('TipoTelefone', 'Model');
App::import('ParticipanteAula', 'Model');
App::import('Equipamento', 'Model');

App::import('Endereco', 'Model');
App::import('Contato', 'Model');
App::import('ContaContato', 'Model');

App::import('Ocorrencia', 'Model');

App::import("Attachment", "Model");

App::import("InteresseRepository", "Model");
App::import("Guardaria", "Model");

class PainelController extends AppController {
	
	public function pf() {
		$search_value = _isset($_GET['search_value'], null);
		$order = _isset($_GET['order'], 'nome');
		$order_values = array(
			'default' => 'eps.nps',
			'nome' => 'eps.nps',
			'cseg2' => 'eseg2.classificacao',
			'segmentacao' => 'eseg.nseg',
			'data' => 'eps.ts',
		);
		
		$conditions = " ";

		$filtros = $_GET['filtros'] ?? '';

		if($filtros === "com-cpf") {
			$conditions = $conditions . " AND LENGTH(upsf.cpf) > 1 ";
		}
		else if($filtros === "sem-cpf") {
			$conditions = $conditions . " AND LENGTH(upsf.cpf) = 0 ";
		}
		else if($filtros === 'com-prim-contato') {
			$conditions = $conditions . " AND upsf.d_contato > 0 and upsf.m_contato > 0 and upsf.a_contato > 0 ";
		}
		else if($filtros === 'sem-prim-contato') {
			$conditions = $conditions . " AND (upsf.d_contato = 0 or upsf.m_contato = 0 or upsf.a_contato = 0) ";
		}
		
		$ativo = _isset($_GET['ativo'], 1);
		
		$page = (int) _isset($_GET['page'], 1);
		$limit = (int) _isset($_GET['limit'], 20);
		
		if($search_value) {
			$list = ClientePF::search($search_value, 'all', array('page' => $page, 'limit' => $limit, 'conditions' => ' AND zpainel.ativo = ' . $ativo . $conditions, 'order' => _isset($order_values[$order], $order_values['default'])));
			$count = ClientePF::search($search_value, 'count', array('conditions' => ' AND zpainel.ativo = '.$ativo . $conditions));
		}
		else {
			$list = ClientePF::find('all', array('page' => $page, 'limit' => $limit, 'conditions' => ' AND zpainel.ativo = ' . $ativo . $conditions, 'order' => _isset($order_values[$order], $order_values['default'])));
			$count = ClientePF::find('count', array('conditions' => ' AND zpainel.ativo = '.$ativo . $conditions));
		}
		
		$this->view->set('list', $list);
		$this->view->set('count', $count);
		$this->view->set('page', $page);
		$this->view->set('pages', (int) ceil($count / $limit));
	}
	
	public function overview_pf(int $cps = null) {
		if(!$cps || !$clientepf = ClientePF::findByCps($cps)) {
			return $this->redirect('/painel/pf');
		}

		$clientepf['telefones'] = ClientePF::telefones($clientepf['cps']);
		$clientepf['enderecos'] = ClientePF::enderecos($clientepf['cps']);
		$clientepf['canais'] = ClientePF::canais($cps);
		$clientepf['canais_contato'] = ClientePF::canais_contato($cps);
		$clientepf['interesses'] = ClientePF::interesses($cps);
		$clientepf['aulas'] = ParticipanteAula::findByCps($cps, 'all', array('order' => ' eaula.can ASC, eaula.cmes ASC, eaula.cdia ASC, eaula.subtitulo ASC '));
		$clientepf['equipamentos'] = Equipamento::findByCps($cps);
		$clientepf['attachments'] = Attachment::get_attachments(WEBROOT . DS . "attachments" . DS . "painel" . DS . "pf" . DS . $cps);
		
		$this->view->set('clientepf', $clientepf);
		$this->view->set('guarderias', Guardaria::findByCps($cps));
		$this->view->set('ocorrencia', Ocorrencia::findByCodigoPessoa($clientepf['cps'], 'all', array('order' => 'eocorrencia.data DESC')));
	}
	
	public function inserir_pf() {
		if($this->request->method === 'POST') {
			$data = $_POST;
			try {
				$clientepf = ClientePF::save($data);
				return $this->redirect('/painel/overview_pf/' . $clientepf['cps']);
			}
			catch(Exception $e) {
				$this->view->set('error', $e->getMessage());
			}
		}
		
		$this->view->set('interesses', Interesse::find());
		$this->view->set('canais', Canal::find());
		$this->view->set('canais_contato', CanalContato::find());
		$this->view->set('segmentacoes', Segmentacao::find('all', array('order' => 'eseg.ordem')));
		$this->view->set('classificacoes', Classificacao::find('all', array('order' => 'eseg2.ordem')));
		$this->view->set('tipos_telefone', TipoTelefone::find());
		$this->view->set('tipos_endereco', Endereco::tipoEndereco());
	}
	
	public function editar_pf(int $cps = null){
		if(!$cps || !$clientepf = ClientePF::findByCps($cps)) {
			$this->redirect('/painel/pf');
		}
		
		if($this->request->method === 'POST') {
			$data = $_POST;
			$clientepf['nps'] = _isset($data['nps'], $clientepf['nps']);
			$clientepf['d_nasc'] = _isset($data['d_nasc'], $clientepf['d_nasc']);
			$clientepf['m_nasc'] = _isset($data['m_nasc'], $clientepf['m_nasc']);
			$clientepf['a_nasc'] = _isset($data['a_nasc'], $clientepf['a_nasc']);
			$clientepf['rg'] = _isset($data['rg'], $clientepf['rg']);
			$clientepf['cpf'] = _isset($data['cpf'], $clientepf['cpf']);
			$clientepf['email'] = _isset($data['email'], $clientepf['email']);
			$clientepf['profissao'] = _isset($data['profissao'], $clientepf['profissao']);
			$clientepf['equipe'] = _isset($data['equipe'], $clientepf['equipe']);
			$clientepf['peso'] = _isset($data['peso'], $clientepf['peso']);
			$clientepf['dependente1'] = _isset($data['dependente1'], $clientepf['dependente1']);
			$clientepf['dependente2'] = _isset($data['dependente2'], $clientepf['dependente2']);
			$clientepf['dependente3'] = _isset($data['dependente3'], $clientepf['dependente3']);
			$clientepf['dependente4'] = _isset($data['dependente4'], $clientepf['dependente4']);
			$clientepf['dependente5'] = _isset($data['dependente5'], $clientepf['dependente5']);
			$clientepf['d_contato'] = _isset($data['d_contato'], $clientepf['d_contato']);
			$clientepf['m_contato'] = _isset($data['m_contato'], $clientepf['m_contato']);
			$clientepf['a_contato'] = _isset($data['a_contato'], $clientepf['a_contato']);
			$clientepf['telefones'] = _isset($data['telefones'], array());
			$clientepf['cseg'] = _isset($data['cseg'], $clientepf['cseg']);
			$clientepf['cseg2'] = _isset($data['cseg2'], $clientepf['id']);
			$clientepf['canais'] = _isset($data['canais'], array());
			$clientepf['canais_contato'] = _isset($data['canais_contato'], array());
			$clientepf['interesses'] = _isset($data['interesses'], array());
			$clientepf['ativo'] = _isset($data['ativo'], $clientepf['ativo']);
			$clientepf['observacao'] = _isset($data['observacao'], $clientepf['observacao']);
			try {
				ClientePF::save($clientepf);
				return $this->redirect('/painel/overview_pf/' . $clientepf['cps']);
			}
			catch(Exception $e) {
				$this->view->set('error', $e->getMessage());
			}
		}
		
		$clientepf['telefones'] = ClientePF::telefones($clientepf['cps']);
		$clientepf['enderecos'] = ClientePF::enderecos($clientepf['cps']);
		$clientepf['canais'] = ClientePF::canais($clientepf['cps']);
		$clientepf['canais_contato'] = ClientePF::canais_contato($clientepf['cps']);
		$clientepf['interesses'] = ClientePF::interesses($clientepf['cps']);
				
		$this->view->set('clientepf', $clientepf);
		$this->view->set('segmentacoes', Segmentacao::find('all', array('order' => 'eseg.ordem')));
		$this->view->set('classificacoes', Classificacao::find('all', array('order' => 'eseg2.ordem')));
		$this->view->set('interesses', Interesse::find());
		$this->view->set('canais', Canal::find());
		$this->view->set('canais_contato', CanalContato::find());
		$this->view->set('tipos_telefone', TipoTelefone::find());
		$this->view->set('tipos_endereco', Endereco::tipoEndereco());
	}
	
	public function pj() {
		$search_value = _isset($_GET['search_value'], null);
		$order = _isset($_GET['order'], 'nome');
		$order_values = array(
			'default' => 'eps.nps',
			'nome' => 'eps.nps',
			'segmentacao' => 'eseg.nseg',
			'data' => 'eps.ts',
		);
		
		$ativo = (int) _isset($_GET['ativo'], 1);
		$page = (int) _isset($_GET['page'], 1);
		$limit = (int) _isset($_GET['limit'], 20);
		
		if($search_value) {
			$list = ClientePJ::search($search_value, 'all', array('page' => $page, 'limit' => $limit, 'conditions' => ' AND zpainel.ativo = ' . $ativo, 'order' => _isset($order_values[$order], $order_values['default'])));
			$count = ClientePJ::search($search_value, 'count');
		}
		else {
			$list = ClientePJ::find('all', array('page' => $page, 'limit' => $limit, 'conditions' => ' AND zpainel.ativo = ' . $ativo, 'order' => _isset($order_values[$order], $order_values['default'])));
			$count = ClientePJ::find('count');
		}
		
		$this->view->set('list', $list);
		$this->view->set('count', $count);
		$this->view->set('page', $page);
		$this->view->set('pages', (int) ceil($count / $limit));
	}
	
	public function overview_pj(int $cps = null) {
		if(!$cps || !$clientepj = ClientePJ::findByCps($cps)) {
			return $this->redirect('/painel/pj');
		}
		
		$clientepj['contatos'] = Contato::findByCpsConta($clientepj['cps']);
		$clientepj['enderecos'] = Endereco::findByCps($clientepj['cps']);
		$clientepj['attachments'] = Attachment::get_attachments(WEBROOT . DS . "attachments" . DS . "painel" . DS . "pj" . DS . $cps);
		
		$this->view->set('clientepj', $clientepj);
		$this->view->set('ocorrencia', Ocorrencia::findByCodigoPessoa($clientepj['cps'], 'all', array('order' => 'eocorrencia.data DESC')));
	}
	
	public function inserir_pj() {
		if($this->request->method === 'POST') {
			$data = $_POST;
			try {
				$clientepj = ClientePJ::save($data);
				return $this->redirect('/painel/overview_pj/' . $clientepj['cps']);
			}
			catch(Exception $e) {
				$this->view->set('error', $e->getMessage());
			}
		}
		

		$this->view->set('segmentacoes', Segmentacao::find('all', array('order' => 'eseg.ordem')));
		$this->view->set('tipos_telefone', TipoTelefone::find());
		$this->view->set('tipos_endereco', Endereco::tipoEndereco());
	}
	
	public function editar_pj(int $cps = null){
		if(!$cps || !$clientepj = ClientePJ::findByCps($cps)) {
			return $this->redirect('/painel/pj');
		}
		if($this->request->method === 'POST') {
			$data = $_POST;
			$clientepj['nps'] = _isset($_POST['nps'], $clientepj['nps']);
			$clientepj['cnpj'] = _isset($_POST['cnpj'], $clientepj['cnpj']);
			$clientepj['cseg'] = _isset($_POST['cseg'], $clientepj['cseg']);
			$clientepj['ativo'] = _isset($_POST['ativo'], $clientepj['ativo']);
			try {
				ClientePJ::save($clientepj);
				return $this->redirect('/painel/overview_pj/' . $clientepj['cps']);
			}
			catch(Exception $e) {
				$this->view->set('error', $e->getMessage());
			}
		}
		
		$this->view->set('clientepj', $clientepj);
		$this->view->set('segmentacoes', Segmentacao::find('all', array('order' => 'eseg.ordem')));
	}
	
	public function ajax_cps_to_clientepj($cps = null) {
		$this->autoRender = false;
		
		if(ClientePJ::cps_to_clientepj($cps)) {
			echo 'SUCCESS';
		}
		else {
			echo 'FAIL';
		}
	}
	
	public function relatorio() {
		$filter = '';
		
		$excel = (int) _isset($_GET['excel'], 0);
		
		if($excel==1){
			$this->layout = false;
		}
			
		$ativo = $_GET['ativo'] ?? 1;
		$dia = $_GET['dia'] ?? 0;
		$mes = $_GET['mes'] ?? 0;
		$ano = $_GET['ano'] ?? 0;
		$cseg2 = $_GET['seg2'] ?? 0;
		$cseg = $_GET['seg'] ?? 0;
		$interesses = $_GET['interesses'] ?? [];

		$conditions = '';
		$conditions .= " AND zpainel.ativo = $ativo";
		if($dia > 0) {
			$conditions .= " AND upsf.d_contato = $dia";
		}
		if($mes > 0) {
			$conditions .= " AND upsf.m_contato = $mes";
		}
		if($ano > 0) {
			$conditions .= " AND upsf.a_contato = $ano";
		}
		if($cseg > 0) {
			$conditions .= " AND zpainel.cseg = $cseg";
		}
		if($cseg2 > 0) {
			$conditions .= " AND eseg2.id = $cseg2";
		}
		if(!empty($interesses)) {
			foreach($interesses as $interesse) {
				$conditions .= " AND EXISTS (select czinteresse FROM zinteresse WHERE cps = eps.cps AND ctinteresse = $interesse)";
			}
		}

		$list = ClientePF::find(
			'all',
			[
				'order' => 'eps.nps',
				'conditions' => $conditions
			]
		);
		
		$this->view->set('list', $list);
		$this->view->set('excel', $excel);
		$this->view->set('interesses', Interesse::find());
		$this->view->set('segmentacoes', Segmentacao::find('all', array('order' => 'eseg.ordem')));
		$this->view->set('classificacoes', Classificacao::find('all', array('order' => 'eseg2.ordem')));
		$this->view->set('can', Calendario::ean());
		$this->view->set('cmes', Calendario::emes());
		$this->view->set('cdia', Calendario::edia());
		$this->view->set('ano', $ano);
		$this->view->set('mes', $mes);
		$this->view->set('dia', $dia);
	}
	
	public function relatorio_aniversariantes() {
		$filter = '';
		
		$excel = (int) _isset($_GET['excel'], 0);
		
		if($excel==1){
			$this->layout = false;
		}
			
		$ativo = (int) _isset($_GET['ativo'], 1);
		$filter .= " AND zpainel.ativo = $ativo";
		
		if(($dia = (int) _isset($_GET['dia'], date("j"))) !== 0) {
			$filter .= " AND upsf.d_nasc = $dia ";
		}
		if(($mes = (int) _isset($_GET['mes'], date("n"))) !== 0) {
			$filter .= " AND upsf.m_nasc = $mes ";
		}
		
		$list = ClientePF::findByCelular('all', array('order' => ' eps.nps ', 'conditions' => $filter));
		
		$this->view->set('excel', $excel);
		$this->view->set('list', $list);
		$this->view->set('cmes', Calendario::emes());
		$this->view->set('cdia', Calendario::edia());
		$this->view->set('mes', $mes);
		$this->view->set('dia', $dia);
	}
	
	/*
		Faz upload de arquivos.
	*/
	public function attachment_upload(int $cps) {
		$this->autoRender = false;
		
		if(!$pessoa = Pessoa::findById($cps)) {
			return $this->redirect("/");
		}
		
		$tipo = NULL;
		if($pessoa['cpsf']) {
			$tipo = "pf";
		}
		else if($pessoa['cpsj']) {
			$tipo = "pj";
		}
		
		if(!$tipo) {
			return $this->redirect("/");
		}
		
		if($this->request->method === "POST" && Attachment::have_uploads("attachments")) {
			Attachment::save_all("attachments", WEBROOT . DS . "attachments" . DS . "painel" . DS . $tipo . DS . $cps);
		}
		
		return $this->redirect("/painel/overview_" . $tipo . "/" . $cps);
	}
	
	public function attachment_delete(int $cps) {
		$this->autoRender = false;
		
		if(!$pessoa = Pessoa::findById($cps)) {
			return $this->redirect("/");
		}
		
		$tipo = NULL;
		if($pessoa['cpsf']) {
			$tipo = "pf";
		}
		else if($pessoa['cpsj']) {
			$tipo = "pj";
		}
		
		if(!$tipo) {
			return $this->redirect("/");
		}
		
		if($this->request->method === "POST") {
			$file = _isset($_POST['image_name'], null);
			if($file !== null) {
				Attachment::delete_attachment(WEBROOT . DS . "attachments" . DS . "painel" . DS . $tipo . DS . $cps . DS . $file);
			}
		}
		
		return $this->redirect("/painel/overview_" . $tipo . "/" . $cps);
	}
	
	
	public function search() {
		$this->autoRender = false;
		
		$term = _isset($_GET["term"], null);
		
		$result = [];
		
		if($term !== null) {
			$result = ClientePF::search($term, "all", array("limit" => 5)); 
		}
		
		echo json_encode($result);
	}
	
	public function search2() {
		App::import("Cliente", "Model");
		
		$this->autoRender = false;
		
		$term = _isset($_GET["term"], null);
		
		$result = [];
		
		if($term !== null) {
			$result = Cliente::search($term, "all", array("limit" => 5));
		}
		
		echo json_encode($result);
	}
}