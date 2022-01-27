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
            $reason = ($r === '') ? $this->cfg->get('DefaultBanReason') : $r;
            $p = $sender->getServer()->getPlayerExact($name);
            
            if($sender instanceof Player){
                $adminName = $sender->getNameTag();
                $admin = $sender->getName();
            }else{
                $adminName = $this->cfg->get("Console");
                $admin = $adminName;
            }
            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('BannedPlayerKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
            
            $bannedPlayer = $this->getPlugin()->getServer()->getNameBans()->getEntry($name);
            if($bannedPlayer !== null){
                if($sender instanceof Player and $sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))){
                    $sender->sendMessage("§4[AdminProtect] §c".str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantEditBan")));
                    return false;
                }
            }
            if($p instanceof Player){
                if($p->hasPermission("adminprotect.ban.protect" )){
                    if($sender instanceof Player and !$sender->hasPermission("adminprotect.ban.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                        return false;
                    }
                }
                $p->kick($kick_message);
            }else{
                if($sender instanceof Player and !$sender->hasPermission("adminprotect.ban.use.offline")){
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanOffline")}");
                    return false;
                }
            }
            $sender->getServer()->getNameBans()->addBan($name, $reason, null, $admin);
            $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanBroadcast'));
            $broadcast = str_replace("%player%", $name, $broadcast);
            $broadcast = str_replace("%reason%", $reason, $broadcast);
            $sender->getServer()->broadcastMessage($broadcast);
            
        }
        return true;
    }
}