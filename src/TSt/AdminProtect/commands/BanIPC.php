<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\API;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class BanIPC extends API{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "banip", "Ban specified IP", "/ban <player> [reason...]", null, ["tbanip", "ban-ip"]);
        $this->setPermission("admin.protect.banip.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /banip <{$this->cfg->get("Player")}|{$this->cfg->get("IP")}> [{$this->cfg->get("Reason")}...]");
        }else{
            
            $defaultreason = $this->cfg->get('DefaultBanReason');
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
                $adminName = $sender->getName();
            }else{
                $admin = $this->cfg->get("Console");
                $adminName = $this->cfg->get("Console");
            }
            $kick_message = str_replace("%sender%", $admin, $this->cfg->get('BannedIPKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
        
            if($p instanceof Player){
                if($p->hasPermission("admin.protect.banip" )){
                    if($sender instanceof Player and !$sender->hasPermission("admin.protect.banip.use.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                    }else{
                        $sender->getServer()->getIPBans()->addBan($p->getAddress(), $reason, null, $adminName);
                        $kick_message = str_replace("%sender%", $admin, $this->cfg->get('BannedPlayerKickMessage'));
                        $kick_message = str_replace("%reason%", $reason, $kick_message);
                        $p->kick($kick_message);
                        $broad = str_replace("%sender%", $admin, $this->cfg->get('BanIPBroadcast'));
                        $broadc = str_replace("%player%", $name, $broad);
                        $broadcast = str_replace("%reason%", $reason, $broadc);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                }else{
                    $sender->getServer()->getIPBans()->addBan($p->getAddress(), $reason, null, $adminName);
                    $p->kick($kick_message);
                    $broad = str_replace("%sender%", $admin, $this->cfg->get('BanIPBroadcast'));
                    $broadc = str_replace("%player%", $name, $broad);
                    $broadcast = str_replace("%reason%", $reason, $broadc);
                    $sender->getServer()->broadcastMessage($broadcast);
                }
            
            }else{
                if(!($sender instanceof Player) or $sender->hasPermission("admin.protect.banip.use.offline")){
                    if($this->getPlugin()->isIPValid($name)){
                        $sender->getServer()->getIPBans()->addBan($name, $reason, null, $adminName);
                        $broad = str_replace("%sender%", $admin, $this->cfg->get('BanIPBroadcast'));
                        $broadc = str_replace("%player%", $name, $broad);
                        $broadcast = str_replace("%reason%", $reason, $broadc);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }else{
                        $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("IncorrectIP")} {$this->cfg->get("forBan")}");
                    }
                }else{
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanPOffline")}");
                }
                
            }
            
        }
        return true;
    }
}