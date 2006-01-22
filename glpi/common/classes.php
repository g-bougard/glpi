<?php
/*
 * @version $Id$
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------
/**
 *  Database class for Mysql
 */
class DBmysql {

	//! Database Host
	var $dbhost	= ""; 
	//! Database User
	var $dbuser = "";
	//! Database Password 
	var $dbpassword	= "";
	//! Default Database
	var $dbdefault	= "";
	//! Database Handler
	var $dbh;
	//! Database Error
	var $error = 0;

	/**
	* Constructor / Connect to the MySQL Database
	*
	* Use dbhost, dbuser, dbpassword and dbdefault
	* Die if connection or database selection failed
	*
	* @return nothing 
	*/
	function DBmysql()
	{  // Constructor
		$this->dbh = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpassword) or $this->error = 1;
		if ($this->dbh)
		mysql_select_db($this->dbdefault) or $this->error = 1;
		else {
			nullHeader("Mysql Error",$_SERVER['PHP_SELF']);
			echo "<div align='center'><strong>A link to the Mysql server could not be established. Please Check your configuration.</strong></div>";
			nullFooter("Mysql Error",$_SERVER['PHP_SELF']);
			die();
		}
	}
	/**
	* Execute a MySQL query
	* @param $query Query to execute
	* @return Query result handler
	*/
	function query($query) {
		global $cfg_debug,$DEBUG_SQL_STRING,$SQL_TOTAL_TIMER, $SQL_TOTAL_REQUEST;
		
		if ($cfg_debug["active"]) {
			if ($cfg_debug["sql"]){		
				$SQL_TOTAL_REQUEST++;
				$DEBUG_SQL_STRING.="N&#176; ".$SQL_TOTAL_REQUEST." : <br>".$query;
				
				if ($cfg_debug["profile"]){		
					$TIMER=new Script_Timer;
					$TIMER->Start_Timer();
				}
			}
		}

		$res=mysql_query($query);

		if ($cfg_debug["active"]) {
			if ($cfg_debug["profile"]&&$cfg_debug["sql"]){		
				$TIME=$TIMER->Get_Time();
				$DEBUG_SQL_STRING.="<br><b>Time: </b>".$TIME."s";
				$SQL_TOTAL_TIMER+=$TIME;
			}
			if ($cfg_debug["sql"]){
				$DEBUG_SQL_STRING.="<hr>";
			}
		}
		
		return $res;
	}
	/**
	* Give result from a mysql result
	* @param $result MySQL result handler
	* @param $i Row to give
	* @param $field Field to give
	* @return Value of the Row $i and the Field $field of the Mysql $result
	*/
	function result($result, $i, $field) {
		$value=get_magic_quotes_runtime()?stripslashes_deep(mysql_result($result, $i, $field)):mysql_result($result, $i, $field);
		return $value;
	}
	/**
	* Give number of rows of a Mysql result
	* @param $result MySQL result handler
	* @return number of rows
	*/
	function numrows($result) {
		return mysql_num_rows($result);
	}
	/**
	* Fetch array of the next row of a Mysql query
	* @param $result MySQL result handler
	* @return result array
	*/
	function fetch_array($result) {
		$value=get_magic_quotes_runtime()?stripslashes_deep(mysql_fetch_array($result)):mysql_fetch_array($result);
		return $value;
	}
	/**
	* Fetch row of the next row of a Mysql query
	* @param $result MySQL result handler
	* @return result row
	*/
	function fetch_row($result) {
		$value=get_magic_quotes_runtime()?stripslashes_deep(mysql_fetch_row($result)):mysql_fetch_row($result);
		return $value;
	}
	/**
	* Fetch assoc of the next row of a Mysql query
	* @param $result MySQL result handler
	* @return result associative array
	*/
	function fetch_assoc($result) {
		$value=get_magic_quotes_runtime()?stripslashes_deep(mysql_fetch_assoc($result)):mysql_fetch_assoc($result);
		return $value;
	}
	/**
	* Move current pointer of a Mysql result to the specific row
	* @param $result MySQL result handler
	* @param $seek row to move current pointer
	* @return boolean
	*/
	function data_seek($result,$num){
		return mysql_data_seek ($result,$num);
	}
	/**
	* Give ID of the last insert item by Mysql
	* @return item ID
	*/
	function insert_id() {
 		return mysql_insert_id();
 	}
	/**
	* Give number of fields of a Mysql result
	* @param $result MySQL result handler
	* @return number of fields
	*/
	function num_fields($result) {
		return mysql_num_fields($result);
	}
	/**
	* Give name of a field of a Mysql result
	* @param $result MySQL result handler
	* @param $nb number of column of the field
	* @return name of the field
	*/
	function field_name($result,$nb)
	{
		return mysql_field_name($result,$nb);
	}
	function field_flags($result,$field)
	{
		return mysql_field_flags($result,$field);
	}
	function list_tables() {
		return mysql_list_tables($this->dbdefault);
	}
	function table_name($result,$nb) {
		return mysql_tablename($result,$nb);
	}
	function list_fields($table) {
		return mysql_list_fields($this->dbdefault,$table);
	}
	function affected_rows() {
		return mysql_affected_rows($this->dbh);
	}
	function errno()
	{
		return mysql_errno();
	}

	function error()
	{
		return mysql_error();
	}
	function close()
	{
		return mysql_close($this->dbh);
	}
	
}




