<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\Loader;
use TSt\AdminProtect\APIs\APCommand;
use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TempBanIPC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "tempban-ip", "Ban specified player", "/tempban-ip <player> <date or duration> [reason...]", null, ["tban-ip", "tbanip", "tempbanip"]);
        $this->setPermission("adminprotect.banip.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /tempbanip <{$this->cfg->get("Player")}|{$this->cfg->get("IP")}> <{$this->cfg->get("Date")}> [{$this->cfg->get("Reason")}...]");
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
                
                $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('TempBannedIPKickMessage'));
                $kick_message = str_replace("%duration%", date("d.m.Y H:i:s O", $banTime), $kick_message);
                $kick_message = str_replace("%reason%", $reason, $kick_message);
                $ip = "";
                $broadcast = "";
                
                $bannedPlayer = $this->getPlugin()->getServer()->getIPBans()->getEntry($name);
                if($bannedPlayer !== null){
                    if(($sender instanceof Player and $sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))) or ($sender->hasPermission("adminprotect.unban.except.*") && $bannedPlayer->getSource() != $sender->getName())){
                        $sender->sendMessage("§4[AdminProtect] §c".str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantEditBan")));
                        return false;
                    }
                }
                if(($p = $sender->getServer()->getPlayerByPrefix($name)) instanceof Player){
                    $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanIPBroadcast'));
                    $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                    $broadcast = str_replace("%reason%", $reason, $broadcast);
                    if($p->hasPermission("adminprotect.banip.protect" )){
                        if($sender instanceof Player and !$sender->hasPermission("adminprotect.banip.use.protected")){
                            $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                            return false;
                        }
                    }
                    $ip = $p->getNetworkSession()->getIp();
                }else{
                    $ip = $name;
                    $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanIPBroadcast'));
                    $broadcast = str_replace("%player%", $ip, $broadcast);
                    $broadcast = str_replace("%reason%", $reason, $broadcast);
                    if($sender instanceof Player and !$sender->hasPermission("adminprotect.banip.use.offline")){
                        $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanOffline")}");
                        return false;
                    }
                    if(!$this->getPlugin()->isIPValid($ip)){
                        $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("IncorrectIP")} {$this->cfg->get("forBan")}");
                        return false;
                    }
                }
                // if also block network, banned player will not see server status
                // and will not see "You are banned" message when trying to connect
                // But in the PockerMine ip-ban also blocks network
                // I don't know, block adress or not...
                //$sender->getServer()->getNetwork()->unblockAddress($p->getNetworkSession()->getIp());
                //$sender->getServer()->getNetwork()->blockAddress($name, abs(date("U")-$dt->getTimestamp()));
                
                $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
                $sender->getServer()->getIPBans()->addBan($ip, $reason, $dt, $admin);
                $sender->getServer()->broadcastMessage($broadcast);
                $players = $sender->getServer()->getOnlinePlayers();
                foreach ($players as $player){
                    if($player->getNetworkSession()->getIp() === $ip){
                        $player->kick($kick_message);
                    }
                }
                
            }
        }
        return true;
    }
}