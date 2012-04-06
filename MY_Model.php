<?php

class MY_Model extends CI_Model {

    protected $_table;
    protected $_primary_key = 'id';
    protected $_loaded_from_db;
    protected $_magic_timestamps = false;
    protected $_magic_timestamp_created = 'created';
    protected $_magic_timestamp_updated = 'updated';
    
    protected $_attributes;
    
    protected $_CI;
    
    function __construct() {
        parent::__construct();
        
        $this->_table = strtolower($this->_pluralize(get_class($this)));
        
        $this->_CI =& get_instance();
        $this->reset();
    }
    
    private function _pluralize($word) {
        $plural = array(
        '/(quiz)$/i' => '$1zes',
        '/^(ox)$/i' => '$1en',
        '/([m|l])ouse$/i' => '$1ice',
        '/(matr|vert|ind)ix|ex$/i' => '$1ices',
        '/(x|ch|ss|sh)$/i' => '$1es',
        '/([^aeiouy]|qu)ies$/i' => '$1y',
        '/([^aeiouy]|qu)y$/i' => '$1ies',
        '/(hive)$/i' => '$1s',
        '/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '$1a',
        '/(buffal|tomat)o$/i' => '$1oes',
        '/(bu)s$/i' => '$1ses',
        '/(alias|status)/i'=> '$1es',
        '/(octop|vir)us$/i'=> '$1i',
        '/(ax|test)is$/i'=> '$1es',
        '/s$/i'=> 's',
        '/$/'=> 's');

        $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

        $irregular = array(
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves');

        $lowercased_word = strtolower($word);

        foreach ($uncountable as $_uncountable){
            if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
                return $word;
            }
        }

        foreach ($irregular as $_plural=> $_singular){
            if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
                return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
            }
        }

