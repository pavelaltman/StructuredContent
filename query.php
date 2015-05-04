<?php
class SqlQuery
{
	private $sstr, // columns to select 
			$fstr, // tables to select from 
			$wstr, // where clause
			$ostr, // order by clause
			$jstr, // left join clause 
			$istr, // table to insert 
			$cstr, // columns to insert 
			$vstr, // values to insert
			$ustr, // value pairs for upadate, filled with insert values
			$dstr; // update on duplicate key
	

	function Reset()
	{
		$this->sstr="" ; 
		$this->fstr="" ; 
		$this->jstr="" ; 
		$this->wstr="" ; 
		$this->ostr="" ; 
		$this->istr="" ; 
		$this->cstr="" ; 
		$this->vstr="" ;
		$this->dstr="" ;
		$this->ustr="" ;
	}
	
	function __construct()
	{
		$this->Reset() ;
	}
	
	function add_select($add_field)
	{
		$this->sstr=$this->sstr.(strlen($this->sstr) ? ",":"").$add_field ;
		return $this->sstr ;
	}

	function add_from($add_table)
	{
		$this->fstr=$this->fstr.(strlen($this->fstr) ? ",":"").$add_table ;
		return $this->fstr ;
	}
	
	function add_order($add_fld)
	{
		$this->ostr=$this->ostr.(strlen($this->ostr) ? ",":"").$add_fld ;
		return $this->ostr ;
	}
	
	function add_join($add_table,$add_condition)
	{
		$this->jstr=$this->jstr." LEFT JOIN ".$add_table." ON ".$add_condition ;
		return $this->jstr ;
	}

	function add_where($add_condition)
	{
		$this->wstr=$this->wstr.(strlen($this->wstr) ? " AND ":"").$add_condition ;
		return $this->wstr ;
	}
	
	function add_insert($ins_table)
	{
		$this->istr=$ins_table ;
		return $this->istr ;
	}
	
	function add_values($column,$value)
	{
		$this->cstr=$this->cstr.(strlen($this->cstr) ? ",":"").$column ;
		$this->vstr=$this->vstr.(strlen($this->vstr) ? ",":"")."'".$value."'" ;

	    // also fill ustr
	    $this->ustr=$this->ustr.(strlen($this->ustr) ? ",":"").$column."='".$value."'" ;
	}
	
	function add_duplicate($add_dup)
	{
		$this->dstr=$this->dstr.(strlen($this->dstr) ? ", ":"").$add_dup ;
		return $this->dstr ;
	}

	function get_query()
	{
		return "SELECT ".$this->sstr." FROM ".$this->fstr.(strlen($this->jstr) ? "  ".$this->jstr." " : "").(strlen($this->wstr) ? " WHERE ".$this->wstr : "").(strlen($this->ostr) ? " ORDER BY ".$this->ostr : "") ;
	}

	function get_insert_query()
	{
		return "INSERT INTO ".$this->istr." (".$this->cstr.") VALUES (".$this->vstr.")".(strlen($this->dstr) ? " ON DUPLICATE KEY UPDATE ".$this->dstr : "") ;
	}
	function get_update_query()
	{
		return "UPDATE ".$this->istr." SET ".$this->ustr.(strlen($this->wstr) ? " WHERE ".$this->wstr : "") ;
	}
}


?>