/**
 *  Common Item of GLPI : Global simple interface to items - abstraction usage
 */
class CommonItem{
	//! Object Type depending of the device_type
	var $obj = NULL;	
	//! Device Type ID of the object
	var $device_type=0;
	//! Device ID of the object
	var $id_type=0;
	
	
	/**
	* Get an Object / General Function
	*
	* Create a new Object depending of $device_type and Get the item with the ID $id_device
	*
	* @param $device_type Device Type ID of the object
	* @param $id_device Device ID of the object
	*
	* @return boolean : object founded and loaded
	*/
	function getfromDB ($device_type,$id_device) {
		$this->id_device=$id_device;
		$this->device_type=$device_type;
		// Make new database object and fill variables

			switch ($device_type){
			case COMPUTER_TYPE :
				$this->obj=new Computer;
				break;
			case NETWORKING_TYPE :
				$this->obj=new Netdevice;
				break;
			case PRINTER_TYPE :
				$this->obj=new Printer;
				break;
			case MONITOR_TYPE : 
				$this->obj= new Monitor;	
				break;
			case PERIPHERAL_TYPE : 
				$this->obj= new Peripheral;	
				break;				
			case SOFTWARE_TYPE : 
				$this->obj= new Software;	
				break;				
			case CONTRACT_TYPE : 
				$this->obj= new Contract;	
				break;				
			case ENTERPRISE_TYPE : 
				$this->obj= new Enterprise;	
				break;	
			case CONTACT_TYPE : 
				$this->obj= new Contact;	
				break;	
			case KNOWBASE_TYPE : 
				$this->obj= new kbitem;	
				break;					
			case CARTRIDGE_TYPE : 
				$this->obj= new CartridgeType;	
				break;					
			case CONSUMABLE_TYPE : 
				$this->obj= new ConsumableType;	
				break;					
			case CARTRIDGE_ITEM_TYPE : 
				$this->obj= new Cartridge;	
				break;					
			case CONSUMABLE_ITEM_TYPE : 
				$this->obj= new Consumable;	
				break;					
			case LICENSE_TYPE : 
				$this->obj= new License;	
				break;					
			case DOCUMENT_TYPE : 
				$this->obj= new Document;	
				break;					
			}

			if ($this->obj!=NULL){
				// Do not load devices
					return $this->obj->getfromDB($id_device);
			}
			else return false;
			
	}
	
	/**
	* Set the device type
	*
	* @param $device_type Device Type ID of the object
	*
	*/
	function setType ($device_type){
		$this->device_type=$device_type;
	}

