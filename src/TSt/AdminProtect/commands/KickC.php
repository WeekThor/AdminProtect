<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\API;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class KickC extends API{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "kick", "Kick specified player", "/kick <player> [reason...]", null, ["tkick"]);
        $this->setPermission("admin.protect.kick.use");
    }
    
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
          $sender->sendMessage("§4[AdminProtect]§c /kick <{$this->cfg->get("Player")}> [{$this->cfg->get("Reason")}...]");
        }else{
            
            $defaultreason = $this->cfg->get('DefaultKickReason');
            $name = array_shift($args);
            $r = trim(implode(" ", $args));
            if($r == null){
                $reason = $defaultreason;
            }else{
                $reason = $r;
            }
            $p = $sender->getServer()->getPlayer($name);
        
        
        
            if($sender instanceof Player){
                $admin = $sender->getNameTag();
            }else{
                $admin = $this->cfg->get("Console");
            }
            $kick_message = str_replace("%sender%", $admin, $this->cfg->get('KickedPlayerKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
        
            if($p instanceof Player){
                if($p->hasPermission("admin.protect.kick" )){
                    if($sender instanceof Player and !$sender->hasPermission("admin.protect.kick.use.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantKickPlayer")}");
                    }else{
                        $p->kick($kick_message);
                        $broad = str_replace("%sender%", $admin, $this->cfg->get('KickBroadcast'));
                        $broadc = str_replace("%player%", $p->getNameTag(), $broad);
                        $broadcast = str_replace("%reason%", $reason, $broadc);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                    return false;
                }else{
                    $p->kick($kick_message);
                    $broad = str_replace("%sender%", $admin, $this->cfg->get('KickBroadcast'));
                    $broadc = str_replace("%player%", $p->getNameTag(), $broad);
                    $broadcast = str_replace("%reason%", $reason, $broadc);
                    $sender->getServer()->broadcastMessage($broadcast);
                    return false;
                } 
        
            }else{
                $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("PlayerNotFound")}");
                return true;
            }
        
        }
        return true;
    }
}