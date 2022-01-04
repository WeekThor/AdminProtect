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
            $reason = implode(' ', $args);
            if($reason == ''){
                $reason = $this->cfg->get('DefaultBanReason');
            }
            if(DateTime::createFromFormat("d.m.Y", $until) !== false){
                $banTime = strtotime($until);
            }else{
                $time = preg_replace("/(\d+)(h)(\d+|$)/i", '${1}hours${3}', $until);
                $time = preg_replace("/(\d+)(m)(\d+|$)/i", '${1}minutes${3}', $time);
                $time = preg_replace("/(\d+)(mo)(\d+|$)/i", '${1}month${3}', $time);
                $time = preg_replace("/(\d+)(s)(\d+|$)/i", '${1}seconds${3}', $time);
                $time = preg_replace("/(\d+)(w)(\d+|$)/i", '${1}weeks${3}', $time);
                $time = preg_replace("/(\d+)(d)(\d+|$)/i", '${1}days${3}', $time);
                $time = preg_replace("/(\d+)(y)(\d+|$)/i", '${1}years${3}', $time);
                $banTime = strtotime(date('d.m.Y H:i:s').' +'.$time);
            }
            if($banTime === false){
                $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("DateFormatError")}");
            }else{
                $dt = \DateTime::createFromFormat("d.m.Y H:i:s", date("d.m.Y H:i:s", $banTime));
                $p = $sender->getServer()->getPlayerExact($name);
                if($sender instanceof Player){
                    $adminName = $sender->getNameTag();
                }else{
                    $adminName = $this->cfg->get("Console");;
                }
                
                if($p instanceof Player){
                    if($p->hasPermission("adminprotect.tempban.protect" )){
                        if($sender instanceof Player and !$sender->hasPermission("adminprotect.tempban.protected")){
                            $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                        }else{
                            $sender->getServer()->getNameBans()->addBan($name, $reason, $dt, $adminName);
                            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('TempBannedPlayerKickMessage'));
                            $kick_message = str_replace("%reason%", $reason, $kick_message);
                            $kick_message = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $kick_message);
                            $p->kick($kick_message);
                            $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanBroadcast'));
                            $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                            $broadcast = str_replace("%reason%", $reason, $broadcast);
                            $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
                            $sender->getServer()->broadcastMessage($broadcast);
                        }
                    }else{
                        $sender->getServer()->getNameBans()->addBan($name, $reason, $dt, $adminName);
                        $p->kick($kick_message);
                        $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanBroadcast'));
                        $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                        $broadcast = str_replace("%reason%", $reason, $broadcast);
                        $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                    
                }else{
                    if(!($sender instanceof Player) or $sender->hasPermission("adminprotect.tempban.use.offline")){
                        $sender->getServer()->getNameBans()->addBan($name, $reason, $dt, $adminName);
                        $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanBroadcast'));
                        $broadcast = str_replace("%player%", $name, $broadcast);
                        $broadcast = str_replace("%reason%", $reason, $broadcast);
                        $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
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
        }
        return true;
    }
}