	/**
	* Get The Type Name of the Object
	*
	* @return String: name of the object type in the current language
	*/
	function getType (){
		global $lang;
		
		switch ($this->device_type){
			case GENERAL_TYPE :
				return $lang["help"][30];
				break;
			case COMPUTER_TYPE :
				return $lang["computers"][44];
				break;
			case NETWORKING_TYPE :
				return $lang["networking"][12];
				break;
			case PRINTER_TYPE :
				return $lang["printers"][4];
				break;
			case MONITOR_TYPE : 
				return $lang["monitors"][4];
				break;
			case PERIPHERAL_TYPE : 
				return $lang["peripherals"][4];
				break;				
			case SOFTWARE_TYPE : 
				return $lang["software"][10];
				break;				
			case CONTRACT_TYPE : 
				return $lang["financial"][1];
				break;				
			case ENTERPRISE_TYPE : 
				return $lang["financial"][26];
				break;
			case CONTACT_TYPE : 
				return $lang["financial"][32];
				break;
			case KNOWBASE_TYPE : 
				return $lang["knowbase"][0];
				break;	
			case CARTRIDGE_TYPE : 
				return $lang["cartridges"][16];
				break;
			case CONSUMABLE_TYPE : 
				return $lang["consumables"][16];
				break;					
			case LICENSE_TYPE : 
				return $lang["software"][11];
				break;					
			case CARTRIDGE_ITEM_TYPE : 
				return $lang["cartridges"][0];
				break;
			case CONSUMABLE_ITEM_TYPE : 
				return $lang["consumables"][0];
				break;					
			case DOCUMENT_TYPE : 
				return $lang["document"][0];
				break;					
			}
	
	}


	/**
	* Get The Name of the Object
	*
	* @return String: name of the object in the current language
	*/
	function getName(){
		global $lang;
		
		if ($this->device_type==0) return "";
		
		if ($this->device_type==KNOWBASE_TYPE&&$this->obj!=NULL&&isset($this->obj->fields["question"])&&$this->obj->fields["question"]!="")
			return $this->obj->fields["question"];
		else if ($this->device_type==LICENSE_TYPE&&$this->obj!=NULL&&isset($this->obj->fields["serial"])&&$this->obj->fields["serial"]!="")
			return $this->obj->fields["serial"];
		else if (($this->device_type==CARTRIDGE_TYPE||$this->device_type==CONSUMABLE_TYPE)&&$this->obj!=NULL&&$this->obj->fields["name"]!=""){
			$name=$this->obj->fields["name"];
			if (isset($this->obj->fields["ref"])&&!empty($this->obj->fields["ref"]))			
				$name.=" - ".$this->obj->fields["ref"];
			return $name;
			}
		else if ($this->obj!=NULL&&isset($this->obj->fields["name"])&&$this->obj->fields["name"]!="")
			return $this->obj->fields["name"];
		else 
			return "N/A";
	}
	function getNameID(){
		if ($this->device_type==0)
		return $this->getName();
		else return $this->getName()." (".$this->id_device.")";
	}
	/**
	* Get The link to the Object
	*
	* @return String: link to the object type in the current language
	*/
	function getLink(){
	
		global $cfg_install,$cfg_layout;
		$ID="";
		switch ($this->device_type){
			case GENERAL_TYPE :
				return $this->getName();
				break;
			case COMPUTER_TYPE :
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/computers/computers-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;
			case NETWORKING_TYPE :
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/networking/networking-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;
			case PRINTER_TYPE :
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/printers/printers-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;
			case MONITOR_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/monitors/monitors-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;
			case PERIPHERAL_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/peripherals/peripherals-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;				
			case SOFTWARE_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/software/software-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;				
			case CONTRACT_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/contracts/contracts-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;				
			case ENTERPRISE_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/enterprises/enterprises-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;
			case CONTACT_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/contacts/contacts-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;
			case KNOWBASE_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/knowbase/knowbase-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;						
			case CARTRIDGE_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/cartridges/cartridges-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;						
			case CONSUMABLE_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/consumables/consumables-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;						
			case LICENSE_TYPE : 
				return $this->getName();
				break;						
			case CARTRIDGE_ITEM_TYPE : 
				return $this->getName();
				break;						
			case CONSUMABLE_ITEM_TYPE : 
				return $this->getName();
				break;						
			case DOCUMENT_TYPE : 
				if($cfg_layout["view_ID"]) $ID= " (".$this->id_device.")";
				return "<a href=\"".$cfg_install["root"]."/documents/documents-info-form.php?ID=".$this->id_device."\">".$this->getName()."$ID</a>";
				break;						
			
			}

	
	}
	
}




?>
