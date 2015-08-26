<?php

namespace Node;

use ArrayIterator, IteratorAggregate;
use Foundation\RangeQuery;
use Foundation\Storage as Exception;
use Foundation\AndQuery, Foundation\EqualQuery;
use Foundation\TrueQuery;

class Graph implements IteratorAggregate {
	private $data;

	public function __construct($data = []) {
		$this->data = $data;
	}

	public function __get($field) {
		if ($obj = $this->get()) {
			return $obj->{$field};
		}
	}

	public function __set($field, $value) {
		if ($obj = $this->get()) {
			$obj->{$field} = $value;
		}
	}

	public function __call($method, $args) {
		if ($obj = $this->get()) {
			return call_user_func_array([$obj, $method], $args);
		}
	}

	/**
	 * @param $name
	 * @param array $args
	 * @return Graph
	 */
	public static function query($name, $args = []) {
		assert('is_string($args) or is_array($args)');

		$class = config('graph')->get($name);

        assert('!empty($class)', "Node {$name} missing the graph config.");
		assert('class_exists($class)', "Class `{$class}` not exists.");

		$node = new $class;
		if (is_string($args)) {
			$result = $node->load($args);
		} else {
			if ($args) {
				$query = new AndQuery();
				foreach ($node as $field => $value)
					if (isset($args[$field])) {
                        $val = $args[$field];
                        if (preg_match("/^\[(?<lower>\d*),(?<upper>\d*)\]$/", $val, $m)) {
                            $query->add(new RangeQuery($field, $m['lower'] == '' ? null : $m['lower'], $m['upper'] == '' ? null : $m['upper']));
                        } else {
                            $query->add(new EqualQuery($field, $args[$field]));
                        }
					}

				if ($query->isEmpty())
					$query = new TrueQuery();
			}
			$result = $node->find($query, $args);
		}

		return new Graph($result);
	}

	/**
	 * @param $name
	 * @return array
	 */
	public function getEdges($name, $args = []) {
		$that = $this->get();

		$class = config('graph')->get($name);
		assert('class_exists($class)');

		$node = new $class;

		$query = new AndQuery();
		foreach ($node->getFields() as $field) {
			if (strstr($node->getFieldTag($field, 'type'), get_class($that))) {
				$query->add(new EqualQuery($field, $that->id));
			}
		}

		if ($query->isEmpty()) {
			$query = new TrueQuery();
		}

		return new Graph($node->find($query, $args));
	}

	public function get($index = 0) {
		$data = $this->toArray();
		return isset($data[$index]) ? $data[$index] : null;
	}

	public function size() {
		return count($this->toArray());
	}

	public function toArray() {
		return is_array($this->data) ? array_values($this->data) : [$this->data];
	}

	public function getIterator() {
		return new ArrayIterator($this->toArray());
	}
}