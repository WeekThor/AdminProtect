<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use DateTime;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class TempBanC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "tempban", "Temp ban specified player", "/tempban <player> <date or duration> [reason...]", null, ["tban"]);
        $this->setPermission("adminprotect.tempban.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) < 2){
            $sender->sendMessage("§4[AdminProtect]§c /ban <{$this->cfg->get("Player")}> <{$this->cfg->get("Date")}> [{$this->cfg->get("Reason")}...]");
        }else{
            $name = array_shift($args);
            $until = array_shift($args);
            $r = trim(implode(" ", $args));
            $reason = ($r === '') ? $this->cfg->get('DefaultBanReason') : $r;
            $banTime = $this->getPlugin()->parseDuration($until);
            if($banTime === false){
                $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("DateFormatError")}");
            }else{
                $dt = \DateTime::createFromFormat("d.m.Y H:i:s", date("d.m.Y H:i:s", $banTime));
                $p = $sender->getServer()->getPlayerExact($name);
                if($sender instanceof Player){
                    $adminName = $sender->getNameTag();
                    $admin = $sender->getName();
                }else{
                    $adminName = $this->cfg->get("Console");
                    $admin = $adminName;
                }
                $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('TempBannedPlayerKickMessage'));
                $kick_message = str_replace("%reason%", $reason, $kick_message);
                $kick_message = str_replace("%duration%", date("d.m.Y H:i:s O", $banTime), $kick_message);
                
                $bannedPlayer = $this->getPlugin()->getServer()->getNameBans()->getEntry($name);
                if($bannedPlayer !== null){
                    if($sender instanceof Player and $sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))){
                        $sender->sendMessage("§4[AdminProtect] §c".str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantEditBan")));
                        return false;
                    }
                }
                if($p instanceof Player){
                    if($p->hasPermission("adminprotect.tempban.protect" )){
                        if($sender instanceof Player and !$sender->hasPermission("adminprotect.tempban.protected")){
                            $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                            return false;
                        }
                    }
                    $p->kick($kick_message);
                }else{
                    if($sender instanceof Player and !$sender->hasPermission("adminprotect.tempban.use.offline")){
                        $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanOffline")}");
                        return false;
                    }
                }
                $sender->getServer()->getNameBans()->addBan($name, $reason, $dt, $admin);
                $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanBroadcast'));
                $broadcast = str_replace("%player%", $name, $broadcast);
                $broadcast = str_replace("%reason%", $reason, $broadcast);
                $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
                $sender->getServer()->broadcastMessage($broadcast);
            }
        }
        return true;
    }
}