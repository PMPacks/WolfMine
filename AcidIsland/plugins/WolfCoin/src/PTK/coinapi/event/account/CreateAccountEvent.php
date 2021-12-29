<?php

/*
 * Coins, the massive coin plugin with many features for PocketMine-MP
 * Copyright (C) 2017-2018  PTK-KienPham <kien192837@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace PTK\coinapi\event\account;

use PTK\coinapi\event\CoinEvent;
use PTK\coinapi\Coin;

class CreateAccountEvent extends CoinEvent{
	private $username, $defaultCoin;
	public static $handlerList;
	
	public function __construct(Coin $plugin, $username, $defaultCoin, $issuer){
		parent::__construct($plugin, $issuer);
		$this->username = $username;
		$this->defaultCoin = $defaultCoin;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function setDefaultCoin($coin){
		$this->defaultCoin = $coin;
	}
	
	public function getDefaultCoin(){
		return $this->defaultCoin;
	}
}