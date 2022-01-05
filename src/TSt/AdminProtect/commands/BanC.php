<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class BanC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "ban", "Ban specified player", "/ban <player> [reason...]", null, []);
        $this->setPermission("adminprotect.ban.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /ban <{$this->cfg->get("Player")}> [{$this->cfg->get("Reason")}...]");
        }else{
            $name = array_shift($args);
            $r = trim(implode(" ", $args));
            $reason = ($r === '') ? $this->cfg->get('DefaultKickReason') : $r;
            $p = $sender->getServer()->getPlayerExact($name);
            
            if($sender instanceof Player){
                $adminName = $sender->getNameTag();
            }else{
                $adminName = $this->cfg->get("Console");;
            }
            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('BannedPlayerKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
        
            if($p instanceof Player){
                if($p->hasPermission("adminprotect.ban.protect" )){
                    if($sender instanceof Player and !$sender->hasPermission("adminprotect.ban.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                    }else{
                        $sender->getServer()->getNameBans()->addBan($name, $reason, null, $adminName);
                        $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('BannedPlayerKickMessage'));
                        $kick_message = str_replace("%reason%", $reason, $kick_message);
                        $p->kick($kick_message);
                        $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanBroadcast'));
                        $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                        $broadcast = str_replace("%reason%", $reason, $broadcast);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                }else{
                    $sender->getServer()->getNameBans()->addBan($name, $reason, null, $adminName);
                    $p->kick($kick_message);
                    $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanBroadcast'));
                    $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                    $broadcast = str_replace("%reason%", $reason, $broadcast);
                    $sender->getServer()->broadcastMessage($broadcast);
                }
            
            }else{
                if(!($sender instanceof Player) or $sender->hasPermission("adminprotect.ban.use.offline")){
                    $sender->getServer()->getNameBans()->addBan($name, $reason, null, $adminName);
                    $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanBroadcast'));
                    $broadcast = str_replace("%player%", $name, $broadcast);
                    $broadcast = str_replace("%reason%", $reason, $broadcast);
                    $sender->getServer()->broadcastMessage($broadcast);
                }else{
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanOffline")}");
                }
                
            }
            if($this->getPlugin()->banInfoAPI != null){
                $api = $this->getPlugin()->banInfoAPI;
                $api->updateHistory($name);
            }
            
        }
        return true;
    }
}