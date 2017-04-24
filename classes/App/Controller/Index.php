<?php
namespace App\Controller;

class Index extends \App\Page {

	private $selected_elements = array();
	private $mission = array();

	public function action_index()
	{
		$this->view->subview = 'home';
		$query = $this->pixie->orm->get('element')->where('parent_id',0);
		$selected_category = $this->request->get('selected_category');
		if (empty($selected_category))
			$selected_category = $this->pixie->session->get('selected_category');
		else
			$this->pixie->session->set('selected_category',$selected_category);
		if (!empty($selected_category) && $selected_category != -1)
			$query->where('category_id',$selected_category);
		else
		{
			$query->where('category_id',5);
			$this->pixie->session->set('selected_category',5);
			$selected_category = 5;
		}
		$elements = $query->find_all()->as_array();
		$this->view->elements = $elements;
		$this->view->components = $this->pixie->orm->get('component')->order_by('order')->find_all();
		$this->view->selected_category = $selected_category;
		$categories = array();
		// ONLY A3
		foreach ($this->pixie->orm->get('element')->where('id',5)->find_all() as $element) {
			if (!in_array($element->category, $categories))
				$categories[] = $element->category;
		}
		$this->view->categories = $categories;
	}

	public function action_generate(){

		$selected_components = $this->request->post('selected_components');
		$selected_elements = $this->request->post('selected_root_elements');

		if (!is_array($selected_components) || count($selected_components) <= 0 || empty($selected_elements))
		{
			$this->response->redirect('/');
			$this->execute = false;
			return;
		}
		$elements = array();
		$components = array();
		$selected_elements = explode(',',$this->request->post('selected_root_elements'));
		
		$roots = array();
		$ids = array();
		
		function constructarray($patharray) {
			$array = array();
			if (count($patharray) <= 0) return $array;
			$id = array_pop($patharray);
			$array[$id] = constructarray($patharray);
			return $array;
		}

		function readArray( $arr, $k, $default = 0 ) {
    		return isset( $arr[$k] ) ? $arr[$k] : $default;
		}

		function merge( $arr1, $arr2 ) {
		    $result = array();
		    foreach( $arr1 as $k => $v ) {
		        if( is_numeric( $v ) ) {
		            $result[$k] = (int)$v + (int) readArray( $arr2, $k );
		        } else {
		            $result[$k] = merge( $v, readArray($arr2, $k, array()) );
		        }
		    }
		    foreach( $arr2 as $k => $v ) {
		        if( is_numeric( $v ) ) {
		            $result[$k] = (int)$v + (int) readArray( $arr1, $k );
		        } else {
		            $result[$k] = merge( $v, readArray($arr1, $k, array()) );
		        }
		    }
		    return $result;
		}
		foreach ($selected_elements as $path)
		{
			$patharray = explode('/',$path);
			$ids = merge($ids,constructarray($patharray));
		}

		foreach ($ids as $key => $array)
		{
			$id = $key;
			$element = $this->pixie->orm->get('element')->where('id',$id)->find();
			$elements[] = $element->subtree($array);
		}
		//exit;

		$this->selected_elements = $elements;

		//*
		//print_r($selected_components);
		//exit;
		//*/
		
		/*$nodeids = array();
		$edges = array();
		foreach ($selected_components as $key => $componentid)
		{
			$nodeids[] = $componentid;
			//var_dump($componentid);
			$component = $this->pixie->orm->get('component',$componentid);
			foreach ($component->dependson->find_all() as $key => $value) {
				$edges[] = array(intval($value->id),intval($componentid));
				//var_dump($value->id);
			}
		}

		print_r($nodeids);
		print_r($edges);
		exit;

		$selected_components = $this->topological_sort($nodeids,$edges);*/

		/*
		print_r($selected_components);
		exit;
		//*/

		foreach ($selected_components as $key => $componentid)
		{
			$component = $this->pixie->orm->get('component',$componentid);
			//echo "Processing component " . $component->name . "<br>";
			$result = $this->process_component($component);
		}
		$this->generate_mission_archive();
		//$this->view->subview = 'generator';
		$this->execute = false;
	}

