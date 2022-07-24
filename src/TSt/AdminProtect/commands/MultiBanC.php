<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class MultiBanC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "ban", "Ban specified players", "/multiban <players> [reason...]", null, ["mban"]);
        $this->setPermission("adminprotect.ban.use.multiple");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /ban <{$this->cfg->get("Player")}> [{$this->cfg->get("Reason")}...]");
        }else{
            $names = explode(',', array_shift($args));
            $r = trim(implode(" ", $args));
            $reason = ($r === '') ? $this->cfg->get('DefaultBanReason') : $r;
            
            if($sender instanceof Player){
                $adminName = $sender->getNameTag();
                $admin = $sender->getName();
                $offline = $sender->hasPermission("adminprotect.ban.use.offline");
                $protected = $sender->hasPermission("adminprotect.ban.use.protected");
            }else{
                $adminName = $this->cfg->get("Console");
                $admin = $adminName;
                $offline = true;
                $protected = true;
            }
            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('BannedPlayerKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
            
            
            foreach($names as $name){
                $p = $sender->getServer()->getPlayerExact($name);
                $bannedPlayer = $this->getPlugin()->getServer()->getNameBans()->getEntry($name);
                if($bannedPlayer !== null){
                    if(($sender instanceof Player and $sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))) or ($sender->hasPermission("adminprotect.unban.except.*") && $bannedPlayer->getSource() != $sender->getName())){
                        $sender->sendMessage("§4[AdminProtect] §c".str_replace(["%error%", "%player%"], [str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantEditBan")),$name], $this->cfg->get("MultipleBanError")));
                        continue;
                    }
                }
                if($p instanceof Player){
                    if($p->hasPermission("adminprotect.ban.protect" )){
                        if($sender instanceof Player and !$protected){
                            $sender->sendMessage("§4[AdminProtect] §c".str_replace(["%error%", "%player%"], [str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantBanPlayer")),$name], $this->cfg->get("MultipleBanError")));
                            continue;
                        }
                    }
                    $p->kick($kick_message);
                }else{
                    if($sender instanceof Player and !$offline){
                        $sender->sendMessage("§4[AdminProtect] §c".str_replace(["%error%", "%player%"], [str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantBanOffline")),$name], $this->cfg->get("MultipleBanError")));
                        continue;
                    }
                }
                $sender->getServer()->getNameBans()->addBan($name, $reason, null, $admin);
                $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanBroadcast'));
                $broadcast = str_replace("%player%", $name, $broadcast);
                $broadcast = str_replace("%reason%", $reason, $broadcast);
                $sender->getServer()->broadcastMessage($broadcast);
            }
            
        }
        return true;
    }
}