        foreach ($plural as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }
        return false;
    }
    
    function _singularize($word) {
        $singular = array (
        '/(quiz)zes$/i' => '\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias|status)es$/i' => '\1',
        '/([octop|vir])i$/i' => '\1us',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/(bus)es$/i' => '\1',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1ovie',
        '/(s)eries$/i' => '\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/([^f])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(n)ews$/i' => '\1ews',
        '/s$/i' => '',
        );

        $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

        $irregular = array(
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves');

        $lowercased_word = strtolower($word);
        foreach ($uncountable as $_uncountable){
            if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
                return $word;
            }
        }

        foreach ($irregular as $_plural=> $_singular){
            if (preg_match('/('.$_singular.')$/i', $word, $arr)) {
                return preg_replace('/('.$_singular.')$/i', substr($arr[0],0,1).substr($_plural,1), $word);
            }
        }

        foreach ($singular as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }
    
    public function create() {
        $this->reset();
    }
    
    public function reset() {
        $this->_attributes = array();
        $this->_loaded_from_db = false;
    }
    
    function __get($key) {
        if (isset($this->_attributes[$key])) {
            return $this->_attributes[$key];
        } else {
            return parent::__get($key);
        }
    }
    
    function __set($key, $value) {
        if (substr($key, 0, 1) != '_') {
            $this->_attributes[$key] = $value;
        } else {
            $this->$key = $value;
        }
    }
    
    function __call($name, $arguments) {
        if (substr($name, 0, 8) == 'load_by_') {
            $field = substr($name, 8);
            return $this->_load_by_something($field, $arguments[0]);
        } else if (substr($name, 0, 4) == 'add_') {
            $other_model = strtolower(substr($name, 4));
            
            if (is_array($arguments) && count($arguments) > 0) {
                $other_id = array_shift($arguments);
                return $this->_add_relationship($other_model, $other_id, $arguments);
            } else {
                return false;
            }
        } else if (substr($name, 0, 7) == 'update_') {
            $other_model = strtolower(substr($name, 7));
            
            if (is_array($arguments) && count($arguments) > 0) {
                $other_id = array_shift($arguments);
                return $this->_update_relationship($other_model, $other_id, $arguments);
            } else {
                return false;
            }
        } else if (substr($name, 0, 7) == 'remove_') {
            $other_model = strtolower(substr($name, 7));
            if (is_array($arguments) && count($arguments) > 0) {
                $other_id = array_shift($arguments);
                return $this->_remove_relationship($other_model, $other_id);
            } else {
                return false;
            }
        } else if (substr($name, 0, 4) == 'get_') {
            $other_table = strtolower(substr($name, 4));
            return $this->_get_relationship($other_table);
        } else if (substr($name, 0, 4) == 'has_') {
            $other_model = strtolower(substr($name, 4));
            if (is_array($arguments) && count($arguments) > 0) {
                $other_id = array_shift($arguments);
                return $this->_has_relationship($other_model, $other_id);
            } else {
                return false;
            }
        }
    }
    
    private function _get_relationship_table($other_table) {
        $tables = array($other_table, $this->_table);
        sort($tables);
        
        return implode('_', $tables);
    }
    
    private function _add_relationship($other_model, $other_id, $params = array()) {
        $_magic_timestamps = false;
        
        $other_table = strtolower($this->_pluralize($other_model));
        $relationship_table = $this->_get_relationship_table($other_table);

        if (count($params) > 0) {
            // see if extra args are given in
            $attributes = array_shift($params);
            if (!is_array($attributes)) {
                $attributes = array();
            }
            
            if (count($params) > 0) {
                $_magic_timestamps = $params[0];
                if (is_bool($_magic_timestamps) == false) {
                    $_magic_timestamps = false;
                }
            }
        } else {
            $attributes = array();
        }
        
        $attributes[strtolower(get_class($this)).'_id'] = $this->{$this->_primary_key};
        $attributes[$other_model.'_id'] = $other_id;
        
        if ($_magic_timestamps) {
            $time = time();
            $attributes[$this->_magic_timestamp_created] = $time;
            $attributes[$this->_magic_timestamp_updated] = $time;
        }
        
        $this->_CI->db->insert($relationship_table, $attributes);
        if ($this->_CI->db->affected_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    private function _update_relationship($other_model, $other_id, $params = array()) {
        $_magic_timestamps = false;
        
        $other_table = strtolower($this->_pluralize($other_model));
        $relationship_table = $this->_get_relationship_table($other_table);

        if (count($params) > 0) {
            // see if extra args are given in
            $attributes = array_shift($params);
            if (!is_array($attributes)) {
                $attributes = array();
            }
            
            if (count($params) > 0) {
                $_magic_timestamps = $params[0];
                if (is_bool($_magic_timestamps) == false) {
                    $_magic_timestamps = false;
                }
            }
        } else {
            $attributes = array();
        }
        
        if ($_magic_timestamps) {
            $time = time();
            $attributes[$this->_magic_timestamp_updated] = $time;
        }
        
        $where = array(
            strtolower(get_class($this)).'_id' => $this->{$this->_primary_key},
            $other_model.'_id' => $other_id
        );
        
        $this->_CI->db->update($relationship_table, $attributes, $where);
        if ($this->_CI->db->affected_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    private function _has_relationship($other_model, $other_id) {
        $other_table = strtolower($this->_pluralize($other_model));
        $relationship_table = $this->_get_relationship_table($other_table);
        $query = $this->_CI->db->get_where($relationship_table, array(strtolower(get_class($this)).'_id' => $this->{$this->_primary_key}, $other_model.'_id' => $other_id), 1, 0);
        if ($query->num_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    private function _get_relationship($other_table) {
        $relationship_table = $this->_get_relationship_table($other_table);
        
        $query = $this->_CI->db->get_where($relationship_table, array(strtolower(get_class($this)).'_id' => $this->{$this->_primary_key}));
        return $query->result();
    }
    
    private function _remove_relationship($other_model, $other_id) {
        $other_table = strtolower($this->_pluralize($other_model));
        $relationship_table = $this->_get_relationship_table($other_table);
        
        $query = $this->_CI->db->delete($relationship_table, array(strtolower(get_class($this)).'_id' => $this->{$this->_primary_key}, $other_model.'_id' => $other_id));
        return $query->result();
    }
    
    public function load($param = null) {
        if (is_array($param) || is_object($param)) {
            foreach ($param as $column => $value) {
                $this->_attributes[$column] = $value;
            }
            
            return true;
        } else {
            return $this->_load_by_something($this->_primary_key, $param);
        }
    }
    
    public function _load_by_something($field, $val) {
        $this->reset();
        if ($val) {
            $query = $this->_CI->db->get_where($this->_table, array($field => $val), 1, 0);
            if ($query->num_rows() == 1) {
                $this->_loaded_from_db = true;
                
                $row = $query->row();
                foreach ($row as $column => $value) {
                    $this->_attributes[$column] = $value;
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    function save() {
        if ($this->_magic_timestamps == true) {
            $this->{$this->_magic_timestamp_updated} = time();
        }
        
        if ($this->_loaded_from_db == true) {
            //update
            $this->_CI->db->where($this->_primary_key, $this->{$this->_primary_key});
            $this->_CI->db->update($this->_table, $this->_attributes);
        } else {
            //create
            if ($this->_magic_timestamps == true) {
                $this->{$this->_magic_timestamp_created} = time();
            }
            
            $this->_CI->db->insert($this->_table, $this->_attributes);

            if (!array_key_exists($this->_primary_key, $this->_attributes) || !$this->_attributes[$this->_primary_key]) {
                $new_id = $this->_CI->db->insert_id();
                $this->{$this->_primary_key} = $new_id;
            }
            $this->_loaded_from_db = true;
        }

        if ($this->_CI->db->affected_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function delete() {
        $this->_CI->db->delete($this->_table, array($this->_primary_key => $this->{$this->_primary_key}));
        if ($this->_CI->db->affected_rows() == 1) {
            return true;
        } else {
            return false;
        }
    }
    
}

?>