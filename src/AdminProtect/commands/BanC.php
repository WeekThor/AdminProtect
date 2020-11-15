<?php
namespace AdminProtect\commands;

use AdminProtect\APIs\API;
use AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
class BanC extends API{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "ban", "Ban specified player", "/ban <player> [reason...]", null, ["tban"]);
        $this->setPermission("admin.protect.ban.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /ban <{$this->cfg->get("Player")}> [{$this->cfg->get("Reason")}...]");
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
                $admin = $admin = $this->cfg->get("Console");;
                $adminName = $admin = $this->cfg->get("Console");;
            }
            $kick_message = str_replace("%sender%", $admin, $this->cfg->get('BannedPlayerKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
        
            if($p instanceof Player){
                if($p->hasPermission("admin.protect.ban" )){
                    if($sender instanceof Player and !$sender->hasPermission("admin.protect.kick.ban.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                    }else{
                        $sender->getServer()->getNameBans()->addBan($p, $reason, null, $adminName);
                        $kick_message = str_replace("%sender%", $admin, $this->cfg->get('BannedPlayerKickMessage'));
                        $kick_message = str_replace("%reason%", $reason, $kick_message);
                        $p->kick($kick_message);
                        $broad = str_replace("%sender%", $admin, $this->cfg->get('BanBroadcast'));
                        $broadc = str_replace("%player%", $p->getNameTag(), $broad);
                        $broadcast = str_replace("%reason%", $reason, $broadc);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                }else{
                    $sender->getServer()->getNameBans()->addBan($name, $reason, null, $adminName);
                    $p->kick($kick_message);
                    $broad = str_replace("%sender%", $admin, $this->cfg->get('BanBroadcast'));
                    $broadc = str_replace("%player%", $p->getNameTag(), $broad);
                    $broadcast = str_replace("%reason%", $reason, $broadc);
                    $sender->getServer()->broadcastMessage($broadcast);
                }
            
            }else{
                if(!($sender instanceof Player) or $sender->hasPermission("admin.protect.ban.use.offline")){
                    $sender->getServer()->getNameBans()->addBan($name, $reason, null, $adminName);
                    $broad = str_replace("%sender%", $admin, $this->cfg->get('BanBroadcast'));
                    $broadc = str_replace("%player%", $name, $broad);
                    $broadcast = str_replace("%reason%", $reason, $broadc);
                    $sender->getServer()->broadcastMessage($broadcast);
                }else{
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanOffline")}");
                }
                
            }
            
        }
        return true;
    }
}
