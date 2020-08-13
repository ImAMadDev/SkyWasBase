<?php

namespace SkyWars;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\Player;
use SkyWars\SkyWars;
use SkyWars\forms\kits\Kitsform;
use SkyWars\forms\vote\VoteForm;
use SkyWars\forms\teams\TeamsForm;
use SkyWars\game\Game;
use SkyWars\game\utils\teams\Team;
use SkyWars\game\utils\GameAPI;
use SkyWars\event\game\GameQuitEvent;
use SkyWars\event\game\GameJoinEvent;
use function explode;

class SkyWarsListener implements Listener{
	
	protected $base;
	
	public function __construct(SkyWars $base){
		$this->base = $base;
	}
	
	public function onJoinGame(GameJoinEvent $event): void{
		if($event->getGame() instanceof Game){
			$this->base->getPlayerManager()->addUser($event->getPlayer());
			$event->getPlayer()->setHealth(20.0);
			$event->getPlayer()->setFood(20.0);
		}
	}
	
	public function onQuitGame(GameQuitEvent $event): void{
		if($event->getGame() instanceof Game){
			if($event->getGame()->getMode() !== 'Solo'){
				if(($team = $event->getGame()->getPlayerTeam($event->getPlayer())) instanceof Team){
					$team->remove($event->getPlayer());
				}
			}
			foreach(array('op', 'basic', 'normal') as $vote){
				$event->getGame()->removeChestVote($vote, $event->getPlayer()->getName());
			}
			$this->base->getPlayerManager()->saveData($event->getPlayer()->getRawUniqueId());
		}
	}
	
	public function onCommand(PlayerCommandPreprocessEvent $event): void{
		$command = strtolower($event->getMessage());
		if($command{0} == '/') {
			$command = explode(' ', $command);
			if(($game = $this->base->getGameByName($event->getPlayer()->getLevel()->getFolderName())) instanceof Game){
				if(isset($command[0]) && isset($command[1])){
					if($command[0] != 'sw'){
						if($command[1] != 'leave'){
							$event->getPlayer()->sendMessage(SkyWars::PREFIX . '§cAll commands are banned on skywars.');
							$event->setCancelled(true);
						}
					}
                }
            }
        }
        unset($command);
    }
	
	public function onLevelChange(EntityLevelChangeEvent $event): void{
		if($event->getEntity() instanceof Player) {
			if(($game = $this->base->getGameByName($event->getOrigin()->getFolderName())) instanceof Game){
					$game->quitGame($event->getEntity());
			}
		}
	}
	
	public function onHunger(PlayerExhaustEvent $event): void{
		if($event->getEntity() instanceof Player) {
			if(($game = $this->base->getGameByName($event->getEntity()->getLevel()->getFolderName())) instanceof Game){
				if(!($game->canEdit())){
					$event->setCancelled(true);
				}
			}
		}
	}
	
	public function onQuit(PlayerQuitEvent $event): void{
		if($event->getPlayer() instanceof Player) {
			if(($game = $this->base->getGameByName($event->getPlayer()->getLevel()->getFolderName())) instanceof Game){
					$game->quitGame($event->getPlayer(), true);
			}
		}
	}
	
	public function onSignChange(SignChangeEvent $event): void{
		$player = $event->getPlayer();
		if($event->getLine(0) == "[SW]"){
			if(!($player->hasPermission("skywars.admin"))) return;
			$world = $event->getLine(1);
			if(($game = $this->base->getGameByName($world)) instanceof Game){
				$event->setLine(0, $game->getSignLine(0));
				$event->setLine(1, $game->getSignLine(1));
				$event->setLine(2, $game->getSignLine(2));
				$event->setLine(3, $game->getSignLine(3));
				$player->sendMessage(SkyWars::PREFIX . '§aJoin sign has been registered.');
			}
		}
	}
	
	public function onJoinSign(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		$sign = $player->getLevel()->getTile($event->getBlock());
		if($sign instanceof Sign){ 
			$text = $sign->getText();
			if($text[0] == SkyWars::PREFIX){
				if(($game = $this->base->getGameByName($text[1])) instanceof Game){
					$game->joinGame($player);
				}
			}
		}
	}
	
	public function onBlockPlacing(BlockPlaceEvent $event): void{
		$player = $event->getPlayer();
		if(($game = $this->base->getGameByName($player->getLevel()->getFolderName())) instanceof Game){
			if(!($game->canEdit())){
				$event->setCancelled(true);
			}
		}
	}
	
	public function onBlockBreak(BlockBreakEvent $event): void{
		$player = $event->getPlayer();
		if(($game = $this->base->getGameByName($player->getLevel()->getFolderName())) instanceof Game){
			if(!($game->canEdit())){
				$event->setCancelled(true);
			} else{
				if($event->getBlock()->getID() == Block::CHEST){
					$chest = $player->getLevel()->getTile($event->getBlock());
					if($chest instanceof Chest){
						$chest->getInventory()->dropContents($player->getLevel(), $event->getBlock()->add(0, 1, 0));
						$event->setCancelled(true);
					}
				}
			}
		}
	}
	
	public function onInteract(PlayerInteractEvent $event): void{
		$player = $event->getPlayer();
		if(($game = $this->base->getGameByName($player->getLevel()->getFolderName())) instanceof Game){
			if(!($game->canEdit())){
				if($event->getBlock()->getID() == Block::CHEST){
					$event->setCancelled(true);
					return;
				} elseif($event->getItem()->getID() == Item::NETHERSTAR){
					new Kitsform($player);
					return;
				} elseif($event->getItem()->getID() == Item::PRISMARINE_SHARD){
					$game->quitGame($player, true);
				} elseif($event->getItem()->getID() == Block::CHEST){
					new VoteForm($player);
				} elseif($event->getItem()->getID() == Item::WOOL){
					new TeamsForm($player);
				}
			}
		}
	}
	
	public function onEntityDamage(EntityDamageEvent $event): void{
		$player = $event->getEntity();
		if($player instanceof Player) {
			if(($game = $this->base->getGameByName($player->getLevel()->getFolderName())) instanceof Game){
				if($game->canEdit()){
					if(($player->getHealth() - $event->getFinalDamage()) <= 0) {
						$event->setCancelled(true);
						$game->killPlayer($player);
						return;
					}
				} else {
					$event->setCancelled(true);
				}
			}
		}
	}
	public function onRegisterCombat(EntityDamageEvent $event): void{
		if($event instanceof EntityDamageByEntityEvent) {
			$victim = $event->getEntity();
			$attacker = $event->getDamager();
			if($victim instanceof Player and $attacker instanceof Player) {
				if(($game = $this->base->getGameByName($victim->getLevel()->getFolderName())) instanceof Game){
					if($game->canEdit()){
						if($game->getMode() !== 'Solo'){
							if($game->equalTeam($victim, $attacker)){
								$event->setCancelled(true);
								return;
							}
						}
						if(!$this->base->getCombatManager()->isTagged($victim)) {
							$victim->sendMessage('§c- §6You are now in combat, do not logout or you will be punished!');
						}
						if(!$this->base->getCombatManager()->isTagged($attacker)) {
							$attacker->sendMessage('§c- §6You are now in combat, do not logout or you will be punished!');
						}
						$this->base->getCombatManager()->setTagged($attacker, $victim, true, 15);
						$this->base->getCombatManager()->setTagged($victim, $attacker, true, 15);
					}
				}
			}
		}
	}
	
}

?>