	private function process_component($component)
	{
		$selected_elements = $this->selected_elements;
		$current_component = array('name'=>$component->name,'properties'=>$component->properties_as_array);
		$code = $component->code;

		if (!empty($code))
		{
			$temp_file = tempnam(sys_get_temp_dir(), 'MTG');
			file_put_contents($temp_file, $code);

			ob_start();
			include($temp_file);
			ob_get_clean();

			unlink($temp_file);
		}
		$files = $component->files->find_all();
		foreach ($files as $file)
		{
			$namearray = array();
			$result = eval($file->name_template);
			if (is_array($result))
			{
				$namearray = $result;
			}
			else
			{
				$namearray[] = $file->name;
			}
			foreach ($namearray as $name)
			{
				//echo "Processing file " . $file->path . '/' . $name . "<br>";
				$temp_file = tempnam(sys_get_temp_dir(), 'MTG');
				file_put_contents($temp_file, $file->content);

				ob_start();
				include($temp_file);
				$result = ob_get_clean();

				unlink($temp_file);

				//echo "<pre>";
				//print_r($this->reindent($result));
				//echo "</pre>";
				$path = $name;
				if (!empty($file->path))
					$path = $file->path.'/'.$name;
				if (isset($this->mission[$path]))
					$this->mission[$path] .= "\r\n".$this->reindent($result);
				else
					$this->mission[$path] = $this->reindent($result);
			}
		}
	}

	private function generate_mission_archive()
	{
		$archivefile = tempnam(sys_get_temp_dir(), 'MTGarchive');
		$archive = new \ZipArchive();
		$archive->open($archivefile,\ZIPARCHIVE::OVERWRITE);
		forEach ($this->mission as $filename => $content)
		{
			$archive->addFromString('utg_xx_xx_template_3-3.VR/'.trim($filename),trim($content));
		}
		$archive->close();
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($archivefile));
		header('Content-Disposition: attachment; filename="utg_xx_xx_template_3-3.zip"');
		readfile($archivefile);
		unlink($archivefile);
	}

	private function reindent($code)
	{
		$result = array();
		$code = explode("\r\n", $code);
		$level = 0;
		foreach ($code as $line) {
			$temp = 0;
			$line = trim($line);
			if ($line == '') continue;
			if ($line == '{')
			{
				$level++;
				$temp = 1;
			}
			elseif ($line == '}' || $line == '};' || preg_match('/^(\t*|\ *)} forEach/', $line) > 0)
				$level--;

			if ($level < 0) $level = 0;
			$line = str_repeat("\t", $level-$temp).$line;
			$result[] = $line;
		}
		return implode("\r\n", $result);
	}

	private function topological_sort($nodeids, $edges)
	{
		// initialize variables
		$L = $S = $nodes = array(); 

		// remove duplicate nodes
		$nodeids = array_unique($nodeids); 	

		// remove duplicate edges
		$hashes = array();
		foreach($edges as $k=>$e) {
			$hash = md5(serialize($e));
			if (in_array($hash, $hashes)) { unset($edges[$k]); }
			else { $hashes[] = $hash; }; 
		}

		// Build a lookup table of each node's edges
		foreach($nodeids as $id) {
			$nodes[$id] = array('in'=>array(), 'out'=>array());
			foreach($edges as $e) {
				if ($id==$e[0]) { $nodes[$id]['out'][]=$e[1]; }
				if ($id==$e[1]) { $nodes[$id]['in'][]=$e[0]; }
			}
		}

		// While we have nodes left, we pick a node with no inbound edges, 
		// remove it and its edges from the graph, and add it to the end 
		// of the sorted list.
		foreach ($nodes as $id=>$n) { if (empty($n['in'])) $S[]=$id; }
		while (!empty($S)) {
			$L[] = $id = array_shift($S);
			foreach($nodes[$id]['out'] as $m) {
				$nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
				if (empty($nodes[$m]['in'])) { $S[] = $m; }
			}
			$nodes[$id]['out'] = array();
		}

		// Check if we have any edges left unprocessed
		foreach($nodes as $n) {
			if (!empty($n['in']) or !empty($n['out'])) {
				return null; // not sortable as graph is cyclic
			}
		}
		return $L;
	}
}
