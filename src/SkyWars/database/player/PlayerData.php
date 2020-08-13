<?php

namespace SkyWars\database\player;

use SkyWars\SkyWars;

use SkyWars\database\DataBase;

use pocketmine\Player;

class PlayerData{

		protected $base;

	

	protected $users = [];

	

	public function __construct(SkyWars $base){

		$this->base = $base;

	}

	

	public function addUser(Player $player){

		if(!(file_exists($this->base->getDataFolder() . 'PlayerData/' . $player->getName() . '.js'))){

			$dataConfig = new DataBase($this->base->getDataFolder() . 'PlayerData/' . $player->getName() . '.js', DataBase::JSON);

			$dats = array(

				'name' => $player->getName(),

				'kills' => 0,

				'deaths' => 0,

				'wins' => 0,

				'kits' => array('basic'),

				'selectKit' => 'basic',

				'money' => 0,

				'hearts' => 0

			);

			foreach($dats as $key => $value){

				$dataConfig->set($key, $value);

			}

			$dataConfig->save();

			$this->users[$player->getRawUniqueId()] = $dats;

		} else {

			$dataConfig = new DataBase($this->base->getDataFolder() . 'PlayerData/' . $player->getName() . '.js', DataBase::JSON);

			$this->users[$player->getRawUniqueId()] = $dataConfig->getAll();

		}

	}

	

	public function saveData($uniqueId){

		if(isset($this->users[$uniqueId])){

			$dats = $this->users[$uniqueId];

			$dataConfig = new DataBase($this->base->getDataFolder() . 'PlayerData/' . $dats['name']. '.js', DataBase::JSON);

			foreach($dats as $key => $value){

				$dataConfig->set($key, $value);

			}

			$dataConfig->save();

			unset($this->users[$uniqueId]);

			unset($dataConfig);

			unset($dats);

		}

	}

	

	public function addKill(Player $player){

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['kills']++;

		} else {

			$this->addUser($player);

			$this->addKill($player);

		}

	}

	

	public function addDeath(Player $player){

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['deaths']++;

		} else {

			$this->addUser($player);

			$this->addDeath($player);

		}

	}

	public function addWin(Player $player){

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['wins']++;

		} else {

			$this->addUser($player);

			$this->addWin($player);

		}

	}

	

	public function addMoney(Player $player, int $money){

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['money'] = $this->users[$player->getRawUniqueId()]['money'] + $money;

		} else {

			$this->addUser($player);

			$this->addMoney($player, $hearts);

		}

	}

	

	public function reduceMoney(Player $player, int $money){

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['money'] = $this->users[$player->getRawUniqueId()]['money'] - $money;

		} else {

			$this->addUser($player);

			$this->reduceMoney($player, $hearts);

		}

	}

	

	public function addHearts(Player $player, int $hearts){

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['hearts'] = $this->users[$player->getRawUniqueId()]['hearts'] + $hearts;

		} else {

			$this->addUser($player);

			$this->addHearts($player, $hearts);

		}

	}

	

	public function getKills(Player $player): int{

		if(isset($this->users[$player->getRawUniqueId()])){

			return $this->users[$player->getRawUniqueId()]['kills'];

		} else {

			$this->addUser($player);

			return 0;

		}

	}

	

	public function getDeaths(Player $player): int{

		if(isset($this->users[$player->getRawUniqueId()])){

			return $this->users[$player->getRawUniqueId()]['deaths'];

		} else {

			$this->addUser($player);

			return 0;

		}

	}

	

	public function getWins(Player $player): int{

		if(isset($this->users[$player->getRawUniqueId()])){

			return $this->users[$player->getRawUniqueId()]['wins'];

		} else {

			$this->addUser($player);

			return 0;

		}

	}

	public function getMoney(Player $player): int{

		if(isset($this->users[$player->getRawUniqueId()])){

			return $this->users[$player->getRawUniqueId()]['money'];

		} else {

			$this->addUser($player);

			return 0;

		}

	}

	

	public function getHearts(Player $player): int{

		if(isset($this->users[$player->getRawUniqueId()])){

			return $this->users[$player->getRawUniqueId()]['hearts'];

		} else {

			$this->addUser($player);

			return 0;

		}

	}

	

	public function getKit(Player $player): string{

		if(isset($this->users[$player->getRawUniqueId()])){

			return $this->users[$player->getRawUniqueId()]['selectKit'];

		} else {

			$this->addUser($player);

			return 'basic';

		}

	}

	

	public function setKit(Player $player, string $kit): void{

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['selectKit'] = $kit;

		} else {

			$this->addUser($player);

			$this->setKit($player, $kit);

		}

	}

	

	public function addKit(Player $player, string $kit): void{

		if(isset($this->users[$player->getRawUniqueId()])){

			$this->users[$player->getRawUniqueId()]['kits'][] = $kit;

		} else {

			$this->addUser($player);

			$this->addKit($player, $kit);

		}

	}

	

	public function hasKit(Player $player, string $kit): bool{

		if(isset($this->users[$player->getRawUniqueId()])){

			return in_array($kit, $this->users[$player->getRawUniqueId()]['kits']);

		} else {

			$this->addUser($player);

			$this->hasKit($player, $kit);

		}

	}

}